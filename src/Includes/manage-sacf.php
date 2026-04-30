<link rel="stylesheet" href="public/assets/css/sample-records.css">

<div class="container-fluid px-4 py-4">

    <!-- Enterprise Grade Filter Bar -->
    <div class="row g-3 align-items-center mb-4 mx-0">
        <div class="col-12">
            <div class="row g-2">
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search by Sample Code or Client...">
                    </div>
                </div>
                
                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select shadow-sm rounded-3 cursor-pointer" id="dateFilter">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select shadow-sm rounded-3 cursor-pointer" id="paymentStatusFilter">
                        <option value="">All Payments</option>
                        <option value="Paid">Paid</option>
                        <option value="Not Paid">Not Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 col-lg-2">
                    <button class="btn btn-outline-secondary shadow-sm rounded-3 w-100 bg-white" id="btnResetFilters" type="button">
                        <i class="fas fa-undo-alt me-1"></i> Reset
                    </button>
                </div>

                <!-- Custom Date Range (Hidden by default) -->
                <div id="customDateForm" class="col-12 col-lg-4 d-none mt-2 mt-lg-0">
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="customDateFrom" class="form-control shadow-sm rounded-3" title="From Date">
                        <span class="text-muted fw-medium">to</span>
                        <input type="date" id="customDateTo" class="form-control shadow-sm rounded-3" title="To Date">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="formsTable" data-form-type="sacf">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3 client-name-column">CLIENT</th>
                            <th class="px-3 py-3">PAYMENT STATUS</th>
                            <th class="px-3 py-3">RECEIVED DATE</th>
                            <th class="px-3 py-3 text-center" style="width: 180px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading samples...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No records found</h5>
            </div>
        </div>
    </div>
</div>

<script src="public/assets/js/manage-forms.js"></script>
