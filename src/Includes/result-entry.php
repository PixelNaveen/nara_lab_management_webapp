<!-- ============================================================
   RESULT ENTRY PAGE
   Laboratory Management System
   Version 1.0 - Test Result Entry with Category-Aware Controls
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/result-entry.css">

<div class="container-fluid px-4 py-4">

    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">

                <!-- Search Input -->
                <input type="text"
                    class="form-control"
                    id="reSearchInput"
                    placeholder="Search by sample code or client name..."
                    style="max-width: 320px;"
                    autocomplete="off">

                <!-- Sample Status Filter -->
                <select class="form-select" id="reStatusFilter" style="max-width: 180px;">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress" selected>In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <!-- Date Presets -->
                <select class="form-select" id="reDatePreset" style="max-width: 180px;">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="last7">Last 7 Days</option>
                    <option value="last30">Last 30 Days</option>
                </select>

            </div>
        </div>
    </div>

    <!-- ==================== RESULTS TABLE CARD ==================== -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="resultsTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3 client-name-column">CLIENT</th>
                            <th class="px-3 py-3">STATUS</th>
                            <th class="px-3 py-3 text-center">ITEMS</th>
                            <th class="px-3 py-3 text-center">PROGRESS</th>
                            <th class="px-3 py-3">RECEIVED</th>
                            <th class="px-3 py-3 text-center" style="width: 160px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading samples...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="reEmptyState" class="empty-state" style="display: none;">
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; opacity: 0.5;"></i>
                <h5 class="text-muted">No samples found</h5>
                <p class="text-muted small">Try adjusting your search filters</p>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
   RESULT ENTRY MODAL
   Full-width modal for entering test results per sample
   ============================================================ -->

<div class="modal fade" id="resultEntryModal" tabindex="-1" aria-labelledby="resultEntryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="resultEntryModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Enter Results
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="re-modal-info" id="modalSampleInfo">
                        <span class="badge bg-light text-primary" id="modalSampleCode">-</span>
                        <span class="ms-2 text-white-50" id="modalClientName">-</span>
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" id="resultEntryBody">
                <!-- Dynamic content loaded by JS -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading form...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0 small">Loading test form...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary re-btn" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success re-btn" id="btnSaveResults" disabled>
                    <i class="bi bi-check-circle me-1"></i>Save All Results
                </button>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" id="modalResultSampleId" value="">

        </div>
    </div>
</div>

<!-- ============================================================
   TOAST NOTIFICATIONS CONTAINER
   ============================================================ -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080" id="reToastContainer">
    <!-- Toasts will be dynamically inserted here -->
</div>

<!-- ============================================================
   EXTERNAL JAVASCRIPT
   ============================================================ -->

<script src="../../public/assets/js/result-entry.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Result Entry View: DOM Loaded');

        if (typeof ResultEntry !== 'undefined') {
            try {
                ResultEntry.init();
                console.log('✅ ResultEntry Module: Initialized Successfully');
            } catch (error) {
                console.error('❌ ResultEntry Module: Initialization Failed', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger m-3';
                errorDiv.innerHTML = `
                <h5><i class="bi bi-exclamation-triangle"></i> Initialization Error</h5>
                <p>Failed to initialize Result Entry module. Please refresh the page.</p>
                <small>${error.message}</small>
            `;
                document.querySelector('.container-fluid').prepend(errorDiv);
            }
        } else {
            console.error('❌ ResultEntry Module: Not Found');
        }
    });
</script>