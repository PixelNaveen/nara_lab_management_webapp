<?php

/**
 * Dashboard Controller
 * Handles AJAX requests for dashboard metrics and charts.
 */

session_start();
require_once __DIR__ . '/../Models/RevenueModel.php';
require_once __DIR__ . '/../Models/SampleStatusModel.php';
require_once __DIR__ . '/../Models/DailySummaryModel.php';
require_once __DIR__ . '/../Models/TurnaroundModel.php';
require_once __DIR__ . '/../Models/DashboardModel.php';

header('Content-Type: application/json');

/**
 * Aggregate revenue trend data by weekly or monthly buckets.
 * Input: array of ['date' => ..., 'billed' => ..., 'paid' => ...]
 */
function aggregateRevenueTrend($rawData, $mode)
{
    if ($mode === 'daily') return $rawData;

    $buckets = [];
    foreach ($rawData as $item) {
        $dt = new DateTime($item['date']);
        if ($mode === 'weekly') {
            // Group by ISO week: "W12 (Mar)"
            $key = 'W' . $dt->format('W') . ' (' . $dt->format('M') . ')';
        } else {
            // monthly: "Apr 2026"
            $key = $dt->format('M Y');
        }
        if (!isset($buckets[$key])) {
            $buckets[$key] = ['date' => $key, 'billed' => 0, 'paid' => 0];
        }
        $buckets[$key]['billed'] += $item['billed'];
        $buckets[$key]['paid'] += $item['paid'];
    }
    return array_values($buckets);
}

/**
 * Aggregate intake trend data by weekly or monthly buckets.
 * Input: ['labels' => [...dates], 'data' => [...counts]]
 */
function aggregateIntakeTrend($rawData, $mode)
{
    if ($mode === 'daily') return $rawData;

    $labels = $rawData['labels'] ?? [];
    $data = $rawData['data'] ?? [];

    $buckets = [];
    $bucketOrder = [];

    for ($i = 0; $i < count($labels); $i++) {
        $dt = new DateTime($labels[$i]);
        if ($mode === 'weekly') {
            $key = 'W' . $dt->format('W') . ' (' . $dt->format('M') . ')';
        } else {
            $key = $dt->format('M Y');
        }
        if (!isset($buckets[$key])) {
            $buckets[$key] = 0;
            $bucketOrder[] = $key;
        }
        $buckets[$key] += $data[$i];
    }

    return [
        'labels' => $bucketOrder,
        'data' => array_values($buckets)
    ];
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'getDashboardData') {
    try {
        $dateFrom = $_POST['date_from'] ?? date('Y-m-01'); // Default to start of this month
        $dateTo = $_POST['date_to'] ?? date('Y-m-d'); // Default to today

        $revenueModel = new RevenueModel();
        $dailySummaryModel = new DailySummaryModel();
        $sampleStatusodel = new SampleStatusModel();
        $turnaroundModel = new TurnaroundModel();
        $dashboardModel = new DashboardModel();

        // 1. Get KPIs for the selected period
        $revenueSummary = $revenueModel->getRevenueSummary($dateFrom, $dateTo);
        $tatSummary = $turnaroundModel->getSummary(['date_from' => $dateFrom, 'date_to' => $dateTo]);

        // Today's KPIs
        $dailyKPIs = $dailySummaryModel->getDailyKPIs();

        // Calculate Completion Rate
        $totalForPeriod = $tatSummary['total_count'] ?? 0;
        $completedForPeriod = $tatSummary['completed_count'] ?? 0;
        $completionRate = $totalForPeriod > 0 ? round(($completedForPeriod / $totalForPeriod) * 100, 1) : 0;

        $kpis = [
            'total_samples' => $totalForPeriod,
            'total_revenue' => $revenueSummary['total_billed'],
            'outstanding_balance' => $revenueSummary['total_outstanding'],
            'completion_rate' => $completionRate,
            'avg_tat' => $tatSummary['avg_tat'],
            'today_intake' => $dailyKPIs['intakes']['total'],
            'today_water' => $dailyKPIs['intakes']['water'],
            'today_food' => $dailyKPIs['intakes']['food'],
            'today_swab' => $dailyKPIs['intakes']['swab'],
            'today_revenue' => $dailyKPIs['revenue']
        ];

        // 2. Get Chart Data
        $revenueTrendRaw = $revenueModel->getRevenueTrend($dateFrom, $dateTo);
        $statusDistribution = $turnaroundModel->getStatusDistribution(['date_from' => $dateFrom, 'date_to' => $dateTo]);
        $revenueByCategory = $revenueModel->getRevenueByCategory($dateFrom, $dateTo);
        $intakeTrendRaw = $dailySummaryModel->getIntakeTrend($dateFrom, $dateTo);

        // New dashboard model methods
        $categoryDistribution = $dashboardModel->getCategoryDistribution($dateFrom, $dateTo);
        $popularTests = $dashboardModel->getPopularTests($dateFrom, $dateTo);

        // --- Smart Aggregation Logic ---
        $start = new DateTime($dateFrom);
        $end = new DateTime($dateTo);
        $daysDiff = (int)$start->diff($end)->days;

        if ($daysDiff <= 31) {
            $aggregation = 'daily';
        } elseif ($daysDiff <= 90) {
            $aggregation = 'weekly';
        } else {
            $aggregation = 'monthly';
        }

        // Aggregate Revenue Trend
        $revenueTrend = aggregateRevenueTrend($revenueTrendRaw, $aggregation);
        // Aggregate Intake Trend
        $intakeTrend = aggregateIntakeTrend($intakeTrendRaw, $aggregation);

        $charts = [
            'revenue_trend' => $revenueTrend,
            'status_distribution' => $statusDistribution,
            'revenue_by_category' => $revenueByCategory,
            'intake_trend' => $intakeTrend,
            'category_distribution' => $categoryDistribution,
            'aggregation' => $aggregation
        ];

        // 3. Get Tables Data
        $recentSamples = $dailySummaryModel->getRecentIntakes();
        $topDebtorsRaw = $revenueModel->getDebtorsList($dateFrom, $dateTo);
        // Limit debtors to top 5 for dashboard
        $topDebtors = array_slice($topDebtorsRaw, 0, 5);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'kpis' => $kpis,
                'charts' => $charts,
                'tables' => [
                    'recent_samples' => $recentSamples,
                    'top_debtors' => $topDebtors,
                    'popular_tests' => $popularTests
                ]
            ]
        ]);
    } catch (Exception $e) {
        error_log("Dashboard Controller Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
