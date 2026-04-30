<?php
/**
 * Swab Parameters Page
 * Interface for managing swab-enabled parameter pricing
 */

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Load Choices.js for multi-select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<div class="page-swab-parameters">
  <div class="swab-parameters-container container-fluide">
    <!-- Info Banner -->
    <div class="alert alert-info mb-3">
      <i class="fas fa-info-circle"></i> This page manages swab pricing for swab-enabled parameters.
      To enable/disable swab feature for a parameter, use the <strong>Parameters Management</strong> page.
    </div>

    <!-- Filter + New -->
    <div class="swab-parameters-card-filter">
      <input type="text" id="searchInput" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
      <select class="form-select" id="statusFilter" style="max-width:120px;">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
      <button class="btn btn-swab-parameters-filter" id="btnFilter">Filter</button>
      <button class="btn btn-outline-secondary" id="btnReset">Reset</button>
      <div class="ms-auto d-flex gap-2">
        <button class="btn-swab-parameters-new" data-type="individual" id="btnAddIndividual">+ Add Individual</button>
        <button class="btn-swab-parameters-new" data-type="combo" id="btnAddCombo">+ Add Combo</button>
      </div>
    </div>

    <!-- Table -->
    <div class="swab-parameters-table-container">
      <table class="swab-parameters-table table table-hover align-middle">
        <thead>
          <tr>
            <th>Parameter Name</th>
            <th>Price</th>
            <th>Status</th>
            <th style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="4" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="swab-parameters-modal-overlay" id="swabParametersModal">
    <div class="swab-parameters-modal-form">
      <div class="swab-parameters-modal-header">
        <h5 id="swabModalTitle">New Swab Parameter</h5>
        <button class="btn-close-modal" id="btnCloseModal">&times;</button>
      </div>
      <form id="swabPriceForm">
        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="hidden" id="swabParamId">
        <input type="hidden" id="formMode" value="create">
        <input type="hidden" id="recordType" value="individual">

        <!-- Individual Mode Only: Parameter Dropdown -->
        <div id="individualSection">
          <div class="mb-3" id="parameterSelectRow">
            <label class="swab-parameters-form-label">Parameter <span class="text-danger">*</span></label>
            <select class="form-select" id="parameterSelect">
              <option value="">-- Select Parameter --</option>
            </select>
            <div class="invalid-feedback" id="parameterSelectError"></div>
            <small class="text-muted d-block mt-1">Only swab-enabled parameters without swab pricing are shown</small>
          </div>

          <!-- Read-only fields (only for edit mode) -->
          <div class="mb-3" id="parameterNameRow" style="display: none;">
            <label class="swab-parameters-form-label">Parameter Name</label>
            <input type="text" class="swab-parameters-form-control" id="parameterName" readonly style="background-color: #f0f0f0;">
          </div>
        </div>

        <!-- Combo Mode Only: Multi-Select -->
        <div id="comboSection" class="d-none">
          <div class="mb-3">
            <label class="swab-parameters-form-label">Included Parameters <span class="text-danger">*</span></label>
            <select class="form-select" id="comboParameters" multiple></select>
            <div class="invalid-feedback" id="comboParametersError">Please select at least 2 parameters.</div>
            <small class="text-muted d-block mt-1">Select 2 or more active swab-enabled parameters.</small>
          </div>
        </div>

        <div class="mb-3">
          <label class="swab-parameters-form-label">Price <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="form-control" id="swabPrice" placeholder="0.00" required>
          <div class="invalid-feedback" id="swabPriceError"></div>
        </div>

        <div class="mb-3">
          <label class="swab-parameters-form-label">Status <span class="text-danger">*</span></label>
          <select class="form-select" id="swabStatus" required>
            <option value="">-- Select Status --</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <div class="invalid-feedback" id="swabStatusError"></div>
        </div>

        <div class="swab-parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
          <button type="submit" class="btn btn-success" id="btnSave">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="swab-parameters-modal-overlay" id="deleteConfirmModal">
    <div class="swab-parameters-modal-form">
      <div class="swab-parameters-modal-header">
        <h5>Confirm Delete</h5>
        <button class="btn-close-modal" id="btnCloseDeleteModal">&times;</button>
      </div>
      <div style="padding:24px;">
        <p>Are you sure you want to delete swab pricing for <strong id="deleteParamName"></strong>?</p>
        <div class="swab-parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:99999;">
  <div id="toastContainer"></div>
</div>

<!-- Load External JS -->
<script src="public/assets/js/swab-param.js"></script>