<?php
session_start();

require_once __DIR__ . '/../Models/parameter-model.php';
header('Content-Type: application/json');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getById', 'getFullData', 'fetchMethods', 'fetchTableView', 'fetchCertificates'])) {
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

            // Use getFullParameterData to include category configs
            $parameter = $model->getFullParameterData($id);
            if ($parameter) {
                echo json_encode(['status' => 'success', 'data' => $parameter]);
            } else {
                throw new Exception('Parameter not found');
            }
            break;

        // ========== GET FULL DATA (alias) ==========
        case 'getFullData':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }
            $data = $model->getFullParameterData($id);
            if ($data) {
                echo json_encode(['status' => 'success', 'data' => $data]);
            } else {
                throw new Exception('Parameter not found');
            }
            break;

        // ========== INSERT ==========
        case 'insert':
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $swabPrice = isset($_POST['swab_price']) && $_POST['swab_price'] !== ''
                ? floatval($_POST['swab_price']) : 0.00;
            $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            $resultMode = $_POST['result_mode'] ?? 'numeric_or_ND';
            $espcApplicable = isset($_POST['espc_applicable']) ? 1 : 0;
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
                    '',
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
                $extraFields = [
                    'short_name' => trim($_POST['short_name'] ?? ''),
                    'display_format' => trim($_POST['display_format'] ?? 'normal'),
                    'result_mode' => in_array($resultMode, ['numeric_or_ND', 'present_or_absent'], true)
                        ? $resultMode
                        : 'numeric_or_ND',
                    'espc_applicable' => $espcApplicable
                ];
                $paramId = $model->insertParameter($name, $category, $swabEnabled, $isActive, $extraFields);

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

        case 'update':
            $id = intval($_POST['parameter_id'] ?? 0);
            $code = trim($_POST['parameter_code'] ?? '');
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $shortName = trim($_POST['short_name'] ?? '');
            $displayFormat = trim($_POST['display_format'] ?? 'normal');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);
            $resultMode = $_POST['result_mode'] ?? 'numeric_or_ND';
            $espcApplicable = isset($_POST['espc_applicable']) ? 1 : 0;
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
                ($currentParam['short_name'] ?? '') === $shortName &&
                ($currentParam['display_format'] ?? 'normal') === $displayFormat &&
                $currentParam['parameter_category'] === $category &&
                ($currentParam['result_mode'] ?? 'numeric_or_ND') === ($resultMode ?: 'numeric_or_ND') &&
                intval($currentParam['espc_applicable'] ?? 0) === $espcApplicable &&
                intval($currentParam['swab_enabled']) === $swabEnabled &&
                intval($currentParam['is_active']) === $isActive &&
                $currentMethods === $methodIds;

            if ($fieldsUnchanged) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'No basic changes detected, proceeding to category configs...',
                    'parameter_id' => $id
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
            $extraFields = [
                'short_name' => $shortName,
                'display_format' => $displayFormat,
                'result_mode' => in_array($resultMode, ['numeric_or_ND', 'present_or_absent'], true)
                    ? $resultMode
                    : 'numeric_or_ND',
                'espc_applicable' => $espcApplicable
            ];
            if ($model->updateParameter($id, $code, $name, $category, $swabEnabled, $isActive, $extraFields)) {
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

        // ========== SAVE CATEGORY CONFIGS ==========
        case 'saveCategoryConfigs':
            $paramId = intval($_POST['parameter_id'] ?? 0);
            if ($paramId <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            $configsJson = $_POST['configs'] ?? '[]';
            $configs = json_decode($configsJson, true);
            $deletedJson = $_POST['deleted_categories'] ?? '[]';
            $deletedCategories = json_decode($deletedJson, true);

            if (!is_array($configs)) {
                throw new Exception('Invalid configs data');
            }

            // Delete removed categories
            if (is_array($deletedCategories)) {
                foreach ($deletedCategories as $removedCatId) {
                    $model->deleteSingleCategoryConfig($paramId, intval($removedCatId));
                }
            }

            // Save each config
            $savedConfigs = [];
            foreach ($configs as $cfg) {
                $categoryId = intval($cfg['base_category_id'] ?? 0);
                $unitId = intval($cfg['base_unit_id'] ?? 0);
                $isSlabAccredited = intval($cfg['is_slab_accredited'] ?? 0);
                $certificateId = !empty($cfg['certificate_id']) ? intval($cfg['certificate_id']) : null;
                $methodIds = isset($cfg['methods']) && is_array($cfg['methods'])
                    ? array_filter(array_map('intval', $cfg['methods']))
                    : [];

                if ($categoryId <= 0 || $unitId <= 0) continue;

                $configId = $model->saveCategoryConfig(
                    $paramId,
                    $categoryId,
                    $unitId,
                    $isSlabAccredited,
                    $certificateId
                );

                if ($configId) {
                    // Always try to save category methods now, since Universal toggle is gone
                    $model->saveCategoryMethods($configId, $methodIds);
                }

                if ($configId) {
                    $savedConfigs[] = ['category_id' => $categoryId, 'config_id' => $configId];
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Category configurations saved (' . count($savedConfigs) . ' categories)',
                'saved_configs' => $savedConfigs
            ]);
            break;

        default:
            // Check for fetchCertificates before falling through
            if ($action === 'fetchCertificates') {
                $certs = $model->getActiveCertificates();
                echo json_encode(['status' => 'success', 'data' => $certs]);
                break;
            }
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Parameter Controller Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
