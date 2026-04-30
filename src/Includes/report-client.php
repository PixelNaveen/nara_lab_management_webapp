<!-- ======================================================
   CLIENT REPORTS (CRM) MODULE
   Laboratory Management System v2.0
   Fixed column refs, updated to project design system
   ====================================================== -->

<link rel="stylesheet" href="../../public/assets/css/report-client.css">

<div class="crm-report-container">

    <!-- ===== PAGE HEADER ===== -->
    <!-- <div class="crm-page-header">
        <h2><i class="bi bi-person-badge-fill"></i> Client Analytics &amp; CRM</h2>
    </div> -->

    <!-- ===== SELECTOR BAR ===== -->
    <div class="crm-selector-bar">
        <label for="crmClientInput"><i class="bi bi-building me-1"></i>Select Client:</label>
        <div class="crm-client-wrapper">
            <input type="text" id="crmClientInput" class="crm-client-input" placeholder="Loading clients..." disabled autocomplete="off">
            <input type="hidden" id="crmClientSelect" value="">
            <div id="crmClientDropdown" class="crm-client-dropdown"></div>
        </div>
        <button class="crm-btn-load" id="crmBtnLoad" disabled>
            <i class="bi bi-search"></i> Load Profile
        </button>
    </div>

    <!-- ===== INITIAL EMPTY STATE ===== -->
    <div id="crmEmptyState" class="crm-empty-state">
        <i class="bi bi-person-lines-fill"></i>
        <h4>No Client Selected</h4>
        <p>Select a client above and click "Load Profile" to view their submission history, financial summary, and most-tested parameters.</p>
    </div>

    <!-- ===== LIVE DASHBOARD (hidden until client is loaded) ===== -->
    <div id="crmDashboard" class="crm-dashboard">

        <!-- CLIENT PROFILE HEADER + FINANCES -->
        <div class="crm-profile-card">
            <div class="crm-profile-info">
                <h3 id="crmClientName">—</h3>
                <div class="crm-profile-meta">
                    <div class="crm-meta-item"><i class="bi bi-telephone-fill"></i><span id="crmClientPhone">—</span></div>
                    <div class="crm-meta-item"><i class="bi bi-person-fill"></i><span>Contact: <strong id="crmClientContact">—</strong></span></div>
                    <div class="crm-meta-item"><i class="bi bi-geo-alt-fill"></i><span id="crmClientCity">—</span></div>
                    <div class="crm-meta-item"><i class="bi bi-calendar-event"></i><span>Since <strong id="crmClientSince">—</strong></span></div>
                </div>
            </div>

            <div class="crm-finance-strip">
                <div class="crm-finance-box">
                    <span class="crm-fin-label">Total Billed</span>
                    <span class="crm-fin-value" id="crmFinBilled">LKR 0.00</span>
                </div>
                <div class="crm-finance-box">
                    <span class="crm-fin-label">Paid</span>
                    <span class="crm-fin-value crm-fin-paid" id="crmFinPaid">LKR 0.00</span>
                </div>
                <div class="crm-finance-box">
                    <span class="crm-fin-label">Outstanding</span>
                    <span class="crm-fin-value crm-fin-due" id="crmFinDue">LKR 0.00</span>
                </div>
                <div class="crm-finance-box">
                    <span class="crm-fin-label">Samples</span>
                    <span class="crm-fin-value" id="crmFinTotal">0</span>
                </div>
            </div>
        </div>

        <!-- TABS NAVIGATION -->
        <div class="crm-tabs-bar">
            <button class="crm-tab-btn active" data-target="tabHistory">
                <i class="bi bi-clock-history"></i> Submission History
            </button>
            <button class="crm-tab-btn" data-target="tabTrends">
                <i class="bi bi-bar-chart-steps"></i> Testing Trends
            </button>
        </div>

        <!-- TAB: HISTORY -->
        <div id="tabHistory" class="crm-tab-content active">
            <div class="crm-table-card">
                <div class="crm-table-card-header">
                    <h5><i class="bi bi-file-earmark-medical"></i> Sample Submission History</h5>
                    <span class="crm-count-chip" id="crmHistoryCount">0</span>
                </div>
                <div class="crm-table-wrap">
                    <table class="crm-data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Form No.</th>
                                <th>Sample Code</th>
                                <th>Received</th>
                                <th>Deadline</th>
                                <th>Completed</th>
                                <th>Lab Status</th>
                                <th>Payment</th>
                                <th>Billed (LKR)</th>
                            </tr>
                        </thead>
                        <tbody id="crmHistoryBody">
                            <tr class="crm-no-data-row">
                                <td colspan="9"><i class="bi bi-hourglass-split me-1"></i> Loading history...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: TESTING TRENDS -->
        <div id="tabTrends" class="crm-tab-content">
            <div class="crm-table-card" style="margin-top:16px;">
                <div class="crm-table-card-header">
                    <h5><i class="bi bi-graph-up-arrow"></i> Most Frequently Tested Parameters</h5>
                </div>
                <div class="crm-trends-grid" id="crmTrendsGrid">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>

    </div><!-- end #crmDashboard -->

</div><!-- end .crm-report-container -->

<script src="../../public/assets/js/report-client.js"></script>