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
        <input type="hidden" id="manageUsersCsrfToken" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // ===== USER MANAGEMENT SCRIPT =====

  // === DOM ELEMENTS ===
  const manageUsersModalOverlay = document.getElementById('manageUsersModalOverlay');
  const manageUsersForm = document.getElementById('manageUsersForm');
  const manageUsersBtnNewUser = document.getElementById('manageUsersBtnNewUser');
  const manageUsersBtnCloseModal = document.getElementById('manageUsersBtnCloseModal');
  const manageUsersBtnCancel = document.getElementById('manageUsersBtnCancel');
  const manageUsersBtnSave = document.getElementById('manageUsersBtnSave');
  const manageUsersBtnUpdate = document.getElementById('manageUsersBtnUpdate');
  const manageUsersFormTitle = document.getElementById('manageUsersFormTitle');
  const manageUsersDeleteModal = document.getElementById('manageUsersDeleteModal');
  const manageUsersToastContainer = document.getElementById('manageUsersToastContainer');
  let manageUsersDeleteUserId = null;
  let manageUsersOriginalData = {};

  const CONTROLLER_PATH = 'src/Controllers/UserController.php';

  // === TOAST FUNCTION ===
  function showUserToast(message, type = 'success') {
    const colors = {
      success: 'bg-success text-white',
      warning: 'bg-warning text-dark',
      danger: 'bg-danger text-white'
    };
    const toastEl = document.createElement('div');
    toastEl.className = `toast fade align-items-center ${colors[type] || 'bg-success text-white'} border-0 mb-2 shadow-lg`;
    toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
    manageUsersToastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, {
      delay: 3000,
      autohide: true
    });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // === AJAX HELPER ===
  async function sendUserAjax(action, data) {
    try {
      const response = await fetch(CONTROLLER_PATH, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action,
          csrf_token: document.getElementById('manageUsersCsrfToken').value,
          ...data
        })
      });

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const text = await response.text();
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('Invalid JSON response:', text);
        return {
          status: 'error',
          message: 'Server returned an invalid response. Please check logs.'
        };
      }
    } catch (error) {
      console.error('AJAX Error:', error);
      return {
        status: 'error',
        message: `Network error: ${error.message}`
      };
    }
  }

  // === LOAD USERS ===
  function loadUsers() {
    const tbody = document.querySelector('#manageUsersTable tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';

    sendUserAjax('fetchAll', {}).then(res => {
      tbody.innerHTML = '';

      if (res.status === 'success' && Array.isArray(res.data)) {
        if (res.data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No active users found in database</td></tr>`;
          return;
        }

        res.data.forEach(user => {
          const roleDisplay = (user.role || 'User').replace(/([A-Z])/g, ' $1').trim();
          const status = user.status || 'active';
          const statusBadge = status === 'active' ? 'bg-success' : 'bg-secondary';
          const statusText = status.charAt(0).toUpperCase() + status.slice(1);
          
          tbody.insertAdjacentHTML('beforeend', `
            <tr data-id="${user.user_id}"
                data-fullname="${escapeHtml(user.fullname || '')}"
                data-username="${escapeHtml(user.username || '')}"
                data-email="${escapeHtml(user.email || '')}"
                data-role="${user.role || ''}"
                data-status="${status}">
              <td class="d-none" data-label="ID">${user.user_id}</td>
              <td data-label="Full Name">${escapeHtml(user.fullname || 'N/A')}</td>
              <td data-label="Username">${escapeHtml(user.username || 'N/A')}</td>
              <td data-label="Email">${escapeHtml(user.email || 'N/A')}</td>
              <td data-label="Role">${roleDisplay}</td>
              <td data-label="Status"><span class="badge ${statusBadge}">${statusText}</span></td>
              <td data-label="Actions">
                <button class="btn btn-sm btn-warning btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          `);
        });
        attachUserRowEvents();
        applyUserFilters();
      } else {
        const errorMsg = res.message || 'Failed to connect to user database';
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error: ${errorMsg}</td></tr>`;
        showUserToast(errorMsg, 'danger');
      }
    }).catch(err => {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-wifi"></i> Connection Error. Check console.</td></tr>`;
    });
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // === VALIDATION CORE ===
  const nameRegex = /^[A-Za-z.\s]{3,}$/;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,12}$/;

  function validateInput(inputEl, errorEl, rules) {
    if (!inputEl) return true;
    const val = inputEl.value.trim();
    let isValid = true;
    let errorMessage = '';

    if (rules.required && val === '') {
      isValid = false;
      errorMessage = 'This field is required.';
    } else if (rules.regex && !rules.regex.test(val)) {
      isValid = false;
      errorMessage = rules.regexMessage || 'Invalid format.';
    }

    if (!isValid) {
      inputEl.classList.add('is-invalid');
      errorEl.textContent = errorMessage;
      errorEl.classList.remove('d-none');
      errorEl.classList.add('d-block');
    } else {
      inputEl.classList.remove('is-invalid');
      if (inputEl.value.length > 0) inputEl.classList.add('is-valid');
      errorEl.textContent = '';
      errorEl.classList.remove('d-block');
      errorEl.classList.add('d-none');
    }
    return isValid;
  }

  function clearValidation(inputEl, errorEl) {
    if (!inputEl) return;
    inputEl.classList.remove('is-invalid', 'is-valid');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.remove('d-block');
      errorEl.classList.add('d-none');
    }
  }

  // DOM Bindings
  const inputFullName = document.getElementById('manageUsersFullName');
  const inputUsername = document.getElementById('manageUsersUsername');
  const inputEmail = document.getElementById('manageUsersEmail');
  const inputRole = document.getElementById('manageUsersRole');
  const inputStatus = document.getElementById('manageUsersStatus');
  const inputPassword = document.getElementById('manageUsersPassword');
  const inputConfirmPassword = document.getElementById('manageUsersConfirmPassword');
  const checkChangePassword = document.getElementById('manageUsersChangePasswordCheck');

  const errFullName = document.getElementById('errManageUsersFullName');
  const errUsername = document.getElementById('errManageUsersUsername');
  const errEmail = document.getElementById('errManageUsersEmail');
  const errRole = document.getElementById('errManageUsersRole');
  const errStatus = document.getElementById('errManageUsersStatus');
  const errPassword = document.getElementById('errManageUsersPassword');
  const errConfirmPassword = document.getElementById('errManageUsersConfirmPassword');

  // Event Listeners for real-time validation
  inputFullName.addEventListener('input', () => validateInput(inputFullName, errFullName, {
    required: true,
    regex: nameRegex,
    regexMessage: 'Must be at least 3 letters, spaces only.'
  }));
  inputUsername.addEventListener('input', () => validateInput(inputUsername, errUsername, {
    required: true
  }));
  inputEmail.addEventListener('input', () => validateInput(inputEmail, errEmail, {
    required: true,
    regex: emailRegex,
    regexMessage: 'Invalid email address.'
  }));
  inputRole.addEventListener('change', () => validateInput(inputRole, errRole, {
    required: true
  }));
  inputPassword.addEventListener('input', () => {
    validateInput(inputPassword, errPassword, {
      required: inputPassword.required,
      regex: passwordRegex,
      regexMessage: '8-12 chars, 1 uppercase, 1 lowercase, 1 number.'
    });
    if (inputConfirmPassword.value) validateConfirmPassword();
  });
  inputConfirmPassword.addEventListener('input', validateConfirmPassword);

  checkChangePassword.addEventListener('change', function() {
    const passwordContainer = document.querySelector('.manage-users-password-fields');
    if (this.checked) {
      passwordContainer.classList.remove('d-none');
      inputPassword.required = true;
      inputConfirmPassword.required = true;
    } else {
      passwordContainer.classList.add('d-none');
      inputPassword.required = false;
      inputConfirmPassword.required = false;
      inputPassword.value = '';
      inputConfirmPassword.value = '';
      clearValidation(inputPassword, errPassword);
      clearValidation(inputConfirmPassword, errConfirmPassword);
    }
  });

  function validateConfirmPassword() {
    if (!inputConfirmPassword.required && !inputConfirmPassword.value) return true;
    let isValid = true;
    let msg = '';
    if (inputConfirmPassword.value === '') {
      isValid = false;
      msg = 'This field is required.';
    } else if (inputConfirmPassword.value !== inputPassword.value) {
      isValid = false;
      msg = 'Passwords do not match.';
    }

    if (!isValid) {
      inputConfirmPassword.classList.add('is-invalid');
      errConfirmPassword.textContent = msg;
      errConfirmPassword.classList.remove('d-none');
      errConfirmPassword.classList.add('d-block');
    } else {
      inputConfirmPassword.classList.remove('is-invalid');
      inputConfirmPassword.classList.add('is-valid');
      errConfirmPassword.classList.add('d-none');
      errConfirmPassword.classList.remove('d-block');
    }
    return isValid;
  }

  function validateForm() {
    let isFormValid = true;
    if (!validateInput(inputFullName, errFullName, {
        required: true,
        regex: nameRegex,
        regexMessage: 'Must be at least 3 letters, spaces only.'
      })) isFormValid = false;
    if (!validateInput(inputUsername, errUsername, {
        required: true
      })) isFormValid = false;
    if (!validateInput(inputEmail, errEmail, {
        required: true,
        regex: emailRegex,
        regexMessage: 'Invalid email address.'
      })) isFormValid = false;
    if (!validateInput(inputRole, errRole, {
        required: true
      })) isFormValid = false;

    if (inputPassword.required) {
      if (!validateInput(inputPassword, errPassword, {
          required: true,
          regex: passwordRegex,
          regexMessage: '8-12 chars, 1 uppercase, 1 lowercase, 1 number.'
        })) isFormValid = false;
      if (!validateConfirmPassword()) isFormValid = false;
    }
    return isFormValid;
  }

  // === MODAL CONTROL ===
  function openUserModal(mode) {
    manageUsersModalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    clearValidation(inputFullName, errFullName);
    clearValidation(inputUsername, errUsername);
    clearValidation(inputEmail, errEmail);
    clearValidation(inputRole, errRole);
    clearValidation(inputStatus, errStatus);
    clearValidation(inputPassword, errPassword);
    clearValidation(inputConfirmPassword, errConfirmPassword);

    if (mode === 'create') {
      manageUsersForm.reset();
      document.getElementById('manageUsersUserId').value = '';
      manageUsersBtnSave.classList.remove('d-none');
      manageUsersBtnUpdate.classList.add('d-none');
      manageUsersFormTitle.textContent = 'Create New User';

      document.querySelector('.manage-users-password-fields').classList.remove('d-none');
      document.getElementById('changePasswordContainer').classList.add('d-none');
      document.getElementById('statusContainer').classList.add('d-none'); // Hide status on create (default active)

      inputPassword.required = true;
      inputConfirmPassword.required = true;
    } else {
      manageUsersBtnSave.classList.add('d-none');
      manageUsersBtnUpdate.classList.remove('d-none');
      manageUsersFormTitle.textContent = 'Update User';

      document.querySelector('.manage-users-password-fields').classList.add('d-none');
      document.getElementById('changePasswordContainer').classList.remove('d-none');
      document.getElementById('statusContainer').classList.remove('d-none'); // Show status on edit
      checkChangePassword.checked = false;

      inputPassword.required = false;
      inputConfirmPassword.required = false;
    }
  }

  function closeUserModal() {
    manageUsersModalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    manageUsersForm.reset();
    manageUsersOriginalData = {};
  }

  manageUsersBtnNewUser.onclick = () => openUserModal('create');
  manageUsersBtnCloseModal.onclick = closeUserModal;
  manageUsersBtnCancel.onclick = closeUserModal;
  manageUsersModalOverlay.onclick = e => {
    if (e.target === manageUsersModalOverlay) closeUserModal();
  };

  // === INSERT USER ===
  manageUsersForm.addEventListener('submit', async e => {
    e.preventDefault();
    if (!validateForm()) return;

    const btn = manageUsersBtnSave;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

    const data = {
      fullname: inputFullName.value.trim(),
      username: inputUsername.value.trim(),
      email: inputEmail.value.trim(),
      role: inputRole.value,
      status: inputStatus.value
    };

    if (!document.getElementById('manageUsersUserId').value) {
      data.password = inputPassword.value;
    }

    const res = await sendUserAjax('insert', data);
    if (res.status === 'success') {
      showUserToast(res.message || 'User created successfully!', 'success');
      loadUsers();
      closeUserModal();
    } else {
      showUserToast(res.message || 'Failed to create user', 'danger');
    }
    btn.disabled = false;
    btn.innerHTML = 'Save User';
  });

  // === ATTACH EDIT & DELETE EVENTS ===
  function attachUserRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        openUserModal('edit');
        document.getElementById('manageUsersUserId').value = row.dataset.id;
        inputFullName.value = row.dataset.fullname;
        inputUsername.value = row.dataset.username;
        inputEmail.value = row.dataset.email;

        const role = row.dataset.role;
        if (Array.from(inputRole.options).some(o => o.value === role)) {
          inputRole.value = role;
        } else {
          inputRole.value = '';
        }

        inputStatus.value = row.dataset.status;

        manageUsersOriginalData = {
          fullname: row.dataset.fullname,
          username: row.dataset.username,
          email: row.dataset.email,
          role: row.dataset.role,
          status: row.dataset.status
        };
      };
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        manageUsersDeleteUserId = row.dataset.id;
        document.getElementById('manageUsersDeleteUserName').textContent = row.dataset.fullname;
        new bootstrap.Modal(manageUsersDeleteModal).show();
      };
    });
  }

  // === DEACTIVATE USER ===
  document.getElementById('manageUsersConfirmDeleteBtn').onclick = async () => {
    if (!manageUsersDeleteUserId) return;
    const res = await sendUserAjax('delete', {
      user_id: manageUsersDeleteUserId
    });
    if (res.status === 'success') {
      showUserToast('User deleted successfully!', 'danger');
      loadUsers();
    } else {
      showUserToast(res.message || 'Failed to delete user', 'danger');
    }
    const modal = bootstrap.Modal.getInstance(manageUsersDeleteModal);
    modal.hide();
    manageUsersDeleteUserId = null;
  };

  // === UPDATE USER ===
  manageUsersBtnUpdate.onclick = async () => {
    if (!validateForm()) return;

    const id = document.getElementById('manageUsersUserId').value;
    const data = {
      user_id: id,
      fullname: inputFullName.value.trim(),
      username: inputUsername.value.trim(),
      email: inputEmail.value.trim(),
      role: inputRole.value,
      status: inputStatus.value
    };

    if (checkChangePassword.checked) {
      data.password = inputPassword.value;
    }

    const passwordChanged = checkChangePassword.checked && inputPassword.value !== '';
    const changed = Object.keys(data).some(key => data[key] !== manageUsersOriginalData[key] && key !== 'password' && key !== 'user_id');

    if (!changed && !passwordChanged) {
      showUserToast('No changes detected', 'warning');
      return;
    }

    const btn = manageUsersBtnUpdate;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

    const res = await sendUserAjax('update', data);
    if (res.status === 'success') {
      showUserToast('User updated successfully!', 'success');
      loadUsers();
      closeUserModal();
    } else {
      showUserToast(res.message || 'Update failed', 'danger');
    }
    btn.disabled = false;
    btn.innerHTML = 'Update User';
  };

  // === FILTERS ===
  const manageUsersSearchInput = document.getElementById('manageUsersSearchInput');
  const manageUsersRoleFilter = document.getElementById('manageUsersRoleFilter');
  const manageUsersStatusFilter = document.getElementById('manageUsersStatusFilter');

  manageUsersSearchInput.addEventListener('input', applyUserFilters);
  manageUsersRoleFilter.addEventListener('change', applyUserFilters);
  manageUsersStatusFilter.addEventListener('change', applyUserFilters);

  function applyUserFilters() {
    const search = manageUsersSearchInput.value.toLowerCase();
    const role = manageUsersRoleFilter.value;
    const status = manageUsersStatusFilter.value;
    const rows = document.querySelectorAll('#manageUsersTable tbody tr');
    let visibleCount = 0;

    rows.forEach(tr => {
      if (tr.classList.contains('no-results')) return;
      const combined = `${tr.dataset.fullname} ${tr.dataset.username} ${tr.dataset.email}`.toLowerCase();
      const matchSearch = combined.includes(search);
      const matchRole = (role === 'All Roles') || (tr.dataset.role === role);
      const matchStatus = (status === 'All Status') || (tr.dataset.status === status);

      if (matchSearch && matchRole && matchStatus) {
        tr.style.display = '';
        visibleCount++;
      } else {
        tr.style.display = 'none';
      }
    });

    const noResultsRow = document.querySelector('#manageUsersTable tbody tr.no-results');
    if (visibleCount === 0) {
      if (!noResultsRow) {
        document.querySelector('#manageUsersTable tbody').insertAdjacentHTML(
          'beforeend',
          `<tr class="no-results"><td colspan="7" class="text-center text-muted">No matching users found</td></tr>`
        );
      }
    } else if (noResultsRow) {
      noResultsRow.remove();
    }
  }

  // ==== SHOW / HIDE PASSWORD ====
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetInput = document.getElementById(btn.dataset.target);
      const icon = btn.querySelector('i');

      if (targetInput.type === 'password') {
        targetInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        targetInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  });


  // === INITIAL LOAD ===
  loadUsers();
</script>