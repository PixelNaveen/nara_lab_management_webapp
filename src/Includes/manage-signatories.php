<?php
/**
 * Manage Signatories Page
 * Interface for managing laboratory scientists and heads
 */

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="page-manage-signatories">
  <div class="signatories-container container-fluid">

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon bg-primary">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-details">
            <p class="stat-label">Total Signatories</p>
            <h3 class="stat-value" id="statTotal">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon bg-success">
            <i class="fas fa-microscope"></i>
          </div>
          <div class="stat-details">
            <p class="stat-label">Scientists</p>
            <h3 class="stat-value" id="statScientists">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon bg-danger">
            <i class="fas fa-user-tie"></i>
          </div>
          <div class="stat-details">
            <p class="stat-label">Heads</p>
            <h3 class="stat-value" id="statHeads">0</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter + New -->
    <div class="signatories-card-filter sig-filters">
      <input type="text" id="sigSearchInput" placeholder="Search by Name" class="form-control" style="max-width:250px;">
      <select class="form-select" id="sigRoleFilter" style="max-width:150px;">
        <option value="">All Roles</option>
        <option value="scientist">Scientists</option>
        <option value="head">Heads</option>
      </select>
      <button class="btn btn-signatories-filter" id="btnSigFilter">Filter</button>
      <button class="btn btn-outline-secondary" id="btnSigReset">Reset</button>

      <div class="ms-auto">
        <button class="btn-signatories-new" id="btnNewSignatory">+ New Signatory</button>
      </div>
    </div>

    <!-- Table -->
    <div class="signatories-table-container">
      <table class="signatories-table table table-hover align-middle" id="signatoryTable">
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Job Title</th>
            <th>Division</th>
            <th class="text-center">Role</th>
            <th class="text-center">Default</th>
            <th style="width:120px;" class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="signatoryTableBody">
          <tr>
            <td colspan="6" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="sigToastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 99999;"></div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="sigDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong><span id="deleteSignatoryName"></span></strong>?</p>
          <p class="text-muted mb-0">This action can be undone by re-adding the signatory.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="btnConfirmSigDelete"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL: ADD / EDIT SIGNATORY (Overlay)      -->
  <!-- ========================================== -->
  <div class="signatories-modal-overlay" id="sigModal">
    <div class="signatories-modal-form">
      <!-- Header -->
      <div class="signatories-modal-header">
        <h5 id="sigModalTitle"><i class="fas fa-user-plus"></i> Add Signatory</h5>
        <button class="btn-close-modal" id="btnCloseSigModal">&times;</button>
      </div>

      <!-- Form -->
      <form id="signatoryForm" autocomplete="off">
        <input type="hidden" id="sigEditId" value="">

        <div class="mb-3">
          <label class="signatories-form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" id="sigFullName" class="form-control" placeholder="e.g., P. Ginigaddarage" required>
          <div class="invalid-feedback" id="sigFullNameError"></div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="signatories-form-label">Job Title <span class="text-danger">*</span></label>
            <select id="sigTitle" class="form-select" required>
              <option value="">-- Select Title --</option>
              <option value="Senior scientist">Senior scientist</option>
              <option value="Principal scientist">Principal scientist</option>
            </select>
            <div class="invalid-feedback" id="sigTitleError"></div>
          </div>
          <div class="col-md-6">
            <label class="signatories-form-label">Division <span class="text-danger">*</span></label>
            <select id="sigDivision" class="form-select" required>
              <option value="">-- Select Division --</option>
              <option value="Post Harvest Technology Division">Post Harvest Technology Division</option>
            </select>
            <div class="invalid-feedback" id="sigDivisionError"></div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="signatories-form-label">Role Type <span class="text-danger">*</span></label>
            <select id="sigRoleType" class="form-select" required>
              <option value="scientist">Scientist</option>
              <option value="head">Head</option>
            </select>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-2">
              <input type="checkbox" id="sigIsDefault" class="form-check-input" checked>
              <label class="form-check-label" for="sigIsDefault">Set as default signatory</label>
            </div>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="signatories-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnCancelSig">Cancel</button>
          <button type="button" class="btn btn-success" id="btnSaveSig">
            <i class="fas fa-save"></i> Save Signatory
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<link rel="stylesheet" href="public/assets/css/manage-signatories.css">

<!-- CSRF Token for AJAX -->
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

<!-- Load External JS -->
<script src="public/assets/js/manage-signatories.js"></script>