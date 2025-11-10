<?php
require_once __DIR__ . '/../Models/test-method-model.php';
header('Content-Type: application/json');

$model = new TestMethodModel();
$action = $_POST['action'] ?? '';

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
        echo json_encode(['status' => 'error', 'message' => 'Method name and standard body are required.']);
        exit;
    }

    if (!in_array($status, ['active', 'inactive'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status.']);
        exit;
    }

    // Check for deleted method
    $deletedMethodId = $model->getDeletedMethodId($method_name, $standard_body);
    if ($deletedMethodId) {
        // Reactivate the method
        if ($model->reactivateTestMethod($deletedMethodId, $status)) {
            echo json_encode(['status' => 'success', 'message' => 'Deleted test method reactivated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reactivate test method.']);
        }
        exit;
    }

    // Prevent duplicates among active methods
    if ($model->isDuplicate($method_name, $standard_body)) {
        echo json_encode(['status' => 'error', 'message' => 'Test method already exists!']);
        exit;
    }

    if ($model->insertTestMethod($method_name, $standard_body, $status)) {
        echo json_encode(['status' => 'success', 'message' => 'Test method added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
    }
    break;


    // ========== UPDATE TEST METHOD ==========
    case 'update':
        $id = intval($_POST['method_id']);
        $method_name = trim($_POST['method_name']);
        $standard_body = trim($_POST['standard_body']);
        $status = $_POST['status'];

        if ($method_name === '' || $standard_body === '') {
            echo json_encode(['status' => 'error', 'message' => 'Method name and standard body are required.']);
            exit;
        }

        if (!in_array($status, ['active', 'inactive'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status.']);
            exit;
        }

        // Prevent duplicates (excluding self)
        if ($model->isDuplicate($method_name, $standard_body, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Test method already exists!']);
            exit;
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
?>