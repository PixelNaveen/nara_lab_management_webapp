<?php
session_start();

require_once __DIR__ . '/../Models/pricing-model.php';
header('Content-Type: application/json');

// CSRF Validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $readOnlyActions = ['fetchAllIndividuals', 'fetchAllCombos', 'getIndividualById', 'getComboById', 'fetchActiveParameters', 'previewComboName'];

    if (!in_array($action, $readOnlyActions)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }
}

$model = new PricingModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ========== INDIVIDUAL PRICING ==========

        case 'fetchAllIndividuals':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }

            $result = $model->getAllIndividualPrices($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result
            ]);
            break;

        case 'getIndividualById':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid pricing ID');
            }

            $result = $model->getIndividualPriceById($id);
            if ($result) {
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                throw new Exception('Price not found');
            }
            break;

        case 'insertIndividual':
            $parameter_id = intval($_POST['parameter_id'] ?? 0);
            $test_charge = floatval($_POST['test_charge'] ?? 0);
            $is_active = intval($_POST['is_active'] ?? 1);

            if ($parameter_id <= 0) {
                throw new Exception('Please select a parameter');
            }
            if ($test_charge < 0) {
                throw new Exception('Price cannot be negative');
            }

            // Check if deleted price exists
            $deletedPrice = $model->findDeletedIndividualPrice($parameter_id);

            if ($deletedPrice) {
                // Reactivate
                if ($model->reactivateIndividualPrice($deletedPrice['pricing_id'], $test_charge, $is_active)) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Price reactivated successfully',
                        'pricing_id' => $deletedPrice['pricing_id']
                    ]);
                } else {
                    throw new Exception('Failed to reactivate price');
                }
            } else {
                // Check for active duplicate
                if ($model->hasIndividualPrice($parameter_id)) {
                    throw new Exception('This parameter already has a price');
                }

                // Insert new
                $pricing_id = $model->insertIndividualPrice($parameter_id, $test_charge, $is_active);

                if ($pricing_id) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Price added successfully',
                        'pricing_id' => $pricing_id
                    ]);
                } else {
                    throw new Exception('Failed to add price');
                }
            }
            break;

            case 'updateIndividual':
            $id = intval($_POST['id'] ?? 0);
            $parameter_id = intval($_POST['parameter_id'] ?? 0);
            $test_charge = floatval($_POST['test_charge'] ?? 0);
            $is_active = intval($_POST['is_active'] ?? 1);

            if ($id <= 0) {
                throw new Exception('Invalid pricing ID');
            }
            if ($parameter_id <= 0) {
                throw new Exception('Please select a parameter');
            }
            if ($test_charge < 0) {
                throw new Exception('Price cannot be negative');
            }

            // Check current data
            $current = $model->getIndividualPriceById($id);
            if (!$current) {
                throw new Exception('Price not found');
            }

            // Check if anything changed
            if (
                $current['parameter_id'] == $parameter_id &&
                floatval($current['test_charge']) == $test_charge &&
                intval($current['is_active']) == $is_active
            ) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No changes detected'
                ]);
                exit;
            }

            // Check for duplicate if parameter changed
            if ($current['parameter_id'] != $parameter_id) {
                if ($model->hasIndividualPrice($parameter_id, $id)) {
                    throw new Exception('Another price already exists for this parameter');
                }
            }

            // Update
            if ($model->updateIndividualPrice($id, $parameter_id, $test_charge, $is_active)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Price updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update price');
            }
            break;

            case 'deleteIndividual':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid pricing ID');
            }

            if ($model->softDeleteIndividualPrice($id)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Price deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete price');
            }
            break;

            // ========== COMBO PRICING ==========

        case 'fetchAllCombos':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }

            $result = $model->getAllComboPrices($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result
            ]);
            break;

             case 'getComboById':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid combo ID');
            }

            $result = $model->getComboPriceById($id);
            if ($result) {
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                throw new Exception('Combo not found');
            }
            break;

    }
} catch (Exception $e) {
    //throw $th;
}
