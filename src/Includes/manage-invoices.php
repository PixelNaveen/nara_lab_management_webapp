<?php
/**
 * Manage Invoices Page
 * Laboratory Management System
 */

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<link rel="stylesheet" href="public/assets/css/manage-invoices.css?v=<?php echo time(); ?>">

<div class="container-fluid p-0">
    <!-- Filter Section -->
    <div class="invoice-filter-bar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="invoiceSearch" class="form-control" placeholder="Search by sample code or client name...">
        </div>
        <select id="paymentStatusFilter" class="form-select">
            <option value="all">All Payments</option>
            <option value="Paid">Paid</option>
            <option value="Not Paid">Not Paid</option>
            <option value="Pending">Pending</option>
        </select>
        <button class="btn btn-outline-secondary" id="btnResetFilters">
            <i class="fas fa-undo me-1"></i> Reset
        </button>
    </div>

    <!-- Invoices Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="table-container bg-white rounded shadow-sm overflow-hidden">
                <table class="table table-hover mb-0" id="invoicesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3">CLIENT</th>
                            <th class="px-3 py-3">PRICE (LKR)</th>
                            <th class="px-3 py-3 text-center">PAYMENT STATUS</th>
                            <th class="px-3 py-3 text-center">PAID DATE</th>
                            <th class="px-3 py-3 text-center">INVOICE</th>
                            <th class="px-3 py-3 text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading invoices...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state text-center p-5 bg-white rounded shadow-sm mt-3" style="display: none;">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No invoices found</h5>
                <p class="text-muted small">Try adjusting your search filters</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
   INVOICE GENERATION MODAL
   ============================================================ -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="invoiceModalLabel">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Generate Invoice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="invoiceSignatory" class="form-label fw-semibold">
                        Select Signatory <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="invoiceSignatory" required>
                        <option value="">Loading signatories...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="invoiceRequestDate" class="form-label fw-semibold">
                        Date of Request <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="invoiceRequestDate" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div id="invoiceErrorAlert" class="alert alert-danger" style="display: none;"></div>

                <div class="alert alert-info mt-3 mb-0 border-0 shadow-sm">
                    <i class="fas fa-info-circle me-1 text-primary"></i> Generating the invoice creates a permanent snapshot of current testing prices.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary px-4" id="btnPreviewInvoice">
                    <i class="fas fa-eye me-1"></i>Preview
                </button>
                <button type="button" class="btn btn-primary px-4" id="btnGenerateInvoice">
                    <i class="fas fa-print me-1"></i>Generate & Print
                </button>
            </div>
            <input type="hidden" id="invoiceSampleId" value="">
        </div>
    </div>
</div>

<!-- ============================================================
   PAYMENT STATUS MODAL (Optional if we want to update it here too)
   ============================================================ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-coins me-2"></i>Update Payment Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info mb-4 border-0 shadow-sm">
                    <div class="row small">
                        <div class="col-6 mb-1"><strong>Sample:</strong> <span id="modalSampleCode">-</span></div>
                        <div class="col-6 mb-1 text-end"><strong>Amount:</strong> <span id="modalAmount" class="text-success fw-bold">0.00</span></div>
                        <div class="col-12"><strong>Client:</strong> <span id="modalClientName">-</span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="modalPaymentStatus" class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="modalPaymentStatus" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Not Paid">Not Paid</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>

                <div id="referenceNumberGroup" class="mb-3" style="display: none;">
                    <label for="modalReferenceNumber" class="form-label fw-semibold">Reference Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modalReferenceNumber" placeholder="Receipt or Cheque No.">
                </div>

                <div id="paymentDateGroup" class="mb-3" style="display: none;">
                    <label for="modalPaymentDate" class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="modalPaymentDate" max="<?php echo date('Y-m-d'); ?>">
                </div>

                <div id="paymentErrorAlert" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="btnSavePayment">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
            <input type="hidden" id="modalSampleId" value="">
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATIONS CONTAINER -->
<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>

<script src="public/assets/js/manage-invoices.js?v=<?php echo time(); ?>"></script>
