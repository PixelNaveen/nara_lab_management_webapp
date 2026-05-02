<!-- ============================================================
   RESULT ENTRY PAGE
   Laboratory Management System
   Version 2.0 - Fixed accordion, scoping, and validation
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/result-entry.css">

<div class="re-container">

    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="row g-3 align-items-center mb-4 mx-0">
        <div class="col-12">
            <div class="row g-2">
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="text"
                            id="reSearchInput"
                            class="form-control border-start-0 ps-0 shadow-none"
                            placeholder="Search by sample code or client name..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select shadow-sm rounded-3 cursor-pointer" id="reStatusFilter">
                        <option value="all" selected>All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select shadow-sm rounded-3 cursor-pointer" id="reDatePreset">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="last7">Last 7 Days</option>
                        <option value="last30">Last 30 Days</option>
                    </select>
                </div>

                <div class="col-12 col-md-1 col-lg-2">
                    <button class="btn btn-outline-secondary btn-re-reset shadow-sm rounded-3 w-100 bg-white" onclick="location.reload()">
                        <i class="fas fa-undo-alt me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== RESULTS TABLE CARD ==================== -->
    <div class="re-table-container">
        <table class="re-table" id="resultsTable">
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

</div><!-- /.re-container -->


<!-- ============================================================
   RESULT ENTRY MODAL
   Full-width modal for entering test results per sample
   ============================================================ -->

<div class="modal fade" id="resultEntryModal" tabindex="-1"
    aria-labelledby="resultEntryModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="resultEntryModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Enter Results
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="re-modal-info" id="modalSampleInfo">
                        <span class="badge bg-light text-primary" id="modalSampleCode">-</span>
                        <span class="ms-2 text-white-50" id="modalClientName">-</span>
                    </span>
                    <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
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
<script src="/public/assets/js/result-entry.js"></script>