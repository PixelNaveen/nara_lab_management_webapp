<?php
// src/Controllers/CityController.php - FINAL COMPLETE VERSION

if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
}

require_once __DIR__ . '/../Models/CityModel.php';
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    echo json_encode(['status' => 'error', 'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"]);
    exit;
});

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getStatistics'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new CityModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ========== FETCH ALL CITIES ==========
        case 'fetchAll':
            $filters = [];
            
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = $_POST['is_active'];
            }
            
            if (isset($_POST['type']) && $_POST['type'] !== '') {
                $filters['type'] = $_POST['type'];
            }
            
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }
            
            if (isset($_POST['sort']) && $_POST['sort'] !== '') {
                $filters['sort'] = $_POST['sort'];
            }
            
            $result = $model->getAllCities($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'total' => $result['total']
            ]);
            break;

        // ========== GET STATISTICS ==========
        case 'getStatistics':
            $stats = $model->getStatistics();
            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
            break;

        // ========== INSERT CITY ==========
        case 'insert':
            $cityName = trim($_POST['city_name'] ?? '');
            
            // Auto-set type and user
            $isPredefined = 1;
            $createdBy = $_SESSION['user_name'] ?? $_SESSION['fullname'] ?? 'admin';
            
            if ($cityName === '') {
                throw new Exception('City name is required');
            }
            
            // Check for deleted record
            $deletedRecord = $model->findDeletedByName($cityName);
            
            if ($deletedRecord) {
                // Reactivate
                if ($model->reactivateCity($deletedRecord['city_id'])) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'City reactivated successfully'
                    ]);
                } else {
                    throw new Exception('Failed to reactivate city');
                }
            } else {
                // Check duplicate
                if ($model->cityExists($cityName)) {
                    echo json_encode([
                        'status' => 'warning',
                        'message' => 'City with this name already exists'
                    ]);
                    exit;
                }
                
                // Insert new
                $cityId = $model->insertCity($cityName, $isPredefined, $createdBy);
                
                if ($cityId) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'City added successfully',
                        'city_id' => $cityId
                    ]);
                } else {
                    throw new Exception('Failed to add city');
                }
            }
            break;

        // ========== UPDATE CITY ==========
        case 'update':
            $cityId = intval($_POST['city_id'] ?? 0);
            $cityName = trim($_POST['city_name'] ?? '');
            
            // Keep existing type
            $currentCity = $model->getCityById($cityId);
            $isPredefined = $currentCity['is_predefined'] ?? 1;
            
            if ($cityId <= 0) {
                throw new Exception('Invalid city ID');
            }
            
            if ($cityName === '') {
                throw new Exception('City name is required');
            }
            
            // Check if name is actually different
            if (strtolower($currentCity['city_name']) === strtolower($cityName)) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'No changes detected'
                ]);
                exit;
            }

            // Check duplicate (excluding current city)
            if ($model->cityExists($cityName, $cityId)) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'City with this name already exists'
                ]);
                exit;
            }
            
            if ($model->updateCity($cityId, $cityName, $isPredefined)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'City updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update city'
                ]);
                exit;
            }
            break;

        // ========== DELETE CITY ==========
        case 'delete':
            $cityId = intval($_POST['city_id'] ?? 0);
            
            if ($cityId <= 0) {
                throw new Exception('Invalid city ID');
            }
            
            if ($model->softDeleteCity($cityId)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'City deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete city');
            }
            break;

        // ========== TOGGLE STATUS ==========
        case 'toggleStatus':
            $cityId = intval($_POST['city_id'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);
            
            if ($cityId <= 0) {
                throw new Exception('Invalid city ID');
            }
            
            if ($model->toggleStatus($cityId, $isActive)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $isActive ? 'City activated' : 'City deactivated'
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
    error_log("CityController Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
