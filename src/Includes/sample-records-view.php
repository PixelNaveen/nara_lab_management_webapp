<!-- Sample Status Management View - FIXED VERSION -->
<div class="container-fluid px-4 py-3">
    <div class="row">
        <div class="col-12">
            
            <!-- Filter Pills and Search Section -->
            <div class="row mb-3 g-3 align-items-end">
                <!-- Status Filter Pills -->
                <div class="col-12 col-lg-8">
                    <label class="form-label text-muted small mb-2">
                        <i class="bi bi-funnel"></i> Filter by Status
                    </label>
                    <div class="status-filter-pills">
                        <button class="filter-pill active" data-status="all">
                            All <span class="pill-count" id="count-all">0</span>
                        </button>
                        <button class="filter-pill" data-status="Pending">
                            Pending <span class="pill-count" id="count-pending">0</span>
                        </button>
                        <button class="filter-pill" data-status="In Progress">
                            In Progress <span class="pill-count" id="count-inprogress">0</span>
                        </button>
                        <button class="filter-pill" data-status="Completed">
                            Completed <span class="pill-count" id="count-completed">0</span>
                        </button>
                        <button class="filter-pill" data-status="Cancelled">
                            Cancelled <span class="pill-count" id="count-cancelled">0</span>
                        </button>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="col-12 col-lg-4">
                    <label class="form-label text-muted small mb-2">
                        <i class="bi bi-search"></i> Search Samples
                    </label>
                    <div class="search-box-wrapper">
                        <i class="bi bi-search search-icon-left"></i>
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="form-control search-input-field" 
                            placeholder="Search by client name or code..."
                            autocomplete="off"
                        >
                        <button id="clearSearch" class="clear-search-btn" style="display: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Samples Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="bi bi-clipboard-data text-primary me-2"></i>
                            Sample Status Management
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark px-3 py-2" id="totalSamplesCount">
                                <i class="bi bi-file-earmark-text me-1"></i>0 Samples
                            </span>
                            <button id="refreshSamples" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="samplesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-3 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                        Sample Code
                                    </th>
                                    <th class="px-3 py-3 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                        Client
                                    </th>
                                    <th class="px-3 py-3 text-uppercase fw-semibold text-end" style="font-size: 0.75rem;">
                                        Total Charge
                                    </th>
                                    <th class="px-3 py-3 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                        Status
                                    </th>
                                    <th class="px-3 py-3 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                        Received Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="samplesTableBody">
                                <!-- Dynamic content loaded via JavaScript -->
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2 mb-0">Loading samples...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i> 
                            Click on status dropdown to update
                        </small>
                        <small class="text-muted">
                            <i class="bi bi-clock-history me-1"></i> 
                            Last updated: <span id="lastUpdated">Never</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="statusToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Required Scripts -->
<script>
    // ✅ FIXED: Correct controller path based on folder structure
    const CONTROLLER_PATH = 'src/Controllers/sample-records-controller.php';
    
    // Debug logging
    console.log('🔧 Controller Path:', CONTROLLER_PATH);
    console.log('📍 Current Location:', window.location.href);
</script>