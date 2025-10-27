<!-- Manage Users Page Wrapper -->
<div class="page-manage-users">

  <div class="manage-users-container container">

    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
      <input
        type="text"
        class="form-control"
        id="manageUsersSearchInput"
        placeholder="Search by name or email"
        style="max-width: 250px;" />

      <select class="form-select" id="manageUsersRoleFilter" style="max-width: 160px;">
        <option>All Roles</option>
        <option>Lab Technician</option>
        <option>Assistant</option>
        <option>Admin</option>
      </select>

      <select class="form-select" id="manageUsersStatusFilter" style="max-width: 120px;">
        <option>All Status</option>
        <option>Active</option>
        <option>Inactive</option>
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
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr data-id="1" data-fullname="John Doe" data-username="johnd" data-email="john@example.com" data-role="LabTechnician" data-status="active">
                <td>1</td>
                <td>John Doe</td>
                <td>johnd</td>
                <td>john@example.com</td>
                <td>Lab Technician</td>
                <td><span class="manage-users-badge bg-success">Active</span></td>
                <td>
                  <button class="btn btn-sm manage-users-btn-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm manage-users-btn-delete" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr data-id="2" data-fullname="Jane Smith" data-username="janes" data-email="jane@example.com" data-role="Assistant" data-status="inactive">
                <td>2</td>
                <td>Jane Smith</td>
                <td>janes</td>
                <td>jane@example.com</td>
                <td>Assistant</td>
                <td><span class="manage-users-badge bg-secondary">Inactive</span></td>
                <td>
                  <button class="btn btn-sm manage-users-btn-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm manage-users-btn-delete" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
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

</div>

<script>
  // Scope all selectors to the page wrapper
  const wrapper = document.querySelector('.page-manage-users');
  const manageUsersModalOverlay = wrapper.querySelector('#manageUsersModalOverlay');
  const manageUsersBtnNewUser = wrapper.querySelector('#manageUsersBtnNewUser');
  const manageUsersBtnCloseModal = wrapper.querySelector('#manageUsersBtnCloseModal');
  const manageUsersBtnCancel = wrapper.querySelector('#manageUsersBtnCancel');
  const manageUsersFormTitle = wrapper.querySelector('#manageUsersFormTitle');
  const manageUsersBtnSave = wrapper.querySelector('#manageUsersBtnSave');
  const manageUsersBtnUpdate = wrapper.querySelector('#manageUsersBtnUpdate');
  const manageUsersForm = wrapper.querySelector('#manageUsersForm');
  const manageUsersPasswordFields = wrapper.querySelectorAll('.manage-users-password-fields');

  // Open modal for new user
  manageUsersBtnNewUser.addEventListener('click', () => {
    openManageUsersModal('create');
  });

  // Close modal
  manageUsersBtnCloseModal.addEventListener('click', closeManageUsersModal);
  manageUsersBtnCancel.addEventListener('click', closeManageUsersModal);
  
  // Close modal when clicking outside
  manageUsersModalOverlay.addEventListener('click', (e) => {
    if (e.target === manageUsersModalOverlay) {
      closeManageUsersModal();
    }
  });

  // Edit button click
  wrapper.querySelectorAll('.manage-users-btn-edit').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const row = e.target.closest('tr');
      loadManageUsersData(row);
      openManageUsersModal('edit');
    });
  });

  // Delete button click
  wrapper.querySelectorAll('.manage-users-btn-delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const row = e.target.closest('tr');
      const userName = row.dataset.fullname;
      if (confirm(`Are you sure you want to delete ${userName}?`)) {
        row.remove();
      }
    });
  });

  // Form submission
  manageUsersForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const password = wrapper.querySelector('#manageUsersPassword').value;
    const confirmPassword = wrapper.querySelector('#manageUsersConfirmPassword').value;
    
    if (!wrapper.querySelector('#manageUsersUserId').value && password !== confirmPassword) {
      alert('Passwords do not match!');
      return;
    }
    
    console.log('Form submitted');
    closeManageUsersModal();
  });

  // Update button click
  manageUsersBtnUpdate.addEventListener('click', () => {
    console.log('User updated');
    closeManageUsersModal();
  });

  function openManageUsersModal(mode) {
    manageUsersModalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (mode === 'create') {
      manageUsersFormTitle.textContent = 'Create New User';
      manageUsersForm.reset();
      manageUsersBtnSave.classList.remove('d-none');
      manageUsersBtnUpdate.classList.add('d-none');
      manageUsersPasswordFields.forEach(el => el.classList.remove('d-none'));
      wrapper.querySelector('#manageUsersPassword').required = true;
      wrapper.querySelector('#manageUsersConfirmPassword').required = true;
    } else if (mode === 'edit') {
      manageUsersFormTitle.textContent = 'Update User';
      manageUsersBtnSave.classList.add('d-none');
      manageUsersBtnUpdate.classList.remove('d-none');
      manageUsersPasswordFields.forEach(el => el.classList.add('d-none'));
      wrapper.querySelector('#manageUsersPassword').required = false;
      wrapper.querySelector('#manageUsersConfirmPassword').required = false;
    }
  }

  function closeManageUsersModal() {
    manageUsersModalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    manageUsersForm.reset();
  }

  function loadManageUsersData(row) {
    wrapper.querySelector('#manageUsersUserId').value = row.dataset.id;
    wrapper.querySelector('#manageUsersFullName').value = row.dataset.fullname;
    wrapper.querySelector('#manageUsersUsername').value = row.dataset.username;
    wrapper.querySelector('#manageUsersEmail').value = row.dataset.email;
    wrapper.querySelector('#manageUsersRole').value = row.dataset.role;
  }
</script>
