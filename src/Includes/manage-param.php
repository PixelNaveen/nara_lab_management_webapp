<div class="page-manage-parameters">
  <div class="parameters-container container">
    <!-- Filter + New -->
    <div class="parameters-card-filter">
      <input type="text" id="searchInput" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
      <select class="form-select" id="statusFilter" style="max-width:120px;">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
      <button class="btn btn-parameters-filter" id="btnFilter">Filter</button>
      <button class="btn btn-outline-secondary" id="btnReset">Reset</button>
      <div class="ms-auto">
        <button class="btn-parameters-new" id="btnNewParam">+ New Parameter</button>
      </div>
    </div>

    <!-- Table -->
    <div class="parameters-table-container">
      <table class="parameters-table table table-hover align-middle" id="parametersTable">
        <thead>
          <tr>
            <th>Parameter Name</th>
            <th>Parameter Code</th>
            <th>Category</th>
            <th>Base Unit</th>
            <th>Swab Enabled</th>
            <th>No. of Variants</th>
            <th>Status</th>
            <!-- <th>Methods</th> NEW: Added column for displaying multiple methods -->
            <th style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="9" class="text-center"> <!-- UPDATED: colspan increased to 9 -->
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
  <div class="parameters-modal-overlay" id="parametersModal">
    <div class="parameters-modal-form">
      <div class="parameters-modal-header">
        <h5 id="parametersModalTitle">New Parameter</h5>
        <button class="btn-close-modal" id="btnCloseModal">&times;</button>
      </div>
      <form id="parameterForm">
        <input type="hidden" id="csrfToken" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="hidden" id="parameterId">
        <input type="hidden" id="formMode" value="create">

        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="parameters-form-label">Parameter Name <span class="text-danger">*</span></label>
            <input type="text" class="parameters-form-control" id="paramName" placeholder="Enter parameter name" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Parameter Code</label>
            <input type="text" class="parameters-form-control" id="paramCode" placeholder="Auto-generated" readonly style="background-color: #f0f0f0;">
            <small class="text-muted">Automatically assigned</small>
          </div>
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Category</label>
            <input type="text" class="parameters-form-control" id="paramCategory" placeholder="Optional">
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="parameters-form-label">Method(s)</label>
            <!-- UPDATED: Changed to multiple select for multi-method support -->
            <select id="paramMethod" multiple placeholder="Select one or more methods..."></select>

            <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple</small>
          </div>
        </div>


        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="parameters-form-label">Base Unit</label>
            <input type="text" class="parameters-form-control" id="paramBaseUnit" placeholder="e.g., CFU/g, mg/L, MPN/100ml">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Swab Test <span class="text-danger">*</span></label>
            <select class="parameters-form-select" id="paramSwab" required>
              <option value="">Select Status</option>
              <option value="1">Enabled</option>
              <option value="0">Disabled</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Status <span class="text-danger">*</span></label>
            <select class="parameters-form-select" id="paramStatus" required>
              <option value="">Select Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Only show on CREATE when swab enabled -->
        <div class="row" id="swabPriceRow" style="display: none;">
          <div class="col-md-12 mb-3">
            <label class="parameters-form-label">Initial Swab Price (Optional)</label>
            <input type="number" step="0.01" min="0" class="parameters-form-control" id="paramSwabPrice" placeholder="0.00">
            <small class="text-muted">Set initial price (can be updated later in Swab Prices page)</small>
          </div>
        </div>

        <div class="parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
          <button type="submit" class="btn btn-success" id="btnSave">Save Parameter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="parameters-modal-overlay" id="deleteConfirmModal">
    <div class="parameters-modal-form">
      <div class="parameters-modal-header">
        <h5>Confirm Delete</h5>
        <button class="btn-close-modal" id="btnCloseDeleteModal">&times;</button>
      </div>
      <div style="padding:24px;">
        <p>Are you sure you want to delete <strong id="deleteParamName"></strong>?</p>
        <p class="text-danger"><small>This will also soft-delete associated swab pricing.</small></p>
        <div class="parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
  <div id="toastContainer"></div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>