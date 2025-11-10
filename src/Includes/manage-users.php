<!-- Manage Users Page Wrapper -->
<div class="page-manage-users">

  <div class="manage-users-container container">

    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
      <input
        type="text"
        class="form-control"
        id="manageUsersSearchInput"
        placeholder="Search by name, username or email"
        style="max-width: 250px;" />

      <select class="form-select" id="manageUsersRoleFilter" style="max-width: 160px;">
        <option value="All Roles">All Roles</option>
        <option value="LabTechnician">Lab Technician</option>
        <option value="Assistant">Assistant</option>
        <option value="Admin">Admin</option>
      </select>

      <select class="form-select" id="manageUsersStatusFilter" style="max-width: 120px;">
        <option value="All Status">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

      <button id="manageUsersBtnFilter" class="btn btn-outline-secondary btn-sm manage-users-btn-filter">Filter</button>

      <!-- New User button pushed to right -->
      <div class="ms-auto">
        <button class="btn btn-primary btn-sm manage-users-btn-new" id="manageUsersBtnNewUser">+ New User</button>
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
      
      <form id="manageUsersForm">
        <input type="hidden" id="manageUsersUserId">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Full Name <span class="manage-users-text-danger">*</span></label>
            <input type="text" class="manage-users-form-control form-control" id="manageUsersFullName" placeholder="Enter full name" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Username <span class="manage-users-text-danger">*</span></label>
            <input type="text" class="manage-users-form-control form-control" id="manageUsersUsername" placeholder="Enter username" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Email <span class="manage-users-text-danger">*</span></label>
            <input type="email" class="manage-users-form-control form-control" id="manageUsersEmail" placeholder="Enter email" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Role <span class="manage-users-text-danger">*</span></label>
            <select class="manage-users-form-select form-select" id="manageUsersRole" required>
              <option value="">Select Role</option>
              <option value="LabTechnician">Lab Technician</option>
              <option value="Assistant">Assistant</option>
              <option value="Admin">Admin</option>
            </select>
          </div>
        </div>

        <div class="row manage-users-password-fields">
          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Password <span class="manage-users-text-danger">*</span></label>
            <input type="password" class="manage-users-form-control form-control" id="manageUsersPassword" placeholder="Enter password">
          </div>

          <div class="col-md-6 mb-3">
            <label class="manage-users-form-label">Confirm Password <span class="manage-users-text-danger">*</span></label>
            <input type="password" class="manage-users-form-control form-control" id="manageUsersConfirmPassword" placeholder="Re-enter password">
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
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Confirm Deactivation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to deactivate <span id="manageUsersDeleteUserName"></span>?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="manageUsersConfirmDeleteBtn">Deactivate</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
    <div id="manageUsersToastContainer"></div>
  </div>

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

  const CONTROLLER_PATH = '../../src/Controllers/user-controller.php';

  // === TOAST FUNCTION ===
  function showUserToast(message, type = 'success') {
    const colors = {
      success: 'bg-success text-white',
      warning: 'bg-warning text-dark',
      danger: 'bg-danger text-white'
    };
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${colors[type] || 'bg-success text-white'} border-0 mb-2`;
    toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
    manageUsersToastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, {
      delay: 2500
    });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // === AJAX HELPER ===
  function sendUserAjax(action, data) {
    return fetch(CONTROLLER_PATH, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action,
          ...data
        })
      })
      .then(res => res.json())
      .catch(() => ({
        status: 'error',
        message: 'Network error!'
      }));
  }

  // === LOAD USERS ===
  function loadUsers() {
    sendUserAjax('fetchAll', {}).then(res => {
      const tbody = document.querySelector('#manageUsersTable tbody');
      tbody.innerHTML = '';

      if (res.status === 'success' && Array.isArray(res.data)) {
        res.data.forEach(user => {
          const roleDisplay = user.role.replace(/([A-Z])/g, ' $1').trim();
          const statusBadge = user.status === 'active' ? 'bg-success' : 'bg-secondary';
          const statusText = user.status.charAt(0).toUpperCase() + user.status.slice(1);
          tbody.insertAdjacentHTML('beforeend', `
  <tr data-id="${user.user_id}"
      data-fullname="${user.fullname}"
      data-username="${user.username}"
      data-email="${user.email}"
      data-role="${user.role}"
      data-status="${user.status}">
    <td class="d-none">${user.user_id}</td>
    <td>${user.fullname}</td>
    <td>${user.username}</td>
    <td>${user.email}</td>
    <td>${roleDisplay}</td>
    <td><span class="badge ${statusBadge}">${statusText}</span></td>
    <td>
      <button class="btn btn-sm btn-warning btn-edit"><i class="fas fa-edit"></i></button>
      <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
    </td>
  </tr>
`);
        });
        attachUserRowEvents();
        applyUserFilters(); // Apply filters after loading
      } else {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>`;
      }
    });
  }

  // === MODAL CONTROL ===
  function openUserModal(mode) {
    manageUsersModalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (mode === 'create') {
      manageUsersForm.reset();
      document.getElementById('manageUsersUserId').value = '';
      manageUsersBtnSave.classList.remove('d-none');
      manageUsersBtnUpdate.classList.add('d-none');
      manageUsersFormTitle.textContent = 'Create New User';
      document.querySelector('.manage-users-password-fields').classList.remove('d-none');
      document.getElementById('manageUsersPassword').required = true;
      document.getElementById('manageUsersConfirmPassword').required = true;
    } else {
      manageUsersBtnSave.classList.add('d-none');
      manageUsersBtnUpdate.classList.remove('d-none');
      manageUsersFormTitle.textContent = 'Update User';
      document.querySelector('.manage-users-password-fields').classList.add('d-none');
      document.getElementById('manageUsersPassword').required = false;
      document.getElementById('manageUsersConfirmPassword').required = false;
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
  manageUsersForm.addEventListener('submit', e => {
    e.preventDefault();

    const password = document.getElementById('manageUsersPassword').value;
    const confirmPassword = document.getElementById('manageUsersConfirmPassword').value;

    if (!document.getElementById('manageUsersUserId').value && password !== confirmPassword) {
      showUserToast('Passwords do not match!', 'warning');
      return;
    }

    const data = {
      fullname: manageUsersForm.manageUsersFullName.value.trim(),
      username: manageUsersForm.manageUsersUsername.value.trim(),
      email: manageUsersForm.manageUsersEmail.value.trim(),
      role: manageUsersForm.manageUsersRole.value
    };

    if (!document.getElementById('manageUsersUserId').value) {
      data.password = password;
    }

    sendUserAjax('insert', data).then(res => {
      if (res.status === 'success') {
        showUserToast(res.message || 'User created successfully!', 'success');
        loadUsers();
        closeUserModal();
      } else {
        showUserToast(res.message || 'Failed to create user', 'danger');
      }
    });
  });

  // === ATTACH EDIT & DELETE EVENTS ===
  function attachUserRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        openUserModal('edit');
        document.getElementById('manageUsersUserId').value = row.dataset.id;
        manageUsersForm.manageUsersFullName.value = row.dataset.fullname;
        manageUsersForm.manageUsersUsername.value = row.dataset.username;
        manageUsersForm.manageUsersEmail.value = row.dataset.email;
        manageUsersForm.manageUsersRole.value = row.dataset.role;

        manageUsersOriginalData = {
          fullname: row.dataset.fullname,
          username: row.dataset.username,
          email: row.dataset.email,
          role: row.dataset.role
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
  document.getElementById('manageUsersConfirmDeleteBtn').onclick = () => {
    if (!manageUsersDeleteUserId) return;
    sendUserAjax('delete', {
      user_id: manageUsersDeleteUserId
    }).then(res => {
      if (res.status === 'success') {
        showUserToast('User deactivated successfully!', 'danger');
        loadUsers();
      } else {
        showUserToast(res.message || 'Failed to deactivate user', 'danger');
      }
      const modal = bootstrap.Modal.getInstance(manageUsersDeleteModal);
      modal.hide();
      manageUsersDeleteUserId = null;
    });
  };

  // === UPDATE USER ===
  manageUsersBtnUpdate.onclick = () => {
    const id = document.getElementById('manageUsersUserId').value;
    const data = {
      user_id: id,
      fullname: manageUsersForm.manageUsersFullName.value.trim(),
      username: manageUsersForm.manageUsersUsername.value.trim(),
      email: manageUsersForm.manageUsersEmail.value.trim(),
      role: manageUsersForm.manageUsersRole.value
    };

    const changed = Object.keys(data).some(key => data[key] !== manageUsersOriginalData[key]);
    if (!changed) {
      showUserToast('No changes detected', 'warning');
      return;
    }

    sendUserAjax('update', data).then(res => {
      if (res.status === 'success') {
        showUserToast('User updated successfully!', 'success');
        loadUsers();
        closeUserModal();
      } else {
        showUserToast(res.message || 'Update failed', 'danger');
      }
    });
  };

  // === FILTERS ===
  const manageUsersSearchInput = document.getElementById('manageUsersSearchInput');
  const manageUsersRoleFilter = document.getElementById('manageUsersRoleFilter');
  const manageUsersStatusFilter = document.getElementById('manageUsersStatusFilter');
  const manageUsersBtnFilter = document.getElementById('manageUsersBtnFilter');

  manageUsersSearchInput.addEventListener('input', applyUserFilters);
  manageUsersBtnFilter.onclick = applyUserFilters;
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

  // === INITIAL LOAD ===
  loadUsers();
</script>