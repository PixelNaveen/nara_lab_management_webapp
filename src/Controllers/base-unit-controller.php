<?php
/**
 * base-unit-controller.php
 * 
 * Purpose: API controller for base unit system
 * Provides endpoints for fetching categories and units
 * 
 * Phase: 2 - Base Unit System
 */

session_start();

require_once __DIR__ . '/../Models/BaseUnitModel.php';
header('Content-Type: application/json');

$model = new BaseUnitModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // ========== GET ALL CATEGORIES ==========
        case 'getAllCategories':
            $categories = $model->getAllCategories();
            echo json_encode([
                'status' => 'success',
                'data' => $categories
            ]);
            break;

        // ========== GET UNITS FOR CATEGORY ==========
        case 'getUnitsForCategory':
            $categoryId = intval($_POST['category_id'] ?? $_GET['category_id'] ?? 0);
            $commonOnly = isset($_POST['common_only']) || isset($_GET['common_only']);
            
            if ($categoryId <= 0) {
                throw new Exception('Invalid category ID');
            }
            
            $units = $model->getUnitsForCategory($categoryId, $commonOnly);
            echo json_encode([
                'status' => 'success',
                'data' => $units,
                'category_id' => $categoryId
            ]);
            break;

        // ========== GET ALL UNITS GROUPED ==========
        case 'getAllUnitsGrouped':
            $grouped = $model->getAllUnitsGrouped();
            echo json_encode([
                'status' => 'success',
                'data' => $grouped
            ]);
            break;

        // ========== GET CATEGORY BY ID ==========
        case 'getCategoryById':
            $categoryId = intval($_POST['category_id'] ?? $_GET['category_id'] ?? 0);
            
            if ($categoryId <= 0) {
                throw new Exception('Invalid category ID');
            }
            
            $category = $model->getCategoryById($categoryId);
            
            if ($category) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $category
                ]);
            } else {
                throw new Exception('Category not found');
            }
            break;

        // ========== GET CATEGORY BY CODE ==========
        case 'getCategoryByCode':
            $code = trim($_POST['code'] ?? $_GET['code'] ?? '');
            
            if ($code === '') {
                throw new Exception('Category code is required');
            }
            
            $category = $model->getCategoryByCode($code);
            
            if ($category) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $category
                ]);
            } else {
                throw new Exception('Category not found');
            }
            break;

        // ========== GET UNIT BY ID ==========
        case 'getUnitById':
            $unitId = intval($_POST['unit_id'] ?? $_GET['unit_id'] ?? 0);
            
            if ($unitId <= 0) {
                throw new Exception('Invalid unit ID');
            }
            
            $unit = $model->getUnitById($unitId);
            
            if ($unit) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $unit
                ]);
            } else {
                throw new Exception('Unit not found');
            }
            break;

        // ========== SEARCH UNITS ==========
        case 'searchUnits':
            $searchTerm = trim($_POST['search'] ?? $_GET['search'] ?? '');
            
            if ($searchTerm === '') {
                throw new Exception('Search term is required');
            }
            
            $units = $model->searchUnits($searchTerm);
            echo json_encode([
                'status' => 'success',
                'data' => $units,
                'search_term' => $searchTerm
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

        // ========== ADMIN: INSERT UNIT ==========
        case 'insertUnit':
            // CSRF protection (will implement properly in Phase 5)
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized');
            }
            
            $categoryId = intval($_POST['category_id'] ?? 0);
            $unitName = trim($_POST['unit_name'] ?? '');
            $unitType = trim($_POST['unit_type'] ?? 'count');
            $isCommon = isset($_POST['is_common']) ? intval($_POST['is_common']) : 1;
            
            if ($categoryId <= 0) {
                throw new Exception('Invalid category ID');
            }
            
            if ($unitName === '') {
                throw new Exception('Unit name is required');
            }
            
            if (!in_array($unitType, ['count', 'presence', 'concentration', 'other'])) {
                throw new Exception('Invalid unit type');
            }
            
            $unitId = $model->insertUnit($categoryId, $unitName, $unitType, $isCommon);
            
            if ($unitId) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Unit added successfully',
                    'unit_id' => $unitId
                ]);
            } else {
                throw new Exception('Failed to add unit');
            }
            break;

        // ========== ADMIN: UPDATE UNIT ==========
        case 'updateUnit':
            // CSRF protection
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized');
            }
            
            $unitId = intval($_POST['unit_id'] ?? 0);
            $unitName = trim($_POST['unit_name'] ?? '');
            $unitType = trim($_POST['unit_type'] ?? 'count');
            $isCommon = isset($_POST['is_common']) ? intval($_POST['is_common']) : 1;
            
            if ($unitId <= 0) {
                throw new Exception('Invalid unit ID');
            }
            
            if ($unitName === '') {
                throw new Exception('Unit name is required');
            }
            
            if ($model->updateUnit($unitId, $unitName, $unitType, $isCommon)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Unit updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update unit');
            }
            break;

        // ========== ADMIN: DELETE UNIT ==========
        case 'deleteUnit':
            // CSRF protection
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized');
            }
            
            $unitId = intval($_POST['unit_id'] ?? 0);
            
            if ($unitId <= 0) {
                throw new Exception('Invalid unit ID');
            }
            
            // TODO: Check if unit is used by any parameters before deleting
            // Will implement in Phase 5
            
            if ($model->deleteUnit($unitId)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Unit deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete unit');
            }
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Base Unit Controller Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}