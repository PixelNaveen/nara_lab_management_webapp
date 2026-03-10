<!-- ============================================================
   TEST REPORTS PAGE
   Laboratory Management System
   Version 1.0 - Final Test Report Generation with 3-Step Wizard
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/test-report.css">

<div class="container-fluid px-4 py-4">



    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Search Input -->
                <input type="text"
                    class="form-control"
                    id="trSearchInput"
                    placeholder="Search by sample code or client name..."
                    style="max-width: 320px;"
                    autocomplete="off">

                <!-- Date Presets -->
                <select class="form-select" id="trDatePreset" style="max-width: 180px;">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="last7">Last 7 Days</option>
                    <option value="last30">Last 30 Days</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ==================== SAMPLES TABLE ==================== -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="trSamplesTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3">CLIENT</th>
                            <th class="px-3 py-3 text-center">ITEMS</th>
                            <th class="px-3 py-3 text-center">TESTS</th>
                            <th class="px-3 py-3">RECEIVED</th>
                            <th class="px-3 py-3 text-center">REPORT</th>
                            <th class="px-3 py-3 text-center" style="width: 180px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading completed samples...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="trEmptyState" class="empty-state" style="display: none;">
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; opacity: 0.5;"></i>
                <h5 class="text-muted">No completed samples found</h5>
                <p class="text-muted small">Reports can only be generated for completed samples with all results entered.</p>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
   REPORT GENERATION MODAL - 2-STEP WIZARD
   Step 1: Layout Choice
   Step 2: Select Signatories → Generate
   ============================================================ -->

<div class="modal fade" id="reportWizardModal" tabindex="-1" aria-labelledby="reportWizardLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="reportWizardLabel">
                    <i class="bi bi-file-earmark-medical me-2"></i>Generate Report
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-success" id="wizardSampleCode">-</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">

                <!-- Step Indicator -->
                <div class="wizard-steps mb-4">
                    <div class="wizard-step active" data-step="1">
                        <span class="step-number">1</span>
                        <span class="step-label">Report Layout</span>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <span class="step-number">2</span>
                        <span class="step-label">Signatories</span>
                    </div>
                </div>

                <!-- ========== STEP 1: Layout Options ========== -->
                <div class="wizard-panel" id="wizardStep1">
                    <h6 class="mb-3"><i class="bi bi-layout-three-columns me-2"></i>Report Layout</h6>

                    <div class="mt-1 p-3 bg-light rounded" id="layoutOptions" style="display: none;">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-layout-three-columns me-1"></i>Layout options (for accredited items)
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="layoutType" id="layoutSingle" value="single" checked>
                            <label class="form-check-label" for="layoutSingle">
                                Separate report for each item
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="layoutType" id="layoutCombined" value="combined">
                            <label class="form-check-label" for="layoutCombined">
                                Combined report (up to 5 items as columns per page)
                            </label>
                        </div>
                        <div class="form-text" id="layoutNote"></div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" id="reportTypeInfo">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="reportTypeText">Analyzing report type...</span>
                    </div>
                </div>

                <!-- ========== STEP 2: Signatories ========== -->
                <div class="wizard-panel" id="wizardStep2" style="display: none;">
                    <h6 class="mb-3"><i class="bi bi-pen me-2"></i>Select Report Signatories</h6>

                    <div class="row g-4">
                        <!-- Left Signatory (Scientist) -->
                        <div class="col-md-6">
                            <div class="signatory-card p-3 border rounded">
                                <label class="form-label fw-semibold text-primary">
                                    <i class="bi bi-person-badge me-1"></i>Scientist (Left Block)
                                </label>
                                <select class="form-select" id="signatoryLeft">
                                    <option value="">Select scientist...</option>
                                </select>
                                <div class="signatory-preview mt-2" id="sigLeftPreview" style="display: none;">
                                    <small class="text-muted d-block" id="sigLeftTitle"></small>
                                    <small class="text-muted d-block" id="sigLeftDivision"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Right Signatory (Head) -->
                        <div class="col-md-6">
                            <div class="signatory-card p-3 border rounded">
                                <label class="form-label fw-semibold text-danger">
                                    <i class="bi bi-person-check me-1"></i>Head (Right Block)
                                </label>
                                <select class="form-select" id="signatoryRight">
                                    <option value="">Select head...</option>
                                </select>
                                <div class="signatory-preview mt-2" id="sigRightPreview" style="display: none;">
                                    <small class="text-muted d-block" id="sigRightTitle"></small>
                                    <small class="text-muted d-block" id="sigRightDivision"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0" id="signatoryWarning" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Both signatories must be selected before generating the report.
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnWizardBack" style="display: none;">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </button>
                <button type="button" class="btn btn-primary" id="btnWizardNext">
                    Next <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success" id="btnGenerateReport" style="display: none;">
                    <i class="bi bi-file-earmark-check me-1"></i>Generate &amp; Print Report
                </button>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" id="wizardSampleId" value="">

        </div>
    </div>
</div>

<!-- ============================================================
   TOAST NOTIFICATIONS
   ============================================================ -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080" id="trToastContainer">
</div>

<!-- ============================================================
   EXTERNAL JAVASCRIPT
   ============================================================ -->
<script src="../../public/assets/js/test-report.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TestReport !== 'undefined') {
            try {
                TestReport.init();
                console.log('✅ TestReport Module: Initialized');
            } catch (error) {
                console.error('❌ TestReport Module: Init Failed', error);
            }
        }
    });
</script>