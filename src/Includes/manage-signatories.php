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

<script>
(() => {
  // ========================================
  // GLOBALS & CONFIG
  // ========================================
  const API_URL = 'src/Controllers/SignatoryController.php';
  let allSignatories = [];
  let deleteSignatoryId = null;

  // ========================================
  // DOM REFERENCES
  // ========================================
  const modal = document.getElementById('sigModal');
  const form  = document.getElementById('signatoryForm');
  const deleteModal = document.getElementById('sigDeleteModal');

  // ========================================
  // INIT
  // ========================================
  loadSignatories();

  // ========================================
  // TOAST HELPER
  // ========================================
  function showToast(message, type = 'success') {
    const colors = {
      success: 'bg-success text-white',
      warning: 'bg-warning text-dark',
      danger:  'bg-danger text-white',
      info:    'bg-warning text-dark'
    };
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${colors[type]} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close ${type === 'warning' ? 'btn-close-black' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;
    document.getElementById('sigToastContainer').appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // ========================================
  // LOAD SIGNATORIES
  // ========================================
  function loadSignatories() {
    const fd = new FormData();
    fd.append('action', 'fetchAll');

    fetch(API_URL, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.status !== 'success') return;
        allSignatories = res.data || [];
        updateStats(allSignatories);
        renderTable(allSignatories);
      });
  }

  function updateStats(data) {
    document.getElementById('statTotal').textContent = data.length;
    document.getElementById('statScientists').textContent = data.filter(s => s.role_type === 'scientist').length;
    document.getElementById('statHeads').textContent = data.filter(s => s.role_type === 'head').length;
  }

  function renderTable(data) {
    const tbody = document.getElementById('signatoryTableBody');

    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No signatories found</td></tr>';
      return;
    }

    tbody.innerHTML = data.map(s => {
      const roleBadge = s.role_type === 'scientist'
        ? '<span class="badge bg-success-subtle text-success border border-success"><i class="fas fa-microscope me-1"></i> Scientist</span>'
        : '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fas fa-user-tie me-1"></i> Head</span>';

      const defaultBadge = s.is_default == 1
        ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Yes</span>'
        : '<span class="badge bg-secondary">No</span>';

      const displayName = s.full_name;
      const shortLabel = s.title ? `<br><small class="text-muted">${escHtml(s.title)}</small>` : '';

      return `<tr>
        <td data-label="Full Name:" class="param-name-cell">${escHtml(displayName)}</td>
        <td data-label="Job Title:">${escHtml(s.title)}</td>
        <td data-label="Division:">${escHtml(s.division)}</td>
        <td data-label="Role:" class="text-center">${roleBadge}</td>
        <td data-label="Default:" class="text-center">${defaultBadge}</td>
        <td data-label="Actions:" class="text-center">
          <button class="btn-signatories-edit" onclick="editSignatory(${s.signatory_id})"><i class="fas fa-edit"></i></button>
          <button class="btn-signatories-delete" onclick="deleteSignatory(${s.signatory_id}, '${escHtml(s.full_name)}')"><i class="fas fa-trash"></i></button>
        </td>
      </tr>`;
    }).join('');
  }

  // ========================================
  // FILTER & SEARCH
  // ========================================
  document.getElementById('sigSearchInput').addEventListener('input', applyFilters);
  document.getElementById('btnSigFilter').addEventListener('click', applyFilters);
  document.getElementById('sigRoleFilter').addEventListener('change', applyFilters);
  document.getElementById('btnSigReset').addEventListener('click', () => {
    document.getElementById('sigSearchInput').value = '';
    document.getElementById('sigRoleFilter').value = '';
    renderTable(allSignatories);
  });

  function applyFilters() {
    const search = document.getElementById('sigSearchInput').value.trim().toLowerCase();
    const role = document.getElementById('sigRoleFilter').value;
    let filtered = allSignatories;

    if (search) {
      filtered = filtered.filter(s =>
        s.full_name.toLowerCase().includes(search) ||
        (s.title && s.title.toLowerCase().includes(search))
      );
    }
    if (role) {
      filtered = filtered.filter(s => s.role_type === role);
    }
    renderTable(filtered);
  }

  // ========================================
  // MODAL CONTROL
  // ========================================
  document.getElementById('btnNewSignatory').addEventListener('click', () => {
    form.reset();
    document.getElementById('sigEditId').value = '';
    document.getElementById('sigModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add Signatory';
    clearValidation();
    modal.classList.add('active');
  });

  document.getElementById('btnCloseSigModal').addEventListener('click', closeModal);
  document.getElementById('btnCancelSig').addEventListener('click', closeModal);
  modal.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });

  function closeModal() {
    modal.classList.remove('active');
    clearValidation();
  }

  // ========================================
  // VALIDATION
  // ========================================
  const nameInput = document.getElementById('sigFullName');
  const titleInput = document.getElementById('sigTitle');
  const divisionInput = document.getElementById('sigDivision');
  const nameRegex = /^[a-zA-Z\s.\-]+$/;

  nameInput.addEventListener('input', () => validateField(nameInput, 'sigFullNameError', {
    required: true,
    minLength: 3,
    regex: nameRegex,
    regexMsg: 'Only letters, dots, spaces, and hyphens allowed.'
  }));
  titleInput.addEventListener('change', () => validateField(titleInput, 'sigTitleError', { required: true }));
  divisionInput.addEventListener('change', () => validateField(divisionInput, 'sigDivisionError', { required: true }));

  function validateField(inputEl, errorElId, rules) {
    const val = inputEl.value.trim();
    const errorEl = document.getElementById(errorElId);
    let isValid = true;
    let msg = '';

    if (rules.required && val === '') {
      isValid = false;
      msg = 'This field is required.';
    } else if (rules.minLength && val.length < rules.minLength) {
      isValid = false;
      msg = `Must be at least ${rules.minLength} characters.`;
    } else if (rules.regex && val !== '' && !rules.regex.test(val)) {
      isValid = false;
      msg = rules.regexMsg || 'Invalid format.';
    }

    if (!isValid) {
      inputEl.classList.add('is-invalid');
      errorEl.textContent = msg;
      errorEl.style.display = 'block';
    } else {
      inputEl.classList.remove('is-invalid');
      if (val.length > 0) inputEl.classList.add('is-valid');
      errorEl.textContent = '';
      errorEl.style.display = 'none';
    }
    return isValid;
  }

  function clearValidation() {
    document.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
  }

  // ========================================
  // SAVE (CREATE / UPDATE)
  // ========================================
  document.getElementById('btnSaveSig').addEventListener('click', () => {
    const vName = validateField(nameInput, 'sigFullNameError', { required: true, minLength: 3, regex: nameRegex, regexMsg: 'Only letters, dots, spaces, and hyphens allowed.' });
    const vTitle = validateField(titleInput, 'sigTitleError', { required: true });
    const vDiv = validateField(divisionInput, 'sigDivisionError', { required: true });

    if (!vName || !vTitle || !vDiv) {
      return;
    }

    const editId = document.getElementById('sigEditId').value;
    const isEdit = editId && editId !== '';

    const fd = new FormData();
    fd.append('action', isEdit ? 'update' : 'create');
    if (isEdit) fd.append('signatory_id', editId);
    fd.append('full_name', nameInput.value.trim());
    fd.append('title', titleInput.value);
    fd.append('division', divisionInput.value);
    fd.append('role_type', document.getElementById('sigRoleType').value);
    fd.append('display_order', 0);
    fd.append('is_default', document.getElementById('sigIsDefault').checked ? 1 : 0);

    const btn = document.getElementById('btnSaveSig');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(API_URL, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          showToast(res.message || 'Saved successfully!', 'success');
          closeModal();
          loadSignatories();
        } else if (res.code === 'DUPLICATE') {
          showToast(res.message, 'warning');
          // Highlight name field as it's likely the cause
          nameInput.classList.add('is-invalid');
          document.getElementById('sigFullNameError').textContent = res.message;
          document.getElementById('sigFullNameError').style.display = 'block';
        } else if (res.code === 'NO_CHANGES') {
          showToast(res.message, 'warning');
          closeModal();
        } else {
          showToast(res.message || 'Save failed', 'danger');
        }
      })
      .catch(() => showToast('Network error!', 'danger'))
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = isEdit ? '<i class="fas fa-save"></i> Update Signatory' : '<i class="fas fa-save"></i> Save Signatory';
      });
  });

  // ========================================
  // EDIT SIGNATORY
  // ========================================
  window.editSignatory = function(id) {
    const sig = allSignatories.find(s => s.signatory_id == id);
    if (!sig) return;

    clearValidation();
    document.getElementById('sigEditId').value = sig.signatory_id;
    document.getElementById('sigFullName').value = sig.full_name;

    // Case-insensitive match for dropdowns
    setSelectCaseInsensitive('sigTitle', sig.title);
    setSelectCaseInsensitive('sigDivision', sig.division);

    document.getElementById('sigRoleType').value = sig.role_type;
    document.getElementById('sigIsDefault').checked = sig.is_default == 1;
    document.getElementById('sigModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Signatory';

    const saveBtn = document.getElementById('btnSaveSig');
    saveBtn.className = 'btn btn-warning';
    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Signatory';

    modal.classList.add('active');
  };

  function setSelectCaseInsensitive(selectId, value) {
    const sel = document.getElementById(selectId);
    const match = Array.from(sel.options).find(opt => opt.value.toLowerCase() === (value || '').toLowerCase());
    sel.value = match ? match.value : '';
  }

  // ========================================
  // DELETE SIGNATORY
  // ========================================
  window.deleteSignatory = function(id, name) {
    deleteSignatoryId = id;
    document.getElementById('deleteSignatoryName').textContent = name;
    new bootstrap.Modal(deleteModal).show();
  };

  document.getElementById('btnConfirmSigDelete').addEventListener('click', () => {
    if (!deleteSignatoryId) return;

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('signatory_id', deleteSignatoryId);

    fetch(API_URL, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          showToast('Signatory deleted!', 'success');
          loadSignatories();
        } else {
          showToast(res.message || 'Delete failed', 'danger');
        }
        const m = bootstrap.Modal.getInstance(deleteModal);
        m.hide();
        deleteSignatoryId = null;
      });
  });

  // ========================================
  // ESCAPE HTML
  // ========================================
  function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }
})();
</script>