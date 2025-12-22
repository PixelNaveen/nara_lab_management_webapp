<!-- ============================================================
   SAMPLE RECORDS VIEW WITH PAYMENT SYSTEM
   Laboratory Management System
   Version 2.0 - Payment System Fully Integrated
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/sample-records.css">

<div class="container-fluid px-4 py-4">

    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">

                <!-- Search Input -->
                <input type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Search by sample code or client name..."
                    style="max-width: 320px;"
                    autocomplete="off">

                <!-- Sample Status Filter -->
                <select class="form-select" id="statusFilter" style="max-width: 180px;">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <!-- Payment Status Filter (NEW) -->
                <select class="form-select" id="paymentStatusFilter" style="max-width: 180px;">
                    <option value="all">All Payments</option>
                    <option value="Pending">Payment Pending</option>
                    <option value="Not Paid">Not Paid</option>
                    <option value="Paid">Paid</option>
                </select>

                <!-- Date Presets -->
                <select class="form-select" id="datePreset" style="max-width: 180px;">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="last7">Last 7 Days</option>
                    <option value="last30">Last 30 Days</option>
                    <!-- <option value="custom">Custom Range</option> -->
                </select>

                <!-- Custom Date Range (Initially Hidden) -->
                <div id="customDateRange" style="display: none; margin-left: 10px;" class="d-flex gap-2">
                    <input type="date" class="form-control" id="dateFrom" style="max-width: 160px;">
                    <input type="date" class="form-control" id="dateTo" style="max-width: 160px;">
                    <button id="btnResetFilters" class="btn-sample-records btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== SAMPLES TABLE CARD ==================== -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="samplesTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3 client-name-column">CLIENT</th>
                            <th class="px-3 py-3">STATUS</th>
                            <th class="px-3 py-3">PAYMENT</th> <!-- NEW COLUMN -->
                            <th class="px-3 py-3">RECEIVED DATE</th>
                            <th class="px-3 py-3 text-end">AMOUNT (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading samples...</p>
                            </td>
                        </tr>
                    </tbody>
                    <!-- <tfoot>
                        <tr class="grand-total-row">
                            <td colspan="5" class="text-end px-3 py-3">
                                <strong>Grand Total:</strong>
                            </td>
                            <td class="text-end px-3 py-3">
                                <strong id="grandTotal" class="text-success">LKR 0.00</strong>
                            </td>
                        </tr>
                        <tr class="paid-total-row">
                            <td colspan="5" class="text-end px-3 py-3">
                                <strong>Paid Total:</strong>
                            </td>
                            <td class="text-end px-3 py-3">
                                <strong id="paidTotal" class="text-success">LKR 0.00</strong>
                            </td>
                        </tr>
                        <tr class="unpaid-total-row">
                            <td colspan="5" class="text-end px-3 py-3">
                                <strong>Unpaid Total:</strong>
                            </td>
                            <td class="text-end px-3 py-3">
                                <strong id="unpaidTotal" class="text-danger">LKR 0.00</strong>
                            </td>
                        </tr>
                    </tfoot> -->
                </table>
            </div>

            <!-- Empty State (Hidden by default, shown when no results) -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No samples found</h5>
                <p class="text-muted small">Try adjusting your search filters</p>
            </div>

        </div>
    </div>

</div>

<!-- ============================================================
   PAYMENT STATUS UPDATE MODAL
   Allows updating payment status with reference number
   ============================================================ -->

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-money-check-alt me-2"></i>Update Payment Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                
                <!-- Sample Information Display -->
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <strong>Sample Code:</strong>
                            <span id="modalSampleCode" class="ms-2">-</span>
                        </div>
                        <div class="col-12 mb-2">
                            <strong>Client:</strong>
                            <span id="modalClientName" class="ms-2">-</span>
                        </div>
                        <div class="col-12">
                            <strong>Amount:</strong>
                            <span id="modalAmount" class="ms-2 text-success fw-bold">LKR 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Selection -->
                <div class="mb-3">
                    <label for="modalPaymentStatus" class="form-label fw-semibold">
                        Payment Status <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="modalPaymentStatus" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Not Paid">Not Paid</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <small class="form-text text-muted">
                        ⚠️ Note: Once marked as Paid, it cannot be changed back.
                    </small>
                </div>

                <!-- Reference Number Input (Hidden by default, shown when "Paid" selected) -->
                <div id="referenceNumberGroup" class="mb-3" style="display: none;">
                    <label for="modalReferenceNumber" class="form-label fw-semibold">
                        Reference Number <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="modalReferenceNumber" 
                           placeholder="Enter payment reference number"
                           maxlength="100"
                           autocomplete="off">
                    <small class="form-text text-muted">
                        📝 Enter transaction ID, cheque number, or any payment reference
                    </small>
                </div>

                <!-- Current Status Indicator (Hidden until loaded) -->
                <div id="currentStatusInfo" class="alert alert-secondary" style="display: none;">
                    <small>
                        <strong>Current Status:</strong> 
                        <span id="modalCurrentStatus" class="badge">-</span>
                    </small>
                </div>

                <!-- Error Alert (Hidden by default) -->
                <div id="paymentErrorAlert" class="alert alert-danger" role="alert" style="display: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="paymentErrorMessage"></span>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn-sample-records btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn-sample-records btn-success" id="btnSavePayment">
                    <i class="fas fa-save me-1"></i>Save Payment
                </button>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" id="modalSampleId" value="">

        </div>
    </div>
</div>

<!-- ============================================================
   TOAST NOTIFICATIONS CONTAINER
   For success/error messages (bottom-right position)
   ============================================================ -->

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080" id="toastContainer">
    <!-- Toasts will be dynamically inserted here -->
</div>

<!-- ============================================================
   EXTERNAL JAVASCRIPT
   ============================================================ -->

<script src="../../public/assets/js/sample-records.js"></script>

<!-- ============================================================
   INLINE JAVASCRIPT FOR INITIALIZATION
   Critical: Must run after page load
   ============================================================ -->

<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sample Records View: DOM Loaded');
    
    // Check if SampleRecords module is available
    if (typeof SampleRecords !== 'undefined') {
        console.log('✅ SampleRecords Module: Loaded');
        
        // Initialize the module
        try {
            SampleRecords.init();
            console.log('✅ SampleRecords Module: Initialized Successfully');
        } catch (error) {
            console.error('❌ SampleRecords Module: Initialization Failed', error);
            
            // Show error to user
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger m-3';
            errorDiv.innerHTML = `
                <h5><i class="fas fa-exclamation-triangle"></i> Initialization Error</h5>
                <p>Failed to initialize Sample Records module. Please refresh the page.</p>
                <small>${error.message}</small>
            `;
            document.querySelector('.container-fluid').prepend(errorDiv);
        }
    } else {
        console.error('❌ SampleRecords Module: Not Found');
        console.error('❌ Check if sample-records.js is loaded correctly');
    }
    
    // Debug: Log all loaded scripts
    console.log('📜 Loaded Scripts:', 
        Array.from(document.scripts).map(s => s.src).filter(Boolean)
    );
});

// Global error handler for debugging
window.addEventListener('error', function(e) {
    console.error('💥 Global Error:', e.message, e.filename, e.lineno);
});

// Log when page is fully loaded (including all resources)
window.addEventListener('load', function() {
    console.log('✅ Page Fully Loaded (All Resources)');
});
</script>