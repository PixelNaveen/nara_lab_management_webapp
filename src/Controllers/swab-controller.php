<?php
session_start();
require_once __DIR__ . '/../Models/swab-model.php';
header('Content-Type: application/json');

// For non-read actions validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'fetchDropdown', 'getById'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new SwabModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // Fetch table rows
        case 'fetchAll':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }
            $rows = $model->getAllSwabParams($filters);
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        // Fetch dropdown for parameter select
        case 'fetchDropdown':
            $params = $model->getParametersDropdown();
            echo json_encode(['status' => 'success', 'data' => $params]);
            break;

        // Get single swab row by id
        case 'getById':
            $id = intval($_POST['swab_param_id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid ID');
            $row = $model->getSwabById($id);
            if ($row) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Not found']);
            }
            break;

        // Insert new (or reactivate deleted)
        case 'insert':
            $paramId = intval($_POST['param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($paramId <= 0) throw new Exception('Parameter is required');

            // Check existing
            $existing = $model->findByParamId($paramId);
            if ($existing) {
                // If active and not deleted -> conflict
                if (intval($existing['is_deleted']) === 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Swab param already exists for this parameter']);
                    exit;
                }

                // Reactivate deleted record
                $ok = $model->reactivateSwabByParam($paramId, $price, $isActive);
                if ($ok) echo json_encode(['status' => 'success', 'message' => 'Swab parameter restored successfully']);
                else echo json_encode(['status' => 'error', 'message' => 'Failed to restore swab parameter']);
                exit;
            }

            // Insert new
            if ($model->insertSwab($paramId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter inserted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Insert failed']);
            }
            break;

        // Update existing
        case 'update':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($swabId <= 0) throw new Exception('Invalid ID');

            if ($model->updateSwabById($swabId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            }
            break;

        // Soft delete by swab_param_id
        case 'delete':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            if ($swabId <= 0) throw new Exception('Invalid ID');
            if ($model->softDeleteById($swabId)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter deleted']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Swab Controller Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
