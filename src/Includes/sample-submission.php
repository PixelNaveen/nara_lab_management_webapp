<?php
/**
 * Sample Submission Form
 * 6-Step Wizard for Laboratory Sample Submission
 * 
 * @package LabManagementSystem
 * @version 1.0
 */

// Session should be started by auth.php in index.php
// Access current user data
$currentUser = $_SESSION['fullname'] ?? 'Unknown User';
$userId = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Submission - Laboratory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="public/assets/css/sample-submission.css" rel="stylesheet">
</head>
<body>
    <!-- Toast Notification Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle me-2 toast-icon"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <div class="main-container">
        <!-- Form Header -->
        <div class="form-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1><i class="fas fa-flask"></i> Sample Information Form</h1>
                    <!-- Form number will be shown after successful submission -->
                </div>
                <div class="text-end">
                    <small class="d-block opacity-75">Submitted by:</small>
                    <strong id="currentUser"><?php echo htmlspecialchars($currentUser); ?></strong>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar" id="progressBar" style="width: 16.67%;"></div>
            </div>
            <ul class="progress-steps">
                <li class="progress-step active" data-step="1">Client</li>
                <li class="progress-step" data-step="2">Type</li>
                <li class="progress-step" data-step="3">Details</li>
                <li class="progress-step" data-step="4">Samples</li>
                <li class="progress-step" data-step="5">Tests</li>
                <li class="progress-step" data-step="6">Review</li>
            </ul>
        </div>

        <!-- Main Form -->
        <form id="siForm">
            <!-- Hidden fields -->
            <input type="hidden" name="submitted_by" value="<?php echo htmlspecialchars($currentUser); ?>">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <input type="hidden" id="selectedClientId" name="client_id" value="">
            
            <!-- Store original client data for comparison -->
            <input type="hidden" id="originalClientName" value="">
            <input type="hidden" id="originalPhone" value="">
            <input type="hidden" id="originalEmail" value="">
            <input type="hidden" id="originalMobile" value="">
            <input type="hidden" id="originalContactPerson" value="">

            <!-- ========================================
                 STEP 1: CLIENT SELECTION
                 ======================================== -->
            <div class="step active" data-step="1">
                <h2 class="step-title"><i class="fas fa-users"></i> Step 1: Select or Create Client</h2>
                
                <!-- Client Search Box -->
                <div class="client-search-wrapper mb-4">
                    <label class="form-label fw-bold">Search Existing Client</label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-lg" id="clientSearch" 
                               placeholder="Search by name, phone, or email..." autocomplete="off">
                        <div class="client-results" id="clientResults"></div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Select an existing client or create a new one below
                </div>

                <!-- Client Form Fields -->
                <h5 class="mb-3"><i class="fas fa-user-plus"></i> Client Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client Name *</label>
                        <input type="text" class="form-control" name="client_name" id="clientName" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="phone_primary" id="phoneInput" 
                               placeholder="e.g., 0111234567" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address_line1" id="addressInput">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" id="cityInput">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control" name="contact_person" id="contactPersonInput">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email (Optional)</label>
                        <input type="email" class="form-control" name="email" id="emailInput" 
                               placeholder="client@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile (Optional)</label>
                        <input type="text" class="form-control" name="mobile" id="mobileInput" 
                               placeholder="07XXXXXXXX">
                    </div>
                </div>
            </div>

            <!-- ========================================
                 STEP 2: SUBMISSION TYPE
                 ======================================== -->
            <div class="step" data-step="2">
                <h2 class="step-title"><i class="fas fa-clipboard-check"></i> Step 2: Select Submission Type</h2>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="type-card" data-type="regular">
                            <input type="radio" name="submission_type" value="regular" id="typeRegular" 
                                   style="display: none;" required>
                            <label for="typeRegular" style="cursor: pointer; width: 100%;">
                                <i class="fas fa-vial"></i>
                                <h4>Regular Testing</h4>
                                <p class="text-muted mb-0">Water, Liquid, Food Samples</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="type-card" data-type="swab">
                            <input type="radio" name="submission_type" value="swab" id="typeSwab" 
                                   style="display: none;" required>
                            <label for="typeSwab" style="cursor: pointer; width: 100%;">
                                <i class="fas fa-hand-sparkles"></i>
                                <h4>SWAB Testing</h4>
                                <p class="text-muted mb-0">Surface Swabs, Equipment Testing</p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 STEP 3: SUBMISSION DETAILS
                 ======================================== -->
            <div class="step" data-step="3">
                <h2 class="step-title"><i class="fas fa-info-circle"></i> Step 3: Submission Details</h2>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Date Received *</label>
                        <input type="date" class="form-control" name="received_date" id="receivedDate" required>
                        <small class="text-muted">Today or past 5 days only</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tentative Report Date *</label>
                        <input type="date" class="form-control" name="tentative_date" id="tentativeDate" required>
                        <small class="text-muted">Today or future date</small>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" name="additional_notes" rows="3" 
                                  placeholder="Any special instructions or observations..."></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Additional Charges (Rs.)</label>
                        <input type="number" class="form-control" name="additional_charges" 
                               step="0.01" min="0" value="0" placeholder="0.00">
                    </div>

                    <!-- Payment Section -->
                    <div class="col-12 mt-4">
                        <hr>
                        <h5 class="mb-3"><i class="fas fa-credit-card"></i> Payment Information</h5>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Status *</label>
                        <div class="payment-options">
                            <div class="payment-option" data-status="paid">
                                <input type="radio" name="payment_status" value="Paid" id="paymentPaid" required>
                                <label for="paymentPaid" class="w-100 text-center">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="d-block mt-2">Paid</span>
                                </label>
                            </div>
                            <div class="payment-option" data-status="not-paid">
                                <input type="radio" name="payment_status" value="Not Paid" id="paymentNotPaid" required>
                                <label for="paymentNotPaid" class="w-100 text-center">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <span class="d-block mt-2">Not Paid</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" id="paymentReferenceField" style="display: none;">
                        <label class="form-label fw-bold">Payment Reference Number *</label>
                        <input type="text" class="form-control" name="payment_reference" id="paymentReference" 
                               placeholder="e.g., CHQ-123456, TR-789012">
                        <small class="text-muted">Enter cheque number, transaction ID, or receipt number</small>
                        <div id="paymentRefValidation" class="mt-1"></div>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 STEP 4: SAMPLE ITEMS
                 ======================================== -->
            <div class="step" data-step="4">
                <h2 class="step-title"><i class="fas fa-flask"></i> Step 4: Add Sample Items</h2>
                
                <div id="samplesContainer"></div>
                
                <button type="button" class="add-sample-btn" id="addSampleBtn">
                    <i class="fas fa-plus-circle"></i> Add Another Sample
                </button>
            </div>

            <!-- ========================================
                 STEP 5: SELECT TESTS
                 ======================================== -->
            <div class="step" data-step="5">
                <h2 class="step-title"><i class="fas fa-tasks"></i> Step 5: Select Tests for Each Sample</h2>
                
                <div class="alert alert-info">
                    <i class="fas fa-exclamation-circle"></i> Each sample must have at least one test selected
                </div>
                
                <div id="testsContainer"></div>
            </div>

            <!-- ========================================
                 STEP 6: REVIEW & SUBMIT
                 ======================================== -->
            <div class="step" data-step="6">
                <h2 class="step-title"><i class="fas fa-check-circle"></i> Step 6: Review & Submit</h2>
                
                <div id="reviewSummary"></div>

                <!-- Receipt Delivery (Optional - One-time use) -->
                <div class="receipt-delivery-section mt-4">
                    <hr>
                    <h5 class="mb-3"><i class="fas fa-envelope"></i> Receipt Delivery (Optional)</h5>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> These are for receipt delivery only and will not be saved to client record.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number (for SMS receipt)</label>
                            <input type="text" class="form-control" id="receiptMobile" 
                                   placeholder="07XXXXXXXX">
                            <small class="text-muted">Leave blank to use client's saved number</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address (for email receipt)</label>
                            <input type="email" class="form-control" id="receiptEmail" 
                                   placeholder="client@example.com">
                            <small class="text-muted">Optional</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <button type="button" class="btn-nav btn-prev" id="prevBtn" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="btn-nav btn-next" id="nextBtn">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn-nav btn-submit" id="submitBtn" style="display: none;">
                    <i class="fas fa-paper-plane"></i> Submit Form
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/assets/js/sample-submission.js"></script>
</body>
</html>