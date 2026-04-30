<div class="container-fluid">


  <!-- Enterprise Grade Filter Bar -->
  <div class="row g-3 align-items-center mb-4 mx-0">
    <div class="col-12 col-lg-9">
      <div class="input-group shadow-sm rounded-3 overflow-hidden">
        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control border-start-0 ps-0 shadow-none" id="searchInput" placeholder="Search clients by name, city or phone number..." />
      </div>
    </div>

    <div class="col-12 col-lg-3 d-flex justify-content-lg-end mt-2 mt-lg-0">
      <button id="btnNewClient" class="btn btn-primary fw-medium shadow-sm px-4 py-2" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); border: none; white-space: nowrap; height: 40px;">
        <i class="fas fa-plus me-1"></i> New Client
      </button>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-12">
      <div class="table-container">
        <table class="table table-hover align-middle clientsTable" id="clientsTable">
          <thead>
            <tr>
              <th class="d-none">ID</th>
              <th>Client Name</th>
              <th>Address</th>
              <th>City</th>
              <th>Phone</th>
              <th>Contact Person</th>
              <th>Registration Date</th>
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

<!-- Client Modal -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-form">
    <div class="modal-header">
      <h5 id="formTitle">Create New Client</h5>
      <button class="btn-close-modal" id="btnCloseModal"><i class="fas fa-times"></i></button>
    </div>

    <form id="clientForm" method="post">
      <input type="hidden" id="clientId">
      <input type="hidden" id="csrfToken" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" id="lblClientName">Client Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="clientName" placeholder="Enter client name" name="clientName" required>
          <div class="invalid-feedback" id="clientNameError"></div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" id="lblContactPerson">Contact Person <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contactPerson" placeholder="Enter contact person" name="contactPerson" required>
          <div class="invalid-feedback" id="contactPersonError"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="form-label" id="lblAddress">Address Line 1 <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="addressLine1" placeholder="Enter street address" name="address" required>
          <div class="invalid-feedback" id="addressError"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" id="lblCity">City <span class="text-danger">*</span></label>
          <div class="position-relative">
            <input type="text" class="form-control" id="city" name="city" placeholder="Type to search cities..." autocomplete="off" required>
            <div class="city-autocomplete" id="cityAutocomplete"></div>
            <div class="invalid-feedback" id="cityError" style="position: static;"></div>
            <div class="valid-feedback text-info" id="cityWarning" style="display:none; position: static;">New city will be saved.</div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" id="lblPhone">Primary Phone <span class="text-danger">*</span></label>
          <input type="tel" class="form-control" id="phonePrimary" placeholder="0XXXXXXXXX" name="phoneNo" required maxlength="10">
          <div class="invalid-feedback" id="phoneError"></div>
        </div>
      </div>

      <div class="modal-footer-btns">
        <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
        <button type="submit" class="btn btn-success" id="btnSave">Save Client</button>
        <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Client</button>
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
        Are you sure you want to delete <span id="deleteClientName"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

</div>
</div>

<!-- Toast Container at root for maximum visibility -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="toastContainer"></div>
</div>

<script src="public/assets/js/manage-clients.js"></script>