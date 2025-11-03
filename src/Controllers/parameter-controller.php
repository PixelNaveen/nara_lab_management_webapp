<?php
require_once __DIR__ . '/../Models/parameter-model.php';
header('Content-Type: application/json');

$model = new ParameterModel();
$action = $_POST['action'] ?? '';

switch ($action) {

    // ========== FETCH ALL PARAMETERS ==========
    case 'fetchAll':
        $filters = [];
        if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
            $filters['is_active'] = intval($_POST['is_active']);
        }
        $parameters = $model->getAllParameters($filters);
        echo json_encode(['status' => 'success', 'data' => $parameters]);
        break;

    // ========== GET PARAMETER BY ID ==========
    case 'getById':
        $id = intval($_POST['parameter_id']);
        $parameter = $model->getParameterById($id);
        if ($parameter) {
            echo json_encode(['status' => 'success', 'data' => $parameter]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Parameter not found']);
        }
        break;

    // ========== INSERT PARAMETER ==========
    case 'insert':
        $name = trim($_POST['parameter_name'] ?? '');
        $category = trim($_POST['parameter_category'] ?? '');
        $baseUnit = trim($_POST['base_unit'] ?? '');
        $swabEnabled = intval($_POST['swab_enabled'] ?? 1);

        // Validation
        if ($name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Parameter name is required.']);
            exit;
        }

        // Prevent duplicate names
        if ($model->isDuplicate($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter with this name already exists!']);
            exit;
        }

        if ($model->insertParameter($name, $category, $baseUnit, $swabEnabled)) {
            echo json_encode(['status' => 'success', 'message' => 'Parameter added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
        }
        break;

    // ========== UPDATE PARAMETER ==========
    case 'update':
        $id = intval($_POST['parameter_id']);
        $code = trim($_POST['parameter_code'] ?? ''); // Code cannot be changed, just for display
        $name = trim($_POST['parameter_name'] ?? '');
        $category = trim($_POST['parameter_category'] ?? '');
        $baseUnit = trim($_POST['base_unit'] ?? '');
        $swabEnabled = intval($_POST['swab_enabled'] ?? 1);
        $isActive = intval($_POST['is_active'] ?? 1);

        // Validation
        if ($name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Parameter name is required.']);
            exit;
        }

        // Prevent duplicate names (excluding current record)
        if ($model->isDuplicate($name, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Another parameter with this name already exists!']);
            exit;
        }

        if ($model->updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)) {
            echo json_encode(['status' => 'success', 'message' => 'Parameter updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
        break;

    // ========== SOFT DELETE ==========
    case 'delete':
        $id = intval($_POST['parameter_id']);
        
        // Optional: Check if parameter has active variants
        if ($model->hasActiveVariants($id)) {
            echo json_encode([
                'status' => 'warning', 
                'message' => 'Warning: This parameter has active variants. Consider deactivating instead of deleting.'
            ]);
            exit;
        }

        if ($model->softDeleteParameter($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Parameter deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>