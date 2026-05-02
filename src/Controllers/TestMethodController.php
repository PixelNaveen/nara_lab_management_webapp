<?php
require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
require_once __DIR__ . '/../Models/TestMethodModel.php';
header('Content-Type: application/json');

// Custom error handler to return JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// CSRF validation and Auth for state-changing operations
$stateChangingActions = ['insert', 'update', 'delete'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Auth & Permission Check
    if (in_array($action, $stateChangingActions)) {
        if (!isset($_SESSION['user_id']) || !in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only Admin or Manager can modify test methods.']);
            exit;
        }

        // CSRF Check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new TestMethodModel();
$action = $_POST['action'] ?? '';

try {
switch ($action) {

    // ========== FETCH ALL TEST METHODS ==========
    case 'fetchAll':
        $testMethods = $model->getAllTestMethods();
        echo json_encode(['status' => 'success', 'data' => $testMethods]);
        break;

    // ========== INSERT TEST METHOD ==========
   case 'insert':
    $method_name = trim($_POST['method_name']);
    $standard_body = trim($_POST['standard_body']);
    $status = $_POST['status'];

    if ($method_name === '' || $standard_body === '') {
        throw new Exception('Method name and standard body are required.');
    }

    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status.');
    }

    // Check for deleted method
    $deletedMethodId = $model->getDeletedMethodId($method_name, $standard_body);
    if ($deletedMethodId) {
        // Reactivate the method
        if ($model->reactivateTestMethod($deletedMethodId, $status)) {
            echo json_encode(['status' => 'success', 'message' => 'Deleted test method reactivated.']);
        } else {
            throw new Exception('Failed to reactivate test method.');
        }
        exit;
    }

    // Prevent duplicates among active methods
    if ($model->isDuplicate($method_name, $standard_body)) {
        throw new Exception('Test method already exists!');
    }

    if ($model->insertTestMethod($method_name, $standard_body, $status)) {
        echo json_encode(['status' => 'success', 'message' => 'Test method added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
    }
    break;


    case 'update':
        $id = intval($_POST['method_id'] ?? 0);
        $method_name = trim($_POST['method_name'] ?? '');
        $standard_body = trim($_POST['standard_body'] ?? '');
        $status = $_POST['status'] ?? '';

        if ($id <= 0) throw new Exception('Invalid method id');
        if ($method_name === '' || $standard_body === '') {
            throw new Exception('Method name and standard body are required.');
        }

        // Check if anything changed
        $current = $model->getTestMethodById($id);
        
        $is_active_input = ($status === 'active') ? 1 : 0;
        if ($current && 
            $current['method_name'] === $method_name &&
            $current['standard_body'] === $standard_body &&
            intval($current['is_active']) === $is_active_input) {
            
            echo json_encode(['status' => 'info', 'message' => 'No update detected.']);
            exit;
        }

        // Prevent duplicates (excluding self)
        if ($model->isDuplicate($method_name, $standard_body, $id)) {
            throw new Exception('A test method with this name and standard body already exists!');
        }

        if ($model->updateTestMethod($id, $method_name, $standard_body, $status)) {
            echo json_encode(['status' => 'success', 'message' => 'Test method updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
        break;

    // ========== SOFT DELETE ==========
    case 'delete':
        $id = intval($_POST['method_id']);
        if ($model->softDeleteTestMethod($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Test method deleted successfully (soft delete).']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
