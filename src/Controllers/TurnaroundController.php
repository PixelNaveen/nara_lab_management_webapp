<?php

/**
 * Turnaround Time (TAT) Report Controller
 * Laboratory Management System
 *
 * Handles AJAX requests for TAT analytics:
 * - getSummary: KPI cards data
 * - getDetails: Full sample table data
 * - getStatusDistribution: For pie chart
 *
 * @version 1.0
 */

require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Models/TurnaroundModel.php';

$model = new TurnaroundModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Extract common filters from request.
 */
function extractFilters()
{
    return [
        'search'      => trim($_POST['search'] ?? $_GET['search'] ?? ''),
        'status'      => trim($_POST['status'] ?? $_GET['status'] ?? 'all'),
        'date_preset' => trim($_POST['date_preset'] ?? $_GET['date_preset'] ?? ''),
        'date_from'   => trim($_POST['date_from'] ?? $_GET['date_from'] ?? ''),
        'date_to'     => trim($_POST['date_to'] ?? $_GET['date_to'] ?? '')
    ];
}

switch ($action) {

    // ========== SUMMARY KPIs ==========
    case 'getSummary':
        try {
            $filters = extractFilters();
            $summary = $model->getSummary($filters);

            echo json_encode([
                'status' => 'success',
                'data'   => $summary
            ]);
        } catch (Exception $e) {
            error_log("TAT Controller - getSummary Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch summary'
            ]);
        }
        break;

    // ========== DETAILED TABLE DATA ==========
    case 'getDetails':
        try {
            $filters = extractFilters();
            $details = $model->getDetailedData($filters);

            echo json_encode([
                'status' => 'success',
                'data'   => $details,
                'count'  => count($details)
            ]);
        } catch (Exception $e) {
            error_log("TAT Controller - getDetails Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch details'
            ]);
        }
        break;

    // ========== STATUS DISTRIBUTION (for chart) ==========
    case 'getStatusDistribution':
        try {
            $filters = extractFilters();
            $dist = $model->getStatusDistribution($filters);

            echo json_encode([
                'status' => 'success',
                'data'   => $dist
            ]);
        } catch (Exception $e) {
            error_log("TAT Controller - getStatusDistribution Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch distribution'
            ]);
        }
        break;

    // ========== UNKNOWN ==========
    default:
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid action: ' . htmlspecialchars($action)
        ]);
        break;
}
