<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Models/DailySummaryModel.php';

// Check Auth & CSRF
if (!isset($_SESSION['user_id'])) {
    echo JSON_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo JSON_encode(['status' => 'error', 'message' => 'CSRF Token Validation Failed']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $model = new DailySummaryModel();

    try {
        switch ($action) {
            case 'getDashboardData':
                $kpis = $model->getDailyKPIs();
                $trend = $model->getIntakeTrend();
                $recentIntakes = $model->getRecentIntakes();
                
                echo JSON_encode([
                    'status' => 'success',
                    'data' => [
                        'kpis' => $kpis,
                        'trend' => $trend,
                        'recentIntakes' => $recentIntakes
                    ]
                ]);
                break;
                
            default:
                echo JSON_encode(['status' => 'error', 'message' => 'Invalid action passed']);
                break;
        }
    } catch (Exception $e) {
        error_log("DailySummaryController Error: " . $e->getMessage());
        echo JSON_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
    }
} else {
    echo JSON_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
}
?>
