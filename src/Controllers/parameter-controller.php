<?php
session_start();

require_once __DIR__ . '/../Models/parameter-model.php';
header('Content-Type: application/json');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getById', 'fetchMethods','fetchTableView'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new ParameterModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ========== FETCH ALL ==========
        case 'fetchAll':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }

            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
            $filters['page'] = $page;
            $filters['limit'] = $limit;

            $result = $model->getAllParameters($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'totalPages' => ceil($result['total'] / $limit)
            ]);
            break;

        // ========== GET BY ID ==========
        case 'getById':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            $parameter = $model->getParameterById($id);
            if ($parameter) {
                // Fetch method_ids instead of single method_id
                $parameter['method_ids'] = $model->getParameterMethodIds($id);
                echo json_encode(['status' => 'success', 'data' => $parameter]);
            } else {
                throw new Exception('Parameter not found');
            }
            break;

        // ========== INSERT ==========
        case 'insert':
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $baseUnit = trim($_POST['base_unit'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $swabPrice = isset($_POST['swab_price']) && $_POST['swab_price'] !== ''
                ? floatval($_POST['swab_price']) : 0.00;
            $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            // Handle array of method_ids
            $methodIds = isset($_POST['method_ids']) && is_array($_POST['method_ids'])
                ? array_filter(array_map('intval', $_POST['method_ids']))
                : [];

            if ($name === '') {
                throw new Exception('Parameter name is required');
            }

            // Check deleted record first
            $deletedRecord = $model->findDeletedByName($name);

            if ($deletedRecord) {
                // Reactivate
                $result = $model->reactivateParameter(
                    $deletedRecord['parameter_id'],
                    $category,
                    $baseUnit,
                    $swabEnabled,
                    $isActive
                );

                if ($result) {
                    $paramId = $deletedRecord['parameter_id'];

                    // Sync methods on reactivation
                    $model->syncParameterMethods($paramId, $methodIds);

                    // ✅ FIX: Removed auto-creation of swab_param on reactivation
                    // User must manually create swab pricing in swab-param page

                    // Prepare response message
                    $message = 'Parameter reactivated successfully';
                    if ($swabEnabled == 1) {
                        $message .= '. Swab pricing enabled - please set the price in Swab Parameter page.';
                    }

                    echo json_encode([
                        'status' => 'success',
                        'message' => $message,
                        'parameter_id' => $paramId,
                        'swab_enabled' => $swabEnabled
                    ]);
                } else {
                    throw new Exception('Failed to reactivate parameter');
                }
            } else {
                // Check active duplicate
                if ($model->isDuplicate($name)) {
                    throw new Exception('Parameter with this name already exists');
                }

                // Insert new parameter
                $paramId = $model->insertParameter($name, $category, $baseUnit, $swabEnabled, $isActive);

                if ($paramId) {
                    // Assign methods
                    $model->assignMethodsToParameter($paramId, $methodIds);

                    // ✅ FIX: Removed auto-creation of swab_param on new parameter
                    // User must manually create swab pricing in swab-param page

                    // Prepare response message
                    $message = 'Parameter added successfully';
                    if ($swabEnabled == 1) {
                        $message .= '. Swab pricing enabled - please set the price in Swab Parameter page.';
                    }

                    echo json_encode([
                        'status' => 'success',
                        'message' => $message,
                        'parameter_id' => $paramId,
                        'swab_enabled' => $swabEnabled
                    ]);
                } else {
                    throw new Exception('Failed to insert parameter');
                }
            }
            break;

        // ========== UPDATE ==========
        case 'update':
            $id = intval($_POST['parameter_id'] ?? 0);
            $code = trim($_POST['parameter_code'] ?? '');
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $baseUnit = trim($_POST['base_unit'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);
            $methodIds = isset($_POST['method_ids']) && is_array($_POST['method_ids'])
                ? array_filter(array_map('intval', $_POST['method_ids']))
                : [];

            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }
            if ($name === '') {
                throw new Exception('Parameter name is required');
            }

            // Get current parameter and methods
            $currentParam = $model->getParameterById($id);
            $currentMethods = $model->getParameterMethodIds($id);

            // Sort arrays for comparison
            sort($currentMethods);
            sort($methodIds);

            // Check if anything changed
            $fieldsUnchanged =
                $currentParam['parameter_name'] === $name &&
                $currentParam['parameter_category'] === $category &&
                $currentParam['base_unit'] === $baseUnit &&
                intval($currentParam['swab_enabled']) === $swabEnabled &&
                intval($currentParam['is_active']) === $isActive &&
                $currentMethods === $methodIds;

            if ($fieldsUnchanged) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No changes detected'
                ]);
                exit;
            }

            // Check for duplicate name + same methods
            $allParams = $model->getAllParameters(['is_active' => '']);
            foreach ($allParams['data'] as $param) {
                if ($param['parameter_id'] == $id) continue;

                if ($param['parameter_name'] === $name) {
                    $existingMethodIds = $model->getParameterMethodIds($param['parameter_id']);
                    sort($existingMethodIds);
                    if ($existingMethodIds === $methodIds) {
                        throw new Exception('Another parameter with same name and same methods already exists');
                    }
                }
            }

            // Get previous swab status
            $wasSwabEnabled = intval($currentParam['swab_enabled']);

            // Perform update
            if ($model->updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)) {
                // Sync methods
                $model->syncParameterMethods($id, $methodIds);

                // Handle swab status changes
                if ($swabEnabled == 1 && $wasSwabEnabled == 0) {
                    // ✅ FIX: Removed auto-creation when enabling swab
                    // User must manually create swab pricing in swab-param page
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Parameter updated successfully. Swab pricing enabled - please set the price in Swab Parameter page.',
                        'swab_enabled_changed' => true
                    ]);
                    exit;
                    
                } elseif ($swabEnabled == 0 && $wasSwabEnabled == 1) {
                    // When disabling swab, soft-delete the swab_param record
                    $model->disableSwabParam($id);
                }

                // ✅ FIX: Use safe sync method that doesn't fail if swab_param doesn't exist
                if ($swabEnabled == 1) {
                    $model->syncSwabParamStatusIfExists($id, $isActive);
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Parameter updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update parameter');
            }
            break;

        // ========== DELETE ==========
        case 'delete':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            if ($model->hasActiveVariants($id)) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This parameter has active variants. Please deactivate them first.'
                ]);
                exit;
            }

            if ($model->softDeleteParameter($id)) {
                // Soft-delete associated swab_param if exists
                $model->disableSwabParam($id);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Parameter deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete parameter');
            }
            break;

        // ========== TOGGLE STATUS ==========
        case 'toggleStatus':
            $id = intval($_POST['parameter_id'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);

            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            if ($model->toggleStatus($id, $isActive)) {
                // ✅ FIX: Use safe sync method that doesn't fail if swab_param doesn't exist
                $model->syncSwabParamStatusIfExists($id, $isActive);

                echo json_encode([
                    'status' => 'success',
                    'message' => $isActive ? 'Parameter activated' : 'Parameter deactivated'
                ]);
            } else {
                throw new Exception('Failed to update status');
            }
            break;

        case 'fetchMethods':
            $methods = $model->getActiveMethods();
            echo json_encode([
                'status' => 'success',
                'data' => $methods
            ]);
            break;

        case 'fetchTableView':
            try {
                $tableData = $model->getParametersWithMethods();

                echo json_encode([
                    'status' => 'success',
                    'data' => $tableData
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load table data: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Parameter Controller Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}