<?php
require_once __DIR__ . '/../Models/variant-model.php';
header('Content-Type: application/json');

$model = new VariantModel();
$action = $_POST['action'] ?? '';

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

        if ($parameter_id <= 0 || $variant_name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Parameter and Variant name are required.']);
            exit;
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

        if ($variant_id <= 0 || $parameter_id <= 0 || $variant_name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Variant id, parameter and name are required.']);
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
            echo json_encode(['status' => 'error', 'message' => 'Invalid variant id.']);
            exit;
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
?>