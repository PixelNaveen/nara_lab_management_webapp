<!-- ============================================================
   TEST REPORTS PAGE
   Laboratory Management System
   Version 1.0 - Final Test Report Generation with 3-Step Wizard
   ============================================================ -->

<!-- Link to External CSS -->
<link rel="stylesheet" href="public/assets/css/test-report.css">

<div class="test-report-container">
    <!-- Header/Title could be here if needed, but keeping it lean per user screenshots -->
    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="row g-3 align-items-center mb-4 mx-0">
        <div class="col-12">
            <div class="row g-2">
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" id="trSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search by sample code or client name..." autocomplete="off">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select shadow-sm rounded-3 cursor-pointer" id="trDatePreset">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="last7">Last 7 Days</option>
                        <option value="last30">Last 30 Days</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <button class="btn btn-outline-secondary btn-tr-reset shadow-sm rounded-3 w-100 bg-white" onclick="location.reload()">
                        <i class="fas fa-undo-alt me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SAMPLES TABLE ==================== -->
    <div class="tr-table-container">
        <table class="tr-table" id="trSamplesTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3">CLIENT</th>
                            <th class="px-3 py-3 text-center">ITEMS</th>
                            <th class="px-3 py-3 text-center">TESTS</th>
                            <th class="px-3 py-3">TEST ENDED</th>
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
                <i class="fa-solid fa-folder-open mb-4"></i>
                <h5 class="fw-bold text-dark">No completed samples found</h5>
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
            <div class="modal-header">
                <h5 class="modal-title" id="reportWizardLabel">
                    <i class="fa-solid fa-file-invoice me-2 text-emerald-600"></i>Generate Report
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-100" id="wizardSampleCode">-</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <h6 class="text-dark"><i class="fa-solid fa-layer-group me-2 text-secondary opacity-50"></i>Report Layout</h6>

                    <div class="mt-1 p-3 bg-light rounded-3 border" id="layoutOptions" style="display: none;">
                        <label class="form-label fw-bold text-slate-700 small">
                            <i class="fa-solid fa-table-list me-1"></i>LAYOOUT OPTIONS (FOR ACCREDITED ITEMS)
                        </label>
                        <div class="form-check custom-radio mb-2">
                            <input class="form-check-input" type="radio" name="layoutType" id="layoutSingle" value="single" checked>
                            <label class="form-check-label fw-medium" for="layoutSingle">
                                Separate report for each item
                            </label>
                        </div>
                        <div class="form-check custom-radio">
                            <input class="form-check-input" type="radio" name="layoutType" id="layoutCombined" value="combined">
                            <label class="form-check-label fw-medium" for="layoutCombined">
                                Combined report (up to 5 items per page)
                            </label>
                        </div>
                        <div class="form-text mt-2 small text-muted" id="layoutNote"></div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm mt-3 mb-0 d-flex align-items-center gap-3" id="reportTypeInfo" style="border-radius: 12px; background: #f0f9ff; color: #0369a1;">
                        <i class="fa-solid fa-circle-info fa-lg"></i>
                        <span id="reportTypeText" class="small fw-medium">Analyzing report type...</span>
                    </div>
                </div>
                <!-- ========== STEP 2: Signatories ========== -->
                <div class="wizard-panel" id="wizardStep2" style="display: none;">
                    <h6 class="text-dark"><i class="fa-solid fa-signature me-2 text-secondary opacity-50"></i>Select Report Signatories</h6>

                    <div class="row g-4">
                        <!-- Left Signatory (Scientist) -->
                        <div class="col-md-6">
                            <div class="signatory-card p-4 border rounded-3 h-100">
                                <label class="form-label fw-bold text-emerald-700 small mb-3">
                                    <i class="fa-solid fa-user-tie me-1"></i>SCIENTIST (LEFT BLOCK)
                                </label>
                                <select class="form-select border-0 shadow-sm" id="signatoryLeft" style="background-color: #f1f5f9;">
                                    <option value="">Select scientist...</option>
                                </select>
                                <div class="signatory-preview mt-3 pt-3 border-top border-dashed" id="sigLeftPreview" style="display: none;">
                                    <small class="fw-bold d-block text-dark" id="sigLeftTitle"></small>
                                    <small class="text-muted d-block small" id="sigLeftDivision"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Right Signatory (Head) -->
                        <div class="col-md-6">
                            <div class="signatory-card p-4 border rounded-3 h-100">
                                <label class="form-label fw-bold text-slate-700 small mb-3">
                                    <i class="fa-solid fa-user-check me-1"></i>HEAD (RIGHT BLOCK)
                                </label>
                                <select class="form-select border-0 shadow-sm" id="signatoryRight" style="background-color: #f1f5f9;">
                                    <option value="">Select head...</option>
                                </select>
                                <div class="signatory-preview mt-3 pt-3 border-top border-dashed" id="sigRightPreview" style="display: none;">
                                    <small class="fw-bold d-block text-dark" id="sigRightTitle"></small>
                                    <small class="text-muted d-block small" id="sigRightDivision"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 shadow-sm mt-4 mb-0 d-flex align-items-center gap-3" id="signatoryWarning" style="display: none; border-radius: 12px; background: #fffbeb; color: #92400e;">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                        <span class="small fw-medium">Both signatories must be selected before generating the report.</span>
                    </div>
                </div>


            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-link text-slate-500 text-decoration-none fw-bold" data-bs-dismiss="modal">
                    CANCEL
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-slate shadow-sm fw-bold px-4" id="btnWizardBack" style="display: none; border-radius: 8px;">
                        <i class="fa-solid fa-arrow-left me-2"></i>BACK
                    </button>
                    <button type="button" class="btn btn-emerald-600 shadow-sm fw-bold px-4 text-white" id="btnWizardNext" style="border-radius: 8px;">
                        NEXT <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-emerald-600 shadow-sm fw-bold px-4 text-white" id="btnGenerateReport" style="display: none; border-radius: 8px;">
                        <i class="fa-solid fa-file-export me-2"></i>GENERATE & PRINT
                    </button>
                </div>
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

                // Auto-open wizard if sample_id was passed from Result Entry page
                <?php
                $autoSampleId = intval($_GET['sample_id'] ?? 0);
                if ($autoSampleId > 0) {
                    echo "setTimeout(function() {";
                    echo "  TestReport.openWizard(" . $autoSampleId . ");";
                    echo "  history.replaceState(null, '', 'index.php?page=test-reports');";
                    echo "}, 800);";
                }
                ?>
            } catch (error) {
                console.error('❌ TestReport Module: Init Failed', error);
            }
        }
    });
</script>