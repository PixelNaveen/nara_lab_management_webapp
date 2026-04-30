<?php
/**
 * Daily Summary Dashboard View
 * Provides real-time metrics for today's lab operations.
 */

// CSRF Token for AJAX Security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Link to Scoped CSS -->
<link rel="stylesheet" href="../../public/assets/css/report-daily.css">

<!-- Chart.js (Required for Visuals) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="daily-summary-page">
    <div class="container-fluid py-4">

        <!-- Premium Page Header -->
        <div class="ds-header mb-4">
            <div class="ds-header-left">
                <div class="ds-time-widget">
                    <div class="ds-clock-icon-wrapper">
                        <i class="bi bi-clock-fill"></i>
                        <span class="ds-pulse"></span>
                    </div>
                    <div class="ds-time-content">
                        <h2 id="dsLiveClock" class="ds-clock-text">--:--</h2>
                        <span class="ds-time-status">Live System Time</span>
                    </div>
                </div>
            </div>
            <div class="ds-header-right">
                <div class="ds-date-widget">
                    <div class="ds-date-day"><?php echo date('l'); ?></div>
                    <div class="ds-date-details">
                        <i class="bi bi-calendar3 me-1"></i> <?php echo date('F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for JS controller -->
        <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <!-- Loader Overlay -->
        <div id="dsMainLoader" class="ds-loader-overlay">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading Dashboard...</span>
            </div>
        </div>

        <!-- ================= KPI CARDS ROW ================= -->
        <div class="row g-4 mb-4">
            <!-- Samples Received Today -->
            <div class="col-xl-3 col-md-6">
                <div class="ds-kpi-card">
                    <div class="ds-kpi-icon ds-icon-blue">
                        <i class="fa-solid fa-vial"></i>
                    </div>
                    <div class="ds-kpi-details flex-grow-1">
                        <div class="ds-kpi-label">Intakes Today</div>
                        <div class="ds-kpi-value" id="dsKpiIntakeTotal">0</div>
                        <div class="ds-kpi-subtext" id="dsKpiIntakeSub">0 Regular / 0 Swabs</div>
                    </div>
                </div>
            </div>

            <!-- Tests Completed Today -->
            <div class="col-xl-3 col-md-6">
                <div class="ds-kpi-card">
                    <div class="ds-kpi-icon ds-icon-green">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div class="ds-kpi-details flex-grow-1">
                        <div class="ds-kpi-label">Completed Tests Today</div>
                        <div class="ds-kpi-value" id="dsKpiCompleted">0</div>
                        <div class="ds-kpi-subtext">Samples finished testing</div>
                    </div>
                </div>
            </div>

            <!-- Reports Generated Today -->
            <div class="col-xl-3 col-md-6">
                <div class="ds-kpi-card">
                    <div class="ds-kpi-icon ds-icon-purple">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div class="ds-kpi-details flex-grow-1">
                        <div class="ds-kpi-label">Reports Generated Today</div>
                        <div class="ds-kpi-value" id="dsKpiReports">0</div>
                        <div class="ds-kpi-subtext">Final PDF test reports created</div>
                    </div>
                </div>
            </div>

            <!-- Revenue Today -->
            <div class="col-xl-3 col-md-6">
                <div class="ds-kpi-card">
                    <div class="ds-kpi-icon ds-icon-orange">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <div class="ds-kpi-details flex-grow-1">
                        <div class="ds-kpi-label">Revenue Collected Today</div>
                        <div class="ds-kpi-value" id="dsKpiRevenue">LKR 0.00</div>
                        <div class="ds-kpi-subtext">Total from paid invoices today</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= CHARTS ROW ================= -->
        <div class="row g-4 mb-4">
            <!-- 7-Day Intake Trend -->
            <div class="col-lg-8">
                <div class="ds-chart-card">
                    <div class="ds-chart-card-title">
                        <i class="fa-solid fa-chart-line text-primary me-2"></i>7-Day Intake Trend
                    </div>
                    <div class="ds-chart-wrapper">
                        <canvas id="dsTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Intake Breakdown -->
            <div class="col-lg-4">
                <div class="ds-chart-card">
                    <div class="ds-chart-card-title">
                        <i class="fa-solid fa-chart-pie text-success me-2"></i>Today's Intake Breakdown
                    </div>
                    <div class="ds-chart-wrapper">
                        <canvas id="dsDonutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= DATA TABLES ROW ================= -->
        <div class="row g-4 mb-4">
            <!-- Recent Intakes Today -->
            <div class="col-xl-12">
                <div class="ds-table-card">
                    <div class="ds-table-header">
                        <h5 class="ds-table-title"><i class="fa-solid fa-clipboard-list text-secondary me-2"></i>Recent Intakes Today</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="DailySummary.refresh()">
                            <i class="fa-solid fa-rotate-right me-1"></i> Refresh
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="ds-table" id="dsRecentIntakeTable">
                            <thead>
                                <tr>
                                    <th>Sample Code</th>
                                    <th>Client Name</th>
                                    <th>Received Time</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Injected via JS -->
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Loading today's intakes...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
</div>

<!-- Link to Scoped JS Controller -->
<script src="../../public/assets/js/report-daily.js"></script>
