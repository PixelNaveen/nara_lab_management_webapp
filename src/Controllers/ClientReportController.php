<?php

/**
 * Client Reports (CRM) Controller
 * Laboratory Management System v2.0
 */

session_start();
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Models/ClientReportModel.php';

try {
    $model = new ClientReportModel();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB init failed: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ========== GET ALL CLIENTS ==========
    case 'getClients':
        try {
            $clients = $model->getAllClients();
            echo json_encode([
                'status' => 'success',
                'data'   => $clients
            ]);
        } catch (Exception $e) {
            error_log("CRM - getClients: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ========== GET CLIENT DATA ==========
    case 'getClientData':
        try {
            $clientId = intval($_POST['client_id'] ?? 0);

            if ($clientId <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Client ID']);
                exit;
            }

            $details = $model->getClientDetails($clientId);
            if (!$details) {
                echo json_encode(['status' => 'error', 'message' => 'Client not found (ID: ' . $clientId . ')']);
                exit;
            }

            $history  = $model->getClientHistory($clientId);
            $finances = $model->getFinancialSummary($clientId);
            $trends   = $model->getTestingTrends($clientId);

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'details'  => $details,
                    'history'  => $history,
                    'finances' => $finances,
                    'trends'   => $trends
                ]
            ]);
        } catch (Exception $e) {
            error_log("CRM - getClientData: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()   // expose actual error for debugging
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action: ' . htmlspecialchars($action)]);
        break;
}
