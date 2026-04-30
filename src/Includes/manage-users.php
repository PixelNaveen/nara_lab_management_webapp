<?php
/**
 * Manage Users Page
 * CRUD interface for application user management
 */

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Manage Users Page Wrapper -->
<div class="page-manage-users">

  <div class="manage-users-container container-fluide">

    <!-- Enterprise Grade Filter Bar -->
    <div class="row g-3 align-items-center mb-4 mx-0">
      <!-- Search & Filters -->
      <div class="col-12 col-lg-9">
        <div class="row g-2">
          <div class="col-12 col-md-6 col-lg-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control border-start-0 ps-0 shadow-none" id="manageUsersSearchInput" placeholder="Search users..." />
            </div>
          </div>

          <div class="col-6 col-md-3 col-lg-3">
            <select class="form-select shadow-sm rounded-3 cursor-pointer" id="manageUsersRoleFilter">
              <option value="All Roles">All Roles</option>
              <option value="Admin">Admin</option>
              <option value="LabManager">Lab Manager</option>
              <option value="LabTechnician">Lab Tech</option>
              <option value="Receptionist">Receptionist</option>
              <option value="Client">Client</option>
            </select>
          </div>

          <div class="col-6 col-md-3 col-lg-3">
            <select class="form-select shadow-sm rounded-3 cursor-pointer" id="manageUsersStatusFilter">
              <option value="All Status">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Action Button -->
      <div class="col-12 col-lg-3 d-flex justify-content-lg-end mt-2 mt-lg-0">
        <button id="manageUsersBtnNewUser" class="btn btn-primary fw-medium shadow-sm px-4 py-2" style="background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%); border: none; white-space: nowrap; height: 40px;">
          <i class="fas fa-user-plus me-1"></i> New User
        </button>
      </div>
    </div>

    <div class="row g-4">
      <!-- Users Table -->
      <div class="col-lg-12">
        <div class="manage-users-table-container">
          <table class="manage-users-table table table-hover align-middle" id="manageUsersTable">
            <thead>
              <tr>
                <th class="d-none">ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
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

  <!-- Modal Overlay Form -->
  <div class="manage-users-modal-overlay" id="manageUsersModalOverlay">
    <div class="manage-users-modal-form">
      <div class="manage-users-modal-header">
        <h5 id="manageUsersFormTitle">Create New User</h5>
        <button class="manage-users-btn-close-modal" id="manageUsersBtnCloseModal">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form id="manageUsersForm" novalidate>
        <input type="hidden" id="manageUsersUserId">
        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersFullName">Full Name <span class="manage-users-text-danger">*</span></label>
            <input type="text" class="manage-users-form-control form-control" id="manageUsersFullName" placeholder="Enter full name">
            <small class="text-danger d-none" id="errManageUsersFullName"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersUsername">Username <span class="manage-users-text-danger">*</span></label>
            <input type="text" class="manage-users-form-control form-control" id="manageUsersUsername" placeholder="Enter username">
            <small class="text-danger d-none" id="errManageUsersUsername"></small>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersEmail">Email <span class="manage-users-text-danger">*</span></label>
            <input type="email" class="manage-users-form-control form-control" id="manageUsersEmail" placeholder="Enter email">
            <small class="text-danger d-none" id="errManageUsersEmail"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersRole">Role <span class="manage-users-text-danger">*</span></label>
            <select class="manage-users-form-select form-select" id="manageUsersRole">
              <option value="">Select Role</option>
              <option value="Admin">Admin</option>
              <option value="LabManager">Lab Manager</option>
              <option value="LabTechnician">Lab Technician</option>
              <option value="Receptionist">Receptionist</option>
              <option value="Client">Client</option>
            </select>
            <small class="text-danger d-none" id="errManageUsersRole"></small>
          </div>

          <div class="col-md-6 mb-3" id="statusContainer">
            <label class="manage-users-form-label">Status <span class="manage-users-text-danger">*</span></label>
            <select class="manage-users-form-select form-select" id="manageUsersStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <small class="text-danger d-none" id="errManageUsersStatus"></small>
          </div>
        </div>

        <div class="row mb-2 d-none" id="changePasswordContainer">
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="manageUsersChangePasswordCheck">
              <label class="form-check-label text-warning fw-bold" for="manageUsersChangePasswordCheck">
                <i class="fas fa-key me-1"></i> Change Password
              </label>
            </div>
            <small class="text-muted d-block mt-1">Check this box if you want to update the user's password.</small>
          </div>
        </div>

        <div class="row manage-users-password-fields">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersPassword">Password <span class="manage-users-text-danger">*</span></label>
            <div class="input-group">
              <input type="password" class="manage-users-form-control form-control" id="manageUsersPassword" placeholder="Enter password">
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="manageUsersPassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <small class="text-danger d-none" id="errManageUsersPassword"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label" id="lblManageUsersConfirmPassword">Confirm Password <span class="manage-users-text-danger">*</span></label>
            <div class="input-group">
              <input type="password" class="manage-users-form-control form-control" id="manageUsersConfirmPassword" placeholder="Re-enter password">
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="manageUsersConfirmPassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <small class="text-danger d-none" id="errManageUsersConfirmPassword"></small>
          </div>
        </div>


        <div class="manage-users-modal-footer-btns">
          <button type="button" class="btn manage-users-btn-secondary" id="manageUsersBtnCancel">Cancel</button>
          <button type="submit" class="btn manage-users-btn-success" id="manageUsersBtnSave">Save User</button>
          <button type="button" class="btn manage-users-btn-warning d-none" id="manageUsersBtnUpdate">Update User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="manageUsersDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete <span id="manageUsersDeleteUserName" class="fw-bold"></span>?<br>
          <small class="text-muted">This will remove the user from the active lists.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="manageUsersConfirmDeleteBtn">Delete User</button>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Toast Container at root for maximum visibility -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="manageUsersToastContainer"></div>
</div>

<!-- Load External JS -->
<script src="public/assets/js/manage-users.js"></script>