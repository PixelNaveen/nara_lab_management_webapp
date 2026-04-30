<!-- Dashboard content wrapped in container -->
<div class="dashboard-container" id="dashboard-container">
    <div class="dashboard-content-wrapper">
        <!-- Page Header & Period Selector -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <!-- <h1 class="page-title mb-1" style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">Command Center</h1>
            <p class="text-muted mb-0" style="font-size: 0.875rem;">Real-time laboratory performance metrics</p> -->
            </div>

            <div class="period-selector d-flex align-items-center gap-2">
                <div class="btn-group shadow-sm" role="group">
                    <button type="button" class="btn period-btn active" data-period="this_week">This Week</button>
                    <button type="button" class="btn period-btn" data-period="this_month">This Month</button>
                    <button type="button" class="btn period-btn" data-period="last_90">Last 90 Days</button>
                    <button type="button" class="btn period-btn" data-period="this_year">This Year</button>
                    <button type="button" class="btn period-btn" data-period="custom">Custom</button>
                </div>

                <div id="custom-date-container" class="d-none d-flex gap-2 align-items-center bg-white p-1 rounded shadow-sm border">
                    <input type="date" id="customDateFrom" class="form-control form-control-sm border-0" style="width: 130px;">
                    <span class="text-muted">to</span>
                    <input type="date" id="customDateTo" class="form-control form-control-sm border-0" style="width: 130px;">
                    <button id="btnApplyCustomDate" class="btn btn-sm btn-primary">Apply</button>
                    <button id="btnResetCustomDate" class="btn btn-sm btn-light text-muted" title="Close & Reset"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>

        <!-- KPI Grid (Row 1) -->
        <div class="kpi-grid mb-4">
            <!-- Samples Card -->
            <div class="kpi-card border-top-primary shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Total Samples</div>
                        <div class="kpi-icon bg-primary-light text-primary">
                            <i class="fas fa-flask"></i>
                        </div>
                    </div>
                    <div class="kpi-value" id="kpi-total-samples">0</div>
                    <div class="kpi-subtitle">For selected period</div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="kpi-card border-top-success shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Billed Revenue</div>
                        <div class="kpi-icon bg-success-light text-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="kpi-value text-success" id="kpi-total-revenue">Rs. 0.00</div>
                    <div class="kpi-subtitle">For selected period</div>
                </div>
            </div>

            <!-- Outstanding Card -->
            <div class="kpi-card border-top-danger shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Outstanding Balance</div>
                        <div class="kpi-icon bg-danger-light text-danger">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="kpi-value text-danger" id="kpi-outstanding">Rs. 0.00</div>
                    <div class="kpi-subtitle">Unpaid for period</div>
                </div>
            </div>

            <!-- Completion Rate Card -->
            <div class="kpi-card border-top-info shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Completion Rate</div>
                        <div class="kpi-icon bg-info-light text-info">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                    <div class="kpi-value" id="kpi-completion">0%</div>
                    <div class="kpi-subtitle">Of period intake</div>
                </div>
            </div>

            <!-- Turnaround Time Card -->
            <div class="kpi-card border-top-purple shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Avg. Turnaround</div>
                        <div class="kpi-icon bg-purple-light text-purple">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                    </div>
                    <div class="kpi-value" id="kpi-tat">0 days</div>
                    <div class="kpi-subtitle">Completed samples</div>
                </div>
            </div>

            <!-- Today Intake Card -->
            <div class="kpi-card border-top-warning shadow-sm">
                <div class="kpi-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="kpi-title">Today's Intake</div>
                        <div class="kpi-icon bg-warning-light text-warning">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="kpi-value" id="kpi-today-intake">0</div>
                    <div class="kpi-subtitle">Samples received today</div>
                </div>
            </div>
        </div>

        <!-- Today's Operational Sub-KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card bg-white shadow-sm border-0 rounded-3">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Today: Water</span>
                            <h4 class="mb-0 fw-bold" id="kpi-today-water">0</h4>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-tint fs-6"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card bg-white shadow-sm border-0 rounded-3">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Today: Food</span>
                            <h4 class="mb-0 fw-bold" id="kpi-today-food">0</h4>
                        </div>
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-hamburger fs-6"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card bg-white shadow-sm border-0 rounded-3">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Today: Swabs</span>
                            <h4 class="mb-0 fw-bold" id="kpi-today-swab">0</h4>
                        </div>
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-microscope fs-6"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card bg-white shadow-sm border-0 rounded-3">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Today: Revenue</span>
                            <h4 class="mb-0 fw-bold text-success" id="kpi-today-revenue">Rs. 0</h4>
                        </div>
                        <div class="bg-success-light text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-coins fs-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Financial Trend + Status Distribution -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Financial Trend</h3>
                            <p class="chart-subtitle" id="trendChartSubtitle">Billed vs Paid Revenue over time</p>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="chart-card shadow-sm">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title"><i class="fas fa-chart-pie me-2 text-info"></i>Status Distribution</h3>
                            <p class="chart-subtitle">Samples by current status</p>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Revenue by Category + Sample Categories Pie -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title"><i class="fas fa-chart-bar me-2 text-success"></i>Revenue by Category</h3>
                            <p class="chart-subtitle">Water vs Food vs Swabs</p>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title"><i class="fas fa-chart-pie me-2 text-warning"></i>Sample Categories</h3>
                            <p class="chart-subtitle">Submission distribution for period</p>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row: Recent Intakes + Outstanding Accounts -->
        <div class="row g-4 mb-4">
            <!-- Recent Samples -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;"><i class="fas fa-clock me-2 text-primary"></i>Today's Recent Intakes</h5>
                        <a href="index.php?page=sample-records-view" class="btn btn-sm btn-light border text-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-3 fw-semibold">Sample Code</th>
                                        <th class="fw-semibold">Client</th>
                                        <th class="fw-semibold">Status</th>
                                        <th class="pe-3 fw-semibold"></th>
                                    </tr>
                                </thead>
                                <tbody id="recentSamplesBody">
                                    <!-- Populated by JS -->
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading recent samples...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Debtors -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Top Outstanding Accounts</h5>
                        <a href="index.php?page=report-revenue" class="btn btn-sm btn-light border text-primary">Details</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-3 fw-semibold">Client</th>
                                        <th class="fw-semibold">Sample</th>
                                        <th class="fw-semibold">Age</th>
                                        <th class="pe-3 text-end fw-semibold">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="topDebtorsBody">
                                    <!-- Populated by JS -->
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading debtors data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Row: Intake Trend + Top Tests -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title"><i class="fas fa-chart-area me-2 text-purple"></i>Intake Trend</h3>
                            <p class="chart-subtitle" id="intakeChartSubtitle">Samples received for selected period</p>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="intakeTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;"><i class="fas fa-vial me-2 text-purple"></i>Top Requested Tests</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-3 fw-semibold">#</th>
                                        <th class="fw-semibold">Test Name</th>
                                        <th class="pe-3 text-end fw-semibold">Count</th>
                                    </tr>
                                </thead>
                                <tbody id="popularTestsBody">
                                    <!-- Populated by JS -->
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading tests...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Load Chart.js if not already loaded (assuming it might be loaded globally, but just in case) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Load Dashboard specific JS -->
<script src="public/assets/js/dashboard.js?v=<?= time() ?>"></script>