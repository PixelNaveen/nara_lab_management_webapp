<?php

/**
 * Sample Submission Form - COMPLETE VERSION WITH VALIDATION
 * Version: 2.0 - Production Ready
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
                            <label class="form-label">Search Client</label>
                            <input type="text" class="form-control" id="clientSearch"
                                placeholder="Search by name, phone, or contact person..." autocomplete="off">
                            <div class="client-results" id="clientResults"></div>
                            <span class="error-label" id="clientSearchError"></span>
                        </div>

                        <!-- Client Details -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Client Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="clientName" required>
                                <span class="error-label" id="clientNameError"></span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Primary Phone <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="phonePrimary"
                                    required placeholder="0XXXXXXXXX" maxlength="10">
                                <small class="text-muted">Format: 10 digits starting with 0</small>
                                <span class="error-label" id="phonePrimaryError"></span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" id="addressLine1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <div class="position-relative">
                                    <input type="text"
                                        class="form-control"
                                        id="city"
                                        placeholder="Type to search cities..."
                                        autocomplete="off">
                                    <input type="hidden" id="selectedCityId" value="">
                                    <div class="city-autocomplete" id="cityAutocomplete"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contactPerson">
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
                        <span class="error-label text-center d-block" id="submissionTypeError"></span>
                    </div>

                    <!-- STEP 3: SUBMISSION DETAILS -->
                    <div class="step" data-step="3">
                        <h2 class="step-title">
                            <i class="fas fa-calendar-alt"></i> Step 3: Submission Details
                        </h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Received Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="receivedDate" required>
                                <small class="text-muted">Today or up to 5 days in the past</small>
                                <span class="error-label" id="receivedDateError"></span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Tentative Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="tentativeDate" required>
                                <small class="text-muted">Today or future date</small>
                                <span class="error-label" id="tentativeDateError"></span>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="additionalNotes" rows="3"
                                    placeholder="Enter any additional notes or special instructions..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Additional Charges (Rs.)</label>
                                <input type="number" class="form-control" id="additionalCharges"
                                    min="0" step="0.01" value="0.00">
                                <small class="text-muted">Any extra charges beyond test fees</small>
                                <span class="error-label" id="additionalChargesError"></span>
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
                    </div>

                    <!-- STEP 6: REVIEW & SUBMIT -->
                    <div class="step" data-step="6">
                        <h2 class="step-title">
                            <i class="fas fa-check-circle"></i> Step 6: Review & Submit
                        </h2>

                        <!-- Summary -->
                        <div id="reviewSummary"></div>

                        <!-- Payment Status -->
                        <!-- <div class="payment-section">
                            <h5 class="mb-3">
                                <i class="fas fa-credit-card"></i> Payment Status <span class="text-danger">*</span>
                            </h5>

                            <div class="payment-options">
                                <div class="payment-option" data-status="paid">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                    <h5>Paid</h5>
                                </div>
                                <div class="payment-option" data-status="not_paid">
                                    <i class="fas fa-times-circle fa-3x text-danger"></i>
                                    <h5>Not Paid</h5>
                                </div>
                            </div>

                            <div id="paymentReferenceSection" class="mt-3 d-none">
                                <label class="form-label">
                                    Payment Reference <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="paymentReference"
                                    placeholder="Enter payment reference number">
                                <span class="error-label" id="paymentReferenceError"></span>
                            </div>
                        </div> -->

                        <!-- Receipt Delivery -->
                        <div class="receipt-delivery-section">
                            <h5 class="mb-3">
                                <i class="fas fa-envelope"></i> Receipt Delivery
                            </h5>

                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                Optional: Provide an email address to receive the receipt electronically.
                            </div>

                            <div class="row g-3">
                                <!-- <div class="col-md-6">
                                    <label class="form-label">Mobile Number (for SMS receipt)</label>
                                    <input type="text" class="form-control" id="receiptMobile"
                                        placeholder="0XXXXXXXXX" disabled>
                                    <small class="text-muted">Feature coming soon</small>
                                </div> -->
                                <div class="col-md-6">
                                    <label class="form-label">Email Address (for email receipt)</label>
                                    <input type="email" class="form-control" id="receiptEmail"
                                        placeholder="client@example.com">
                                    <small class="text-muted">Optional - for sending receipt via email</small>
                                    <span class="error-label" id="receiptEmailError"></span>
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

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
        <div id="submissionToastContainer"></div>
    </div>

</div>
<!-- End scoped div -->

<!-- Load Scripts -->
<link rel="stylesheet" href="public/assets/css/sample-submission.css">
<script src="public/assets/js/sample-submission.js"></script>