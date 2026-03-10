<?php
// src/Controllers/ExtraItemsController.php - UPDATED WITHOUT DISPLAY_ORDER

session_start();

require_once __DIR__ . '/../Models/ExtraItemsModel.php';
header('Content-Type: application/json');

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

$model = new ExtraItemsModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ========== FETCH ALL ITEMS ==========
        case 'fetchAll':
            $filters = [];
            
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = $_POST['is_active'];
            }
            
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }
            
            if (isset($_POST['sort']) && $_POST['sort'] !== '') {
                $filters['sort'] = $_POST['sort'];
            }
            
            $result = $model->getAllItems($filters);
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

        // ========== INSERT ITEM ==========
        case 'insert':
            $itemName = trim($_POST['item_name'] ?? '');
            $itemValue = floatval($_POST['item_value'] ?? 0);
            $itemUnit = trim($_POST['item_unit'] ?? '');
            $itemPrice = floatval($_POST['item_price'] ?? 0);
            $itemDescription = trim($_POST['item_description'] ?? '');
            $createdBy = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'admin';
            
            if ($itemName === '') {
                throw new Exception('Item name is required');
            }
            
            if ($itemValue <= 0) {
                throw new Exception('Item value must be greater than 0');
            }
            
            if ($itemUnit === '') {
                throw new Exception('Item unit is required');
            }
            
            if ($itemPrice <= 0) {
                throw new Exception('Item price must be greater than 0');
            }
            
            // Check for deleted record
            $deletedRecord = $model->findDeletedByDetails($itemName, $itemValue, $itemUnit);
            
            if ($deletedRecord) {
                // Reactivate
                if ($model->reactivateItem($deletedRecord['item_id'], $itemPrice, $itemDescription)) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Item reactivated successfully'
                    ]);
                } else {
                    throw new Exception('Failed to reactivate item');
                }
            } else {
                // Check duplicate
                if ($model->itemExists($itemName, $itemValue, $itemUnit)) {
                    throw new Exception('Item with same name, value and unit already exists');
                }
                
                // Insert new
                $itemId = $model->insertItem($itemName, $itemValue, $itemUnit, $itemPrice, 
                                            $itemDescription, $createdBy);
                
                if ($itemId) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Item added successfully',
                        'item_id' => $itemId
                    ]);
                } else {
                    throw new Exception('Failed to add item');
                }
            }
            break;

        // ========== UPDATE ITEM ==========
        case 'update':
            $itemId = intval($_POST['item_id'] ?? 0);
            $itemName = trim($_POST['item_name'] ?? '');
            $itemValue = floatval($_POST['item_value'] ?? 0);
            $itemUnit = trim($_POST['item_unit'] ?? '');
            $itemPrice = floatval($_POST['item_price'] ?? 0);
            $itemDescription = trim($_POST['item_description'] ?? '');
            
            if ($itemId <= 0) {
                throw new Exception('Invalid item ID');
            }
            
            if ($itemName === '') {
                throw new Exception('Item name is required');
            }
            
            if ($itemValue <= 0) {
                throw new Exception('Item value must be greater than 0');
            }
            
            if ($itemUnit === '') {
                throw new Exception('Item unit is required');
            }
            
            if ($itemPrice <= 0) {
                throw new Exception('Item price must be greater than 0');
            }
            
            // Check duplicate (excluding current item)
            if ($model->itemExists($itemName, $itemValue, $itemUnit, $itemId)) {
                throw new Exception('Item with same name, value and unit already exists');
            }
            
            if ($model->updateItem($itemId, $itemName, $itemValue, $itemUnit, $itemPrice, $itemDescription)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Item updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update item');
            }
            break;

        // ========== DELETE ITEM ==========
        case 'delete':
            $itemId = intval($_POST['item_id'] ?? 0);
            
            if ($itemId <= 0) {
                throw new Exception('Invalid item ID');
            }
            
            if ($model->softDeleteItem($itemId)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Item deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete item');
            }
            break;

        // ========== TOGGLE STATUS ==========
        case 'toggleStatus':
            $itemId = intval($_POST['item_id'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);
            
            if ($itemId <= 0) {
                throw new Exception('Invalid item ID');
            }
            
            if ($model->toggleStatus($itemId, $isActive)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $isActive ? 'Item activated' : 'Item deactivated'
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
    error_log("ExtraItemsController Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>