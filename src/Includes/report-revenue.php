<?php
/**
 * Revenue Analysis View
 * Laboratory Management System
 */

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$defaultStart = date('Y-01-01');
$defaultEnd   = date('Y-m-d');
?>

<!-- Load CSS -->
<link rel="stylesheet" href="public/assets/css/report-revenue.css">

<!-- Loading Overlay -->
<div id="revenueLoader">
    <div class="rev-loader-spinner"></div>
    <p>Analyzing Financial Data…</p>
</div>

<!-- CSRF token for JS -->
<input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

<div class="revenue-page py-4 px-3">

    <!-- ==================== FILTER BAR ==================== -->
    <div class="rev-filter-bar">
        <i class="bi bi-calendar3 text-secondary"></i>
        <span class="rev-filter-label">Period:</span>

        <input type="date" id="revStartDate" value="<?php echo $defaultStart; ?>">
        <span style="color:#94a3b8;font-weight:600;">to</span>
        <input type="date" id="revEndDate" value="<?php echo $defaultEnd; ?>">

        <div style="width:1px;height:28px;background:#e2e8f0;margin:0 4px;"></div>

        <button class="rev-btn-preset active" id="btnYTD" data-range="ytd">YTD</button>
        <button class="rev-btn-preset" id="btnMTD" data-range="mtd">MTD</button>
        <button class="rev-btn-preset" id="btnToday" data-range="today">Today</button>

        <button class="rev-btn-analyze ms-auto" id="btnFetchRevenue">
            <i class="bi bi-arrow-clockwise"></i> Analyze
        </button>
    </div>

    <!-- ==================== KPI CARDS ==================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="rev-kpi-card">
                <div class="rev-kpi-icon icon-blue">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <span class="rev-kpi-label">Total Billed</span>
                    <span class="rev-kpi-value" id="kpiBilled">LKR 0.00</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="rev-kpi-card">
                <div class="rev-kpi-icon icon-green">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <span class="rev-kpi-label">Total Collected</span>
                    <span class="rev-kpi-value text-success" id="kpiCollected">LKR 0.00</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="rev-kpi-card">
                <div class="rev-kpi-icon icon-rose">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <span class="rev-kpi-label">Outstanding Debt</span>
                    <span class="rev-kpi-value text-danger" id="kpiOutstanding">LKR 0.00</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="rev-kpi-card">
                <div class="rev-kpi-icon icon-amber">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <span class="rev-kpi-label">Invoices Issued</span>
                    <span class="rev-kpi-value" id="kpiInvoices">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== CHARTS ==================== -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="rev-card">
                <div class="rev-card-header">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <h5>Revenue Trend (Billed vs. Collected)</h5>
                </div>
                <div class="rev-chart-wrapper">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="rev-card">
                <div class="rev-card-header">
                    <i class="bi bi-pie-chart-fill"></i>
                    <h5>Revenue by Category</h5>
                </div>
                <div class="rev-chart-wrapper">
                    <canvas id="revenueCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== DEBTORS TABLE ==================== -->
    <div class="row g-3">
        <div class="col-12">
            <div class="rev-card">
                <div class="rev-card-header">
                    <i class="bi bi-people-fill text-danger" style="color:#dc2626 !important;"></i>
                    <h5 style="color:#dc2626;">Outstanding Debtors List</h5>
                </div>
                <div class="table-responsive">
                    <table class="rev-debtors-table" id="debtorsTable">
                        <thead>
                            <tr>
                                <th>Sample Code</th>
                                <th>Date Received</th>
                                <th>Client Name</th>
                                <th>Contact</th>
                                <th>Days O/S</th>
                                <th>Amount Due</th>
                            </tr>
                        </thead>
                        <tbody id="debtorsTbody">
                            <tr>
                                <td colspan="6" class="rev-empty-state">
                                    <i class="bi bi-hourglass-split"></i>
                                    Loading debtors...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Module JS -->
<script src="public/assets/js/report-revenue.js"></script>
