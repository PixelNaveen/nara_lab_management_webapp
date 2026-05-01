<?php

/**
 * Sample Submission Form - COMPLETE VERSION 3.0
 * Version: 3.0 - 100% Production Ready with Server Time
 * Date: February 5, 2026
 * Features: Server time, 30-day backdate, auto tentative, time field
 */

$currentUser = $_SESSION['fullname'] ?? 'Unknown User';
$userId = $_SESSION['user_id'] ?? 0;
?>

<!-- Wrap everything in scoped div to prevent CSS conflicts -->
<div class="sample-submission-page">

    <!-- Main Container -->
    <div class="container-fluid py-4">

        <div class="card shadow">
            <!-- Card Header -->
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-file-alt"></i> Sample Information Form
                </h4>
                <small>Submitted by: <?php echo htmlspecialchars($currentUser); ?></small>
            </div>

            <div class="card-body">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-step="1">
                            <i class="fas fa-user"></i> Client
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-step="2">
                            <i class="fas fa-tags"></i> Type
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-step="3">
                            <i class="fas fa-calendar-alt"></i> Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-step="4">
                            <i class="fas fa-flask"></i> Samples
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-step="5">
                            <i class="fas fa-tasks"></i> Tests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-step="6">
                            <i class="fas fa-check-circle"></i> Review
                        </a>
                    </li>
                </ul>

                <!-- Form -->
                <form id="siForm" novalidate>
                    <!-- Hidden Fields -->
                    <input type="hidden" name="submitted_by" value="<?php echo htmlspecialchars($currentUser); ?>">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <input type="hidden" id="selectedClientId" name="client_id" value="">
                    <input type="hidden" id="originalClientName" value="">
                    <input type="hidden" id="originalPhone" value="">
                    <input type="hidden" id="originalContactPerson" value="">
                    <input type="hidden" id="originalCity" value="">

                    <!-- STEP 1: CLIENT INFORMATION -->
                    <div class="step active" data-step="1">
                        <h2 class="step-title">
                            <i class="fas fa-user"></i> Step 1: Client Information
                        </h2>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Search for existing clients or enter new client details
                        </div>

                        <!-- Client Search -->
                        <div class="mb-4 position-relative">
                            <label class="form-label fw-bold">Search Client</label>
                            <input type="text" class="form-control" id="clientSearch"
                                placeholder="Search by name, phone, or contact person..." autocomplete="off">
                            <div class="client-results" id="clientResults"></div>
                            <div class="invalid-feedback" id="clientSearchError"></div>
                        </div>

                        <!-- Client Details -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Client Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="clientName" required>
                                <div class="invalid-feedback" id="clientNameError"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Primary Phone <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="phonePrimary"
                                    required placeholder="0XXXXXXXXX" maxlength="10">
                                <small class="text-muted">Format: 10 digits starting with 0</small>
                                <div class="invalid-feedback" id="phonePrimaryError"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Address <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addressLine1" required>
                                <div class="invalid-feedback" id="addressLine1Error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    City <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="text"
                                        class="form-control"
                                        id="city"
                                        placeholder="Type to search cities..."
                                        autocomplete="off">
                                    <input type="hidden" id="selectedCityId" value="">
                                    <div class="city-autocomplete" id="cityAutocomplete"></div>
                                    <div class="invalid-feedback" id="cityError"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Contact Person <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="contactPerson" required>
                                <div class="invalid-feedback" id="contactPersonError"></div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: SUBMISSION TYPE -->
                    <div class="step" data-step="2">
                        <h2 class="step-title">
                            <i class="fas fa-tags"></i> Step 2: Submission Type
                        </h2>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Select the type of sample submission
                        </div>

                        <div class="type-selection">
                            <div class="type-card" data-type="regular">
                                <i class="fas fa-vial fa-3x"></i>
                                <h5>Regular</h5>
                                <p class="text-muted">Standard sample submission</p>
                            </div>
                            <div class="type-card" data-type="swab">
                                <i class="fas fa-microscope fa-3x"></i>
                                <h5>Swab</h5>
                                <p class="text-muted">Swab sample submission</p>
                            </div>
                        </div>
                        <div class="invalid-feedback text-center d-block" id="submissionTypeError"></div>
                    </div>

                    <!-- STEP 3: SUBMISSION DETAILS -->
                    <div class="step" data-step="3">
                        <h2 class="step-title">
                            <i class="fas fa-calendar-alt"></i> Step 3: Submission Details
                        </h2>

                        <!-- ✅ SERVER TIME DISPLAY -->
                        <div class="alert alert-info mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <i class="fas fa-server"></i> <strong>Server Time:</strong>
                                    <span id="currentServerTime" class="fw-bold text-primary ms-2">Loading...</span>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> Asia/Colombo (UTC+5:30)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row gx-lg-5 gy-4 mb-4">
                            <!-- RECEIVED DATE -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Received Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="receivedDate" required>
                                <small class="text-muted d-block mt-1">Today or up to 30 days in the past</small>
                                <div class="invalid-feedback" id="receivedDateError"></div>
                            </div>

                            <!-- RECEIVED TIME -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Received Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" id="receivedTime" required>
                                <small class="text-muted d-block mt-1">Exact time sample arrived (24-hour format)</small>
                                <div class="invalid-feedback" id="receivedTimeError"></div>
                            </div>
                        </div>

                        <div class="row gx-lg-5 gy-4 mb-4">
                            <!-- COLLECTED DATE -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sample Collected Date</label>
                                <input type="date" class="form-control" id="collectedDate">
                                <small class="text-muted d-block mt-1">Date originally collected by client</small>
                            </div>

                            <!-- COLLECTED TIME -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sample Collected Time</label>
                                <input type="time" class="form-control" id="collectedTime">
                                <small class="text-muted d-block mt-1">Time of original sample collection</small>
                            </div>
                        </div>

                        <div class="row gx-lg-5 gy-4 mb-4">
                            <!-- TENTATIVE DATE -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tentative Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="tentativeDate" required>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Auto-calculated (Received Date + 10 days)
                                </small>
                                <div class="invalid-feedback" id="tentativeDateError"></div>
                            </div>

                            <!-- SUBMISSION ORIGIN -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Sample Drawing Origin <span class="text-danger">*</span>
                                </label>
                                <div class="segmented-control">
                                    <div class="form-check">
                                        <input type="radio" name="is_drawn_by_nara" id="originClient" value="0" checked>
                                        <label for="originClient">
                                            <i class="fas fa-user-tie"></i> Client Submitted
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" name="is_drawn_by_nara" id="originNara" value="1">
                                        <label for="originNara">
                                            <i class="fas fa-flask"></i> Drawn by NARA
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Specify if NARA staff collected the sample</small>
                            </div>
                        </div>

                            <!-- EXTRA ITEMS SECTION -->
                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-box-open"></i> Additional Items / Charges
                                </label>
                                <div id="extraItemsContainer" class="extra-items-section">
                                    <div class="text-center text-muted p-3">
                                        <div class="spinner-border spinner-border-sm"></div> Loading extra items...
                                    </div>
                                </div>
                                <input type="hidden" id="additionalCharges" value="0">
                                <div class="extra-items-total mt-2 d-flex justify-content-end">
                                    <strong>Additional Charges Total: <span id="extraItemsTotalDisplay">Rs. 0.00</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: ADD SAMPLES -->
                    <div class="step" data-step="4">
                        <h2 class="step-title">
                            <i class="fas fa-flask"></i> Step 4: Add Sample Items
                        </h2>

                        <div class="alert alert-info">
                            <i class="fas fa-plus-circle"></i> Add at least one sample. Click "Add Sample" to add more.
                        </div>

                        <div id="samplesContainer"></div>

                        <button type="button" class="btn btn-outline-primary mt-3" id="addSampleBtn">
                            <i class="fas fa-plus"></i> Add Sample
                        </button>
                    </div>

                    <!-- STEP 5: SELECT TESTS -->
                    <div class="step" data-step="5">
                        <h2 class="step-title">
                            <i class="fas fa-tasks"></i> Step 5: Select Tests for Each Sample
                        </h2>

                        <div class="alert alert-info">
                            <i class="fas fa-exclamation-circle"></i> Each sample must have at least one test selected (maximum 10 tests per sample)
                        </div>

                        <div id="testsContainer"></div>

                        <!-- Live Price Summary Bar (Step 5) -->
                        <div id="step5PriceSummary" class="mt-4 p-3 border rounded bg-light" style="display:none;">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <span class="text-muted small"><i class="fas fa-flask"></i> Test Charges:</span>
                                    <strong id="testChargesTotal" class="ms-1 text-primary">Rs. 0.00</strong>
                                </div>
                                <div class="col-md-4" id="swabChargeSummary" style="display:none;">
                                    <span class="text-muted small"><i class="fas fa-tint"></i> Swab Surcharges:</span>
                                    <strong id="swabChargesTotal" class="ms-1" style="color:#6f42c1;">Rs. 0.00</strong>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="text-muted small">Grand Total:</span>
                                    <strong id="grandTotalDisplay" class="ms-1 text-success fs-5">Rs. 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 6: REVIEW & SUBMIT -->
                    <div class="step" data-step="6">
                        <h2 class="step-title">
                            <i class="fas fa-check-circle"></i> Step 6: Review & Submit
                        </h2>

                        <!-- Summary -->
                        <div id="reviewSummary"></div>

                        <!-- Receipt Delivery -->
                        <div class="receipt-delivery-section">
                            <h5 class="mb-3">
                                <i class="fas fa-envelope"></i> Receipt Delivery (Optional)
                            </h5>

                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                Optional: Provide an email address to receive the receipt electronically.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address (for email receipt)</label>
                                    <input type="email" class="form-control" id="receiptEmail"
                                        placeholder="client@example.com">
                                    <small class="text-muted">Optional - for sending receipt via email</small>
                                    <div class="invalid-feedback" id="receiptEmailError"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="nav-buttons">
                        <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <div>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Submit Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- NEW SAMPLE NAME CATEGORY INTERCEPTOR MODAL -->
    <div class="modal fade" id="newSampleNamesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> New Sample Names Detected
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        The following sample names are new to the system. Please assign a category and SLAB accreditation to each.
                    </p>
                    <div class="row fw-bold mb-2">
                        <div class="col-5">Sample Name</div>
                        <div class="col-4">Category</div>
                        <div class="col-3 text-center">SLAB Accredited</div>
                    </div>
                    <div id="newNamesListContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="saveNewNamesBtn">
                        <i class="fas fa-check"></i> Save & Continue
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
        <div id="submissionToastContainer"></div>
    </div>

</div>
<!-- End scoped div -->

<!-- Load Scripts -->
<link rel="stylesheet" href="public/assets/css/sample-submission.css">
<script src="public/assets/js/sample-submission.js"></script>