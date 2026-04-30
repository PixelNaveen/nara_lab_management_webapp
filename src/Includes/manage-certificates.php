<?php
// src/Includes/manage-certificates.php - FINAL COMPLETE VERSION
?>

<link rel="stylesheet" href="public/assets/css/manage-certificates.css">

<div class="certificates-container">

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Certificates</p>
                    <h3 class="stat-value" id="totalCertificates">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Active</p>
                    <h3 class="stat-value" id="activeCertificates">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Expiring Soon</p>
                    <h3 class="stat-value" id="expiringSoon">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Expired</p>
                    <h3 class="stat-value" id="expiredCertificates">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-filter cert-filters d-flex gap-2 align-items-center flex-wrap">
        <input type="text" class="form-control" id="searchInput"
            placeholder="Search certificates..." style="max-width: 250px;">

        <select class="form-select" id="filterStatus" style="max-width: 180px;">
            <option value="all">All Status</option>
            <option value="active">Active Only</option>
            <option value="expired">Expired Only</option>
            <option value="pending">Pending Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 160px;">
            <option value="expiry">Sort by Expiry</option>
            <option value="code">Sort by Code</option>
            <option value="name">Sort by Name</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-outline-primary">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-primary" id="btnNewCertificate">
                <i class="fas fa-plus"></i> New Certificate
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table certificatesTable">
            <thead>
                <tr>
                    <th style="width: 12%;">Code</th>
                    <th style="width: 22%;">Certificate Name</th>
                    <th style="width: 11%;">Valid From</th>
                    <th style="width: 11%;">Valid To</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Current</th>
                    <th style="width: 12%;">Expiry Status</th>
                    <th style="width: 12%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="certificatesTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-form">
        <div class="modal-header">
            <h5 id="formTitle">Add New Certificate</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="certificateForm">
            <input type="hidden" id="certificateId">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Certificate Code <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control"
                        id="certificateCode" name="certificateCode" required
                        placeholder="e.g., TL 010-01">
                    <small class="text-muted">Unique identifier</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select class="form-select"
                        id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Expired</option>
                        <option value="superseded">Superseded</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Certificate Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control"
                    id="certificateName" name="certificateName" required
                    placeholder="e.g., Microbiology Testing Accreditation 2024-2028">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Valid From <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control"
                        id="validFrom" name="validFrom" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Valid To <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control"
                        id="validTo" name="validTo" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Issued Date
                    </label>
                    <input type="date" class="form-control"
                        id="issuedDate" name="issuedDate">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Scope Description
                </label>
                <textarea class="form-control"
                    id="scopeDescription" name="scopeDescription" rows="3"
                    placeholder="What this certificate covers..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Notes
                </label>
                <textarea class="form-control"
                    id="notes" name="notes" rows="2"
                    placeholder="Additional notes..."></textarea>
            </div>

            <div class="mb-3" id="currentCheckboxContainer">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                        id="isCurrent" name="isCurrent">
                    <label class="form-check-label" for="isCurrent">
                        <strong>Set as Current Certificate</strong>
                        <small class="text-muted d-block">
                            (Will unset any other current certificate)
                        </small>
                    </label>
                </div>
            </div>

            <div class="modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save Certificate</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Certificate</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete certificate <strong><span id="deleteCertificateCode"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the certificate.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastContainer"></div>
</div>

<!-- CSRF Token for AJAX -->
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

<!-- Load External JS -->
<script src="public/assets/js/manage-certificates.js"></script>