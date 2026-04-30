<?php
/**
 * Manage Test Methods Page
 * CRUD interface for laboratory test methods
 */

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="container-fluide">

  <div class="card-filter d-flex flex-wrap gap-2 align-items-center mb-3">
    <input type="text" class="form-control" id="searchInput" placeholder="Search by method name or standard body" style="max-width: 250px;" />

    <select class="form-select" id="standardBodyFilter" style="max-width: 160px;">
      <option>All Standard Bodies</option>
      <option>ISO</option>
      <option>SLS</option>
      <option>APHA</option>
    </select>

    <select class="form-select" id="statusFilter" style="max-width: 120px;">
      <option value="All Status">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>

    <button id="btnFilter" class="btn btn-outline-secondary btn-sm" style="min-width: 80px;">Filter</button>

    <div class="ms-auto">
      <button class="btn btn-primary btn-sm" id="btnNewTestMethod">+ New Test Method</button>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-12">
      <div class="table-container">
        <table class="table table-hover align-middle testMethodsTable" id="testMethodsTable">
          <thead>
            <tr>
              <th class="d-none">ID</th>
              <th>Method Name</th>
              <th>Standard Body</th>
              <th>Status</th>
              <th style="width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Data will be loaded via AJAX -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Test Method Modal -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-form">
    <div class="modal-header">
      <h5 id="formTitle">Create New Test Method</h5>
      <button class="btn-close-modal" id="btnCloseModal"><i class="fas fa-times"></i></button>
    </div>

    <form id="testMethodForm" method="post" autocomplete="off">
      <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
      <input type="hidden" id="testMethodId" name="method_id">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Method Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="methodName" placeholder="Enter method name" name="methodName" required>
          <div class="invalid-feedback" id="methodNameError"></div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Standard Body <span class="text-danger">*</span></label>
          <select class="form-select" id="standardBody" name="standardBody" required>
            <option value="">Select Standard Body</option>
            <option value="ISO">ISO</option>
            <option value="SLS">SLS</option>
            <option value="APHA">APHA</option>
          </select>
          <div class="invalid-feedback" id="standardBodyError"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Status <span class="text-danger">*</span></label>
          <select class="form-select" id="status" name="status" required>
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <div class="invalid-feedback" id="statusError"></div>
        </div>
      </div>

      <div class="modal-footer-btns">
        <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
        <button type="submit" class="btn btn-success" id="btnSave">Save Test Method</button>
        <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Test Method</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <span id="deleteTestMethodName"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:99999;">
  <div id="toastContainer"></div>
</div>

<!-- Load External JS -->
<script src="public/assets/js/manage-test-methods.js"></script>