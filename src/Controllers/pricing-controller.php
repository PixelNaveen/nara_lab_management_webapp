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
    }
} catch (Exception $e) {
    //throw $th;
}
