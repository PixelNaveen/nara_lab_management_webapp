<?php
session_start();
require_once __DIR__ . '/../Models/swab-model.php';
header('Content-Type: application/json');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // FIX: Added 'fetchDropdown' to the list of read-only actions that skip CSRF check
    if (!in_array($action, ['fetchAll', 'getById', 'fetchDropdown'])) {
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
        // ========== FETCH ALL SWAB PRICES ==========
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

        // ========== GET PARAMETERS DROPDOWN ==========
        case 'fetchDropdown':
            $params = $model->getParametersDropdown();
            echo json_encode(['status' => 'success', 'data' => $params]);
            break;

        // ========== GET SWAB BY ID ==========
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

        // ========== INSERT NEW SWAB PARAM ==========
        case 'insert':
            $paramId = intval($_POST['param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($paramId <= 0) throw new Exception('Parameter is required');
            if ($price < 0) throw new Exception('Price cannot be negative');

            // Check if already exists
            $existing = $model->findByParamId($paramId);
            if ($existing) {
                // If deleted, reactivate
                if (intval($existing['is_deleted']) === 1) {
                    if ($model->reactivateSwabByParam($paramId, $price, $isActive)) {
                        echo json_encode(['status' => 'success', 'message' => 'Swab parameter restored successfully']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to restore swab parameter']);
                    }
                } else {
                    // Active record exists
                    echo json_encode(['status' => 'error', 'message' => 'Swab parameter already exists for this parameter']);
                }
                exit;
            }

            // Insert new
            if ($model->insertSwab($paramId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Insert failed']);
            }
            break;

        // ========== UPDATE PRICE ==========
        case 'update':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($swabId <= 0) throw new Exception('Invalid ID');
            if ($price < 0) throw new Exception('Price cannot be negative');

            if ($model->updateSwabPrice($swabId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab price updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            }
            break;

        // ========== DELETE SWAB PARAM ==========
        case 'delete':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            if ($swabId <= 0) throw new Exception('Invalid ID');
            
            if ($model->softDeleteById($swabId)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter deleted successfully']);
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