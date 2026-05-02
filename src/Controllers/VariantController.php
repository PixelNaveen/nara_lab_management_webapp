<?php
require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
require_once __DIR__ . '/../Models/VariantModel.php';
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
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only Admin or Manager can modify variants.']);
            exit;
        }

        // CSRF Check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new VariantModel();
$action = $_POST['action'] ?? '';

try {
switch ($action) {

    // fetch all variants (optionally filter by parameter_id or is_active)
    case 'fetchAll':
        $filters = [];
        if (isset($_POST['parameter_id']) && $_POST['parameter_id'] !== '') {
            $filters['parameter_id'] = intval($_POST['parameter_id']);
        }
        if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
            $filters['is_active'] = intval($_POST['is_active']);
        }
        $variants = $model->getAllVariants($filters);
        echo json_encode(['status' => 'success', 'data' => $variants]);
        break;

    // get variant by id
    case 'getById':
        $id = intval($_POST['variant_id'] ?? 0);
        $v = $model->getVariantById($id);
        if ($v) echo json_encode(['status' => 'success', 'data' => $v]);
        else echo json_encode(['status' => 'error', 'message' => 'Variant not found']);
        break;

    // insert
    case 'insert':
        $parameter_id = intval($_POST['parameter_id'] ?? 0);
        $variant_name = trim($_POST['variant_name'] ?? '');
        $full_display_name = trim($_POST['full_display_name'] ?? '');
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

        if ($parameter_id <= 0) {
            throw new Exception('Parameter grouping must be selected.');
        }
        if ($variant_name === '') {
            throw new Exception('Variant name is required.');
        }

        $res = $model->insertVariant($parameter_id, $variant_name, $full_display_name, $is_active);
        if ($res === true) {
            echo json_encode(['status' => 'success', 'message' => 'Variant added successfully.']);
        } elseif ($res === 'duplicate') {
            echo json_encode(['status' => 'error', 'message' => 'A variant with this name already exists for the selected parameter.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
        }
        break;

    // update
    case 'update':
        $variant_id = intval($_POST['variant_id'] ?? 0);
        $parameter_id = intval($_POST['parameter_id'] ?? 0);
        $variant_name = trim($_POST['variant_name'] ?? '');
        $full_display_name = trim($_POST['full_display_name'] ?? '');
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

        if ($variant_id <= 0) {
            throw new Exception('Invalid variant id.');
        }
        if ($parameter_id <= 0) {
            throw new Exception('Parameter grouping must be selected.');
        }
        if ($variant_name === '') {
            throw new Exception('Variant name is required.');
        }

        $currentVariant = $model->getVariantById($variant_id);
        if ($currentVariant && 
            $currentVariant['parameter_id'] == $parameter_id &&
            $currentVariant['variant_name'] === $variant_name &&
            intval($currentVariant['is_active']) === $is_active) {
            
            echo json_encode(['status' => 'info', 'message' => 'No update detected.']);
            exit;
        }

        $res = $model->updateVariant($variant_id, $parameter_id, $variant_name, $full_display_name, $is_active);
        if ($res === true) {
            echo json_encode(['status' => 'success', 'message' => 'Variant updated successfully.']);
        } elseif ($res === 'duplicate') {
            echo json_encode(['status' => 'error', 'message' => 'Another variant with this name exists for the same parameter.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
        break;

    // delete (soft)
    case 'delete':
        $variant_id = intval($_POST['variant_id'] ?? 0);
        if ($variant_id <= 0) {
            throw new Exception('Invalid variant id.');
        }
        if ($model->softDeleteVariant($variant_id)) {
            echo json_encode(['status' => 'success', 'message' => 'Variant deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
        }
        break;

    // fetch params for combobox
    case 'fetchParams':
        $params = $model->getActiveParameters();
        echo json_encode(['status' => 'success', 'data' => $params]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
