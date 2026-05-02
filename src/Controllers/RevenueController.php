<?php
/**
 * Revenue Analysis Controller
 * Laboratory Management System
 * AAA-Grade Implementation for Secure Data Endpoints
 */

require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);

require_once __DIR__ . '/../Models/RevenueModel.php';
require_once __DIR__ . '/../../Config/RolePermissions.php';

header('Content-Type: application/json');

class RevenueController
{
    private $model;
    private $currentUserRole;

    public function __construct()
    {
        // 1. Authentication Check
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        $this->currentUserRole = $_SESSION['role'];

        // Optional: specific permission check for viewing revenue
        // if (!RolePermissions::hasPermission($this->currentUserRole, 'view-revenue')) { ... }

        $this->model = new RevenueModel();
    }

    public function handleRequest()
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'getRevenueData':
                $this->getRevenueData();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
                break;
        }
    }

    private function getRevenueData()
    {
        // 2. CSRF Protection
        $headers = getallheaders();
        $frontendCsrf = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? '';
        if (empty($frontendCsrf) || !hash_equals($_SESSION['csrf_token'], $frontendCsrf)) {
            echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
            return;
        }

        // 3. Date Parsing
        $startDate = $_POST['start_date'] ?? date('Y-01-01'); // Default to YTD
        $endDate = $_POST['end_date'] ?? date('Y-m-d');

        try {
            // Aggregate all data into a single payload for blazing fast dashboard rendering
            $summary = $this->model->getRevenueSummary($startDate, $endDate);
            $categories = $this->model->getRevenueByCategory($startDate, $endDate);
            $debtors = $this->model->getDebtorsList($startDate, $endDate);
            $trend = $this->model->getRevenueTrend($startDate, $endDate);

            echo json_encode([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'categories' => $categories,
                    'debtors' => $debtors,
                    'trend' => $trend,
                    'period' => [
                        'start' => $startDate,
                        'end'   => $endDate
                    ]
                ]
            ]);

        } catch (Exception $e) {
            error_log("RevenueController Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to retrieve revenue data. Please contact support.'
            ]);
        }
    }
}

// Ensure execution starts here
$controller = new RevenueController();
$controller->handleRequest();
