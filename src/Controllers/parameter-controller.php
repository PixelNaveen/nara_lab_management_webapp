<?php
session_start();

// Authentication check
// if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
//     exit;
// }

require_once __DIR__ . '/../Models/parameter-model.php';
header('Content-Type: application/json');

// CSRF Token validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getById'])) {
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
        // ========== FETCH ALL PARAMETERS ==========
        case 'fetchAll':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }
            
            // Pagination
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

        // ========== GET PARAMETER BY ID ==========
        case 'getById':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }
            
            $parameter = $model->getParameterById($id);
            if ($parameter) {
                // Get swab price if exists
                $swabData = $model->getSwabPrice($id);
                $parameter['swab_price'] = $swabData ? $swabData['swab_price'] : null;
                
                echo json_encode(['status' => 'success', 'data' => $parameter]);
            } else {
                throw new Exception('Parameter not found');
            }
            break;

        // ========== INSERT PARAMETER ==========
        case 'insert':
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $baseUnit = trim($_POST['base_unit'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $swabPrice = isset($_POST['swab_price']) && $_POST['swab_price'] !== '' 
                         ? floatval($_POST['swab_price']) : 0.00;
            $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

            // Validation
            if ($name === '') {
                throw new Exception('Parameter name is required');
            }

            // Check for deleted record with same name (reactivation logic)
            $deletedRecord = $model->findDeletedByName($name);
            
            if ($deletedRecord) {
                // Reactivate existing deleted record
                $result = $model->reactivateParameter(
                    $deletedRecord['parameter_id'],
                    $category,
                    $baseUnit,
                    $swabEnabled,
                    $isActive
                );
                
                if ($result) {
                    $paramId = $deletedRecord['parameter_id'];
                    
                    // Handle swab_param reactivation
                    if ($swabEnabled == 1) {
                        $model->reactivateSwabParam($paramId, $swabPrice);
                    }
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Parameter reactivated successfully',
                        'parameter_id' => $paramId
                    ]);
                } else {
                    throw new Exception('Failed to reactivate parameter');
                }
            } else {
                // Check for active duplicate
                if ($model->isDuplicate($name)) {
                    throw new Exception('Parameter with this name already exists');
                }

                // Insert new parameter
                $paramId = $model->insertParameter($name, $category, $baseUnit, $swabEnabled, $isActive);
                
                if ($paramId) {
                    // Insert swab_param if swab is enabled
                    if ($swabEnabled == 1) {
                        $model->insertSwabParam($paramId, $swabPrice);
                    }
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Parameter added successfully',
                        'parameter_id' => $paramId
                    ]);
                } else {
                    throw new Exception('Failed to insert parameter');
                }
            }
            break;

        // ========== UPDATE PARAMETER ==========
        case 'update':
            $id = intval($_POST['parameter_id'] ?? 0);
            $code = trim($_POST['parameter_code'] ?? '');
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $baseUnit = trim($_POST['base_unit'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $swabPrice = isset($_POST['swab_price']) && $_POST['swab_price'] !== '' 
                         ? floatval($_POST['swab_price']) : null;
            $isActive = intval($_POST['is_active'] ?? 1);

            // Validation
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }
            if ($name === '') {
                throw new Exception('Parameter name is required');
            }

            // Check duplicate (excluding current record)
            if ($model->isDuplicate($name, $id)) {
                throw new Exception('Another parameter with this name already exists');
            }

            // Get current swab status
            $currentParam = $model->getParameterById($id);
            $wasSwabEnabled = $currentParam['swab_enabled'];

            // Update parameter
            if ($model->updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)) {
                
                // Handle swab_param changes
                if ($swabEnabled == 1 && $wasSwabEnabled == 0) {
                    // Swab was disabled, now enabled → create swab_param
                    $model->insertSwabParam($id, $swabPrice ?? 0.00);
                    
                } elseif ($swabEnabled == 0 && $wasSwabEnabled == 1) {
                    // Swab was enabled, now disabled → soft delete swab_param
                    $model->deleteSwabParam($id);
                    
                } elseif ($swabEnabled == 1 && $wasSwabEnabled == 1 && $swabPrice !== null) {
                    // Swab still enabled, update price if provided
                    $model->updateSwabPrice($id, $swabPrice);
                }

                // Sync is_active status to swab_param
                if ($swabEnabled == 1) {
                    $model->syncSwabParamStatus($id, $isActive);
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Parameter updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update parameter');
            }
            break;

        // ========== SOFT DELETE ==========
        case 'delete':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }
            
            // Check if parameter has active variants
            if ($model->hasActiveVariants($id)) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This parameter has active variants. Please deactivate them first.'
                ]);
                exit;
            }

            // Soft delete parameter and cascade to swab_param
            if ($model->softDeleteParameter($id)) {
                $model->deleteSwabParam($id); // Cascade soft delete
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Parameter deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete parameter');
            }
            break;

        // ========== TOGGLE STATUS (Activate/Deactivate) ==========
        case 'toggleStatus':
            $id = intval($_POST['parameter_id'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);
            
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            if ($model->toggleStatus($id, $isActive)) {
                // Sync to swab_param
                $model->syncSwabParamStatus($id, $isActive);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => $isActive ? 'Parameter activated' : 'Parameter deactivated'
                ]);
            } else {
                throw new Exception('Failed to update status');
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
?>