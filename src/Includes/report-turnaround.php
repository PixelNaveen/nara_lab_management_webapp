<!-- ============================================================
   TURNAROUND TIME (TAT) REPORT
   Laboratory Management System
   Version 1.0 - ISO 17025 TAT Analytics Dashboard
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/report-turnaround.css">

<div class="tat-container">

    <!-- ==================== PAGE TITLE ==================== -->
    <!-- <h2 class="tat-page-title">
        <i class="bi bi-stopwatch"></i> Turnaround Time Report
    </h2> -->

    <!-- ==================== FILTER SECTION ==================== -->
    <div class="tat-filter-card">
        <!-- Search -->
        <input type="text" id="tatSearch" placeholder="Search sample code or client..." style="max-width: 240px;" autocomplete="off">

        <!-- Status Filter -->
        <select id="tatStatusFilter" style="max-width: 160px;">
            <option value="all">All Status</option>
            <option value="Completed">Completed</option>
            <option value="In Progress">In Progress</option>
            <option value="Pending">Pending</option>
        </select>

        <!-- Date Preset -->
        <select id="tatDatePreset" style="max-width: 150px;">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="last7">Last 7 Days</option>
            <option value="last30" selected>Last 30 Days</option>
            <option value="last90">Last 90 Days</option>
        </select>

        <!-- Custom Date Range -->
        <input type="date" id="tatDateFrom" title="From date" style="max-width: 145px;">
        <input type="date" id="tatDateTo" title="To date" style="max-width: 145px;">

        <!-- Buttons -->
        <button class="tat-btn tat-btn-filter" onclick="TATReport.load()">
            <i class="bi bi-funnel-fill"></i> Filter
        </button>
        <button class="tat-btn tat-btn-reset" onclick="TATReport.reset()">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>
    </div>

    <!-- ==================== KPI CARDS ==================== -->
    <div class="tat-kpi-grid" id="tatKpiGrid">
        <!-- Average TAT -->
        <div class="tat-kpi-card">
            <div class="tat-kpi-icon kpi-avg"><i class="bi bi-speedometer2"></i></div>
            <div class="tat-kpi-info">
                <h4>Avg. Turnaround</h4>
                <p class="tat-kpi-value" id="kpiAvgTat">--</p>
                <span class="tat-kpi-unit">days</span>
            </div>
        </div>

        <!-- Completed -->
        <div class="tat-kpi-card">
            <div class="tat-kpi-icon kpi-done"><i class="bi bi-check-circle-fill"></i></div>
            <div class="tat-kpi-info">
                <h4>Completed</h4>
                <p class="tat-kpi-value" id="kpiCompleted">--</p>
                <span class="tat-kpi-unit">samples</span>
            </div>
        </div>

        <!-- SLA Breaches -->
        <div class="tat-kpi-card">
            <div class="tat-kpi-icon kpi-breach"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="tat-kpi-info">
                <h4>SLA Breaches</h4>
                <p class="tat-kpi-value" id="kpiBreached">--</p>
                <span class="tat-kpi-unit">exceeded deadline</span>
            </div>
        </div>

        <!-- On Time -->
        <div class="tat-kpi-card">
            <div class="tat-kpi-icon kpi-ontime"><i class="bi bi-clock-fill"></i></div>
            <div class="tat-kpi-info">
                <h4>On Time</h4>
                <p class="tat-kpi-value" id="kpiOnTime">--</p>
                <span class="tat-kpi-unit">within deadline</span>
            </div>
        </div>

        <!-- Pending / In Progress -->
        <div class="tat-kpi-card">
            <div class="tat-kpi-icon kpi-pending"><i class="bi bi-hourglass-split"></i></div>
            <div class="tat-kpi-info">
                <h4>Pending / Active</h4>
                <p class="tat-kpi-value" id="kpiPending">--</p>
                <span class="tat-kpi-unit">awaiting completion</span>
            </div>
        </div>
    </div>

    <!-- ==================== CHARTS ==================== -->
    <div class="tat-charts-row">
        <div class="tat-chart-card">
            <h3><i class="bi bi-pie-chart-fill me-1"></i> Status Distribution</h3>
            <div class="tat-chart-canvas-wrap">
                <canvas id="tatStatusChart"></canvas>
            </div>
        </div>
        <div class="tat-chart-card">
            <h3><i class="bi bi-bar-chart-fill me-1"></i> TAT Distribution (Days)</h3>
            <div class="tat-chart-canvas-wrap">
                <canvas id="tatDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ==================== DETAILED TABLE ==================== -->
    <div class="tat-table-card">
        <div class="tat-table-header">
            <h3><i class="bi bi-table me-1"></i> Sample TAT Details</h3>
            <span class="tat-count-badge" id="tatRowCount">0</span>
        </div>
        <div class="tat-table-wrap">
            <table class="tat-table" id="tatDataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sample Code</th>
                        <th>Client</th>
                        <th>Received</th>
                        <th>Deadline</th>
                        <th>Completed</th>
                        <th>TAT (Days)</th>
                        <th>Delay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tatTableBody">
                    <tr>
                        <td colspan="9" class="tat-loading">
                            <i class="bi bi-arrow-repeat me-1"></i> Loading data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js CDN (already in index.php, but safe to include as fallback) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- TAT Report JavaScript -->
<script src="../../public/assets/js/report-turnaround.js"></script>