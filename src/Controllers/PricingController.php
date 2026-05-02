<?php

/**
 * Pricing Controller - PERFECT VERSION FOR ACTUAL MODEL
 * Works with the actual PricingModel methods
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 2.0 - Matched to actual model
 */

require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);

// Include dependencies
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/Functions.php';
require_once __DIR__ . '/../Models/PricingModel.php';

header('Content-Type: application/json; charset=utf-8');

// ========== AUTHENTICATION CHECK ==========
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in.'
    ]);
    exit;
}

// ========== CSRF VALIDATION ==========
$readOnlyActions = [
    'fetchActiveParameters',
    'fetchAllIndividuals',
    'fetchAllCombos',
    'getIndividualById',
    'getComboById',
    'previewComboName'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, $readOnlyActions)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid security token'
            ]);
            exit;
        }
    }
}

// ========== MODEL INITIALIZATION ==========
try {
    $model = new PricingModel();
} catch (Exception $e) {
    logError($e->getMessage(), 'PricingModel initialization');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Please try again.'
    ]);
    exit;
}

// ========== ACTION ROUTER ==========
$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ==================== FETCH ACTIVE PARAMETERS ====================
        case 'fetchActiveParameters':
            try {
                $parameters = $model->getActiveParameters();

                echo json_encode([
                    'status' => 'success',
                    'data' => $parameters
                ]);
            } catch (Exception $e) {
                logError($e->getMessage(), 'fetchActiveParameters');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load parameters: ' . $e->getMessage()
                ]);
            }
            break;

        // ==================== INDIVIDUAL PRICES ====================

        case 'fetchAllIndividuals':
            try {
                $filters = [
                    'search' => trim($_POST['search'] ?? ''),
                    'is_active' => $_POST['is_active'] ?? ''
                ];

                $prices = $model->getAllIndividualPrices($filters);

                echo json_encode([
                    'status' => 'success',
                    'data' => $prices
                ]);
            } catch (Exception $e) {
                logError($e->getMessage(), 'fetchAllIndividuals');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load prices: ' . $e->getMessage()
                ]);
            }
            break;

        case 'getIndividualById':
            try {
                $id = intval($_POST['id'] ?? 0);

                if ($id <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid ID'
                    ]);
                    exit;
                }

                $price = $model->getIndividualPriceById($id);

                if ($price) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $price
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Price not found'
                    ]);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'getIndividualById');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load price: ' . $e->getMessage()
                ]);
            }
            break;

        case 'insertIndividual':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can add prices.']);
                    exit;
                }
                $parameterId = intval($_POST['parameter_id'] ?? 0);
                $testCharge = floatval($_POST['test_charge'] ?? 0);
                $isActive = intval($_POST['is_active'] ?? 1);

                // Validation
                if ($parameterId <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Please select a parameter',
                        'field' => 'parameter_id'
                    ]);
                    exit;
                }

                if ($testCharge < 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Price cannot be negative',
                        'field' => 'test_charge'
                    ]);
                    exit;
                }

                // Check for existing active price
                if ($model->hasIndividualPrice($parameterId)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'This parameter already has a price. Please edit the existing price instead.',
                        'field' => 'parameter_id'
                    ]);
                    exit;
                }

                // Check for deleted price (reactivation)
                $deleted = $model->findDeletedIndividualPrice($parameterId);

                if ($deleted) {
                    // Reactivate existing deleted price
                    $success = $model->reactivateIndividualPrice(
                        $deleted['pricing_id'],
                        $testCharge,
                        $isActive
                    );

                    if ($success) {
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Price reactivated successfully',
                            'pricing_id' => $deleted['pricing_id']
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Failed to reactivate price'
                        ]);
                    }
                } else {
                    // Insert new price
                    $pricingId = $model->insertIndividualPrice($parameterId, $testCharge, $isActive);

                    if ($pricingId) {
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Price added successfully',
                            'pricing_id' => $pricingId
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Failed to add price'
                        ]);
                    }
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'insertIndividual');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error adding price: ' . $e->getMessage()
                ]);
            }
            break;

        case 'updateIndividual':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can modify prices.']);
                    exit;
                }

                $id = intval($_POST['id'] ?? 0);
                $testCharge = floatval($_POST['test_charge'] ?? 0);
                $isActive = intval($_POST['is_active'] ?? 1);

                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
                    exit;
                }

                if ($testCharge < 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Price cannot be negative',
                        'field' => 'test_charge'
                    ]);
                    exit;
                }

                // Get current data for comparison
                $current = $model->getIndividualPriceById($id);
                if (!$current) {
                    echo json_encode(['status' => 'error', 'message' => 'Price not found']);
                    exit;
                }

                // Check if anything changed
                if (
                    floatval($current['test_charge']) === $testCharge &&
                    intval($current['is_active']) === $isActive
                ) {
                    echo json_encode(['status' => 'info', 'message' => 'No update detected.']);
                    exit;
                }

                // Update
                $success = $model->updateIndividualPrice(
                    $id,
                    $current['parameter_id'],
                    $testCharge,
                    $isActive
                );

                if ($success) {
                    echo json_encode(['status' => 'success', 'message' => 'Price updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update price']);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'updateIndividual');
                echo json_encode(['status' => 'error', 'message' => 'Error updating price: ' . $e->getMessage()]);
            }
            break;

        case 'deleteIndividual':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can delete prices.']);
                    exit;
                }
                $id = intval($_POST['id'] ?? 0);

                if ($id <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid ID'
                    ]);
                    exit;
                }

                $success = $model->softDeleteIndividualPrice($id);

                if ($success) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Price deleted successfully'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to delete price'
                    ]);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'deleteIndividual');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error deleting price: ' . $e->getMessage()
                ]);
            }
            break;

        // ==================== COMBO PRICES ====================

        case 'fetchAllCombos':
            try {
                $filters = [
                    'search' => trim($_POST['search'] ?? ''),
                    'is_active' => $_POST['is_active'] ?? ''
                ];

                $combos = $model->getAllComboPrices($filters);

                echo json_encode([
                    'status' => 'success',
                    'data' => $combos
                ]);
            } catch (Exception $e) {
                logError($e->getMessage(), 'fetchAllCombos');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load combos: ' . $e->getMessage()
                ]);
            }
            break;

        case 'getComboById':
            try {
                $id = intval($_POST['id'] ?? 0);

                if ($id <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid ID'
                    ]);
                    exit;
                }

                $combo = $model->getComboPriceById($id);

                if ($combo) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $combo
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Combo not found'
                    ]);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'getComboById');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to load combo: ' . $e->getMessage()
                ]);
            }
            break;

        case 'insertCombo':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can add combos.']);
                    exit;
                }
                // Parse parameter IDs
                $parameterIds = $_POST['parameter_ids'] ?? [];
                if (!is_array($parameterIds)) {
                    $parameterIds = json_decode($parameterIds, true) ?? [];
                }
                $parameterIds = array_filter(array_map('intval', $parameterIds));

                // Reject duplicate parameters
                if (count($parameterIds) !== count(array_unique($parameterIds))) {
                    echo json_encode(['status' => 'error', 'message' => 'Cannot select the same parameter twice', 'field' => 'parameter_ids']);
                    exit;
                }

                $testCharge = floatval($_POST['test_charge'] ?? 0);
                $isActive = intval($_POST['is_active'] ?? 1);

                // Validation
                if (count($parameterIds) < 2) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Please select at least 2 parameters',
                        'field' => 'parameter_ids'
                    ]);
                    exit;
                }

                if ($testCharge < 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Price cannot be negative',
                        'field' => 'test_charge'
                    ]);
                    exit;
                }

                // Check for duplicate combo
                if ($model->hasExactCombo($parameterIds)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'A combo with these exact parameters already exists',
                        'field' => 'parameter_ids'
                    ]);
                    exit;
                }

                // Insert combo
                $comboId = $model->insertCombo($parameterIds, $testCharge, $isActive);

                if ($comboId) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Combo created successfully',
                        'combo_id' => $comboId
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to create combo'
                    ]);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'insertCombo');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error creating combo: ' . $e->getMessage()
                ]);
            }
            break;

        case 'updateCombo':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can modify prices.']);
                    exit;
                }

                $comboId = intval($_POST['id'] ?? 0);

                // Parse parameter IDs
                $parameterIds = $_POST['parameter_ids'] ?? [];
                if (!is_array($parameterIds)) {
                    $parameterIds = json_decode($parameterIds, true) ?? [];
                }
                $parameterIds = array_filter(array_map('intval', $parameterIds));
                sort($parameterIds); // Sort for comparison

                // Reject duplicate parameters
                if (count($parameterIds) !== count(array_unique($parameterIds))) {
                    echo json_encode(['status' => 'error', 'message' => 'Cannot select the same parameter twice', 'field' => 'parameter_ids']);
                    exit;
                }

                $testCharge = floatval($_POST['test_charge'] ?? 0);
                $isActive = intval($_POST['is_active'] ?? 1);

                // Validation
                if ($comboId <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
                    exit;
                }

                if (count($parameterIds) < 2) {
                    echo json_encode(['status' => 'error', 'message' => 'Please select at least 2 parameters', 'field' => 'parameter_ids']);
                    exit;
                }

                if ($testCharge < 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Price cannot be negative', 'field' => 'test_charge']);
                    exit;
                }

                // Get current data for comparison
                $current = $model->getComboPriceById($comboId);
                if (!$current) {
                    echo json_encode(['status' => 'error', 'message' => 'Combo not found']);
                    exit;
                }

                $currentParamIds = $current['parameter_ids'];
                sort($currentParamIds); // Sort for comparison

                // Check if anything changed
                if (
                    floatval($current['test_charge']) === $testCharge &&
                    intval($current['is_active']) === $isActive &&
                    $currentParamIds === $parameterIds
                ) {
                    echo json_encode(['status' => 'info', 'message' => 'No update detected.']);
                    exit;
                }

                // Check for duplicate combo (excluding current)
                if ($model->hasExactCombo($parameterIds, $comboId)) {
                    echo json_encode(['status' => 'error', 'message' => 'A combo with these exact parameters already exists', 'field' => 'parameter_ids']);
                    exit;
                }

                // Update combo
                $success = $model->updateCombo($comboId, $parameterIds, $testCharge, $isActive);

                if ($success) {
                    echo json_encode(['status' => 'success', 'message' => 'Combo updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update combo']);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'updateCombo');
                echo json_encode(['status' => 'error', 'message' => 'Error updating combo: ' . $e->getMessage()]);
            }
            break;

        case 'deleteCombo':
            try {
                // Role check
                if (!in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins and Lab Managers can delete combos.']);
                    exit;
                }
                $id = intval($_POST['id'] ?? 0);

                if ($id <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid ID'
                    ]);
                    exit;
                }

                $success = $model->softDeleteCombo($id);

                if ($success) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Combo deleted successfully'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to delete combo'
                    ]);
                }
            } catch (Exception $e) {
                logError($e->getMessage(), 'deleteCombo');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error deleting combo: ' . $e->getMessage()
                ]);
            }
            break;

        // ==================== COMBO NAME PREVIEW ====================

        case 'previewComboName':
            try {
                // Parse parameter IDs
                $parameterIds = $_POST['parameter_ids'] ?? [];
                if (!is_array($parameterIds)) {
                    $parameterIds = json_decode($parameterIds, true) ?? [];
                }
                $parameterIds = array_filter(array_map('intval', $parameterIds));

                if (count($parameterIds) < 2) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Select at least 2 parameters'
                    ]);
                    exit;
                }

                $comboName = $model->generateComboName($parameterIds);

                echo json_encode([
                    'status' => 'success',
                    'combo_name' => $comboName
                ]);
            } catch (Exception $e) {
                logError($e->getMessage(), 'previewComboName');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error generating combo name: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action: ' . $action
            ]);
    }
} catch (Exception $e) {
    logError($e->getMessage(), 'PricingController: ' . $action);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

exit;

// ==================== NOTES ====================
// 1. sendJsonResponse() is already defined in Functions.php - DO NOT redefine
// 2. All model methods called match the actual PricingModel class
// 3. Model returns arrays/values directly - wrapped in status/message here
// 4. All exits are explicit to prevent double output
// 5. Comprehensive error handling on all operations
// 6. CSRF protection on write operations
// 7. Input validation before model calls
// 8. Duplicate checking before inserts
// 9. Reactivation logic for soft-deleted items
// 10. Transaction safety handled in model layer
