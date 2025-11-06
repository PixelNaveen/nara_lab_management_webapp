<div class="page-swab-parameters">
  <div class="swab-parameters-container container">
    <!-- Filter + New -->
    <div class="swab-parameters-card-filter">
      <input type="text" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
      <select class="form-select" style="max-width:120px;">
        <option value="">All Status</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
      <button class="btn btn-swab-parameters-filter">Filter</button>
      <div class="ms-auto">
        <button class="btn-swab-parameters-new">+ New Parameter</button>
      </div>
    </div>

    <!-- Table -->
    <div class="swab-parameters-table-container">
      <table class="swab-parameters-table table table-hover align-middle">
        <thead>
          <tr>
            <th>Parameter Name</th>
            <th>Parameter Code</th>
            <th>Price</th>
            <th>Status</th>
            <th style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Swab Test A</td>
            <td>STA</td>
            <td>$50</td>
            <td><span class="badge-status bg-success">Active</span></td>
            <td>
              <button class="btn-swab-parameters-edit"><i class="fas fa-edit"></i></button>
              <button class="btn-swab-parameters-delete"><i class="fas fa-trash"></i></button>
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
        <h5 id="swabParametersModalTitle">New Parameter</h5>
        <button class="btn-close-modal">&times;</button>
      </div>
      <form>
        <div class="mb-3">
          <label class="swab-parameters-form-label">Parameter Name</label>
          <select id="swabParameterSelect" class="swab-parameters-form-control" required>
            <option value="">-- Select Parameter --</option>
            <!-- Options loaded by JS -->
          </select>
        </div>
        <!-- <div class="mb-3">
          <label class="swab-parameters-form-label">Parameter Code</label>
          <input type="text" class="swab-parameters-form-control" id="swabParameterCode" placeholder="Enter parameter code">
        </div> -->
        <div class="mb-3">
          <label class="swab-parameters-form-label">Price</label>
          <input type="text" class="swab-parameters-form-control" id="swabParameterPrice" placeholder="Enter price">
        </div>
        <div class="mb-3">
          <label class="swab-parameters-form-label">Status</label>
          <select class="swab-parameters-form-select" id="swabParameterStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="swab-parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="swab-parameters-modal-overlay" id="swabDeleteConfirmModal">
    <div class="swab-parameters-modal-form">
      <div class="swab-parameters-modal-header">
        <h5>Confirm Delete</h5>
        <button class="btn-close-modal">&times;</button>
      </div>
      <div style="padding:24px;">
        <p>Are you sure you want to delete this parameter?</p>
        <div class="swab-parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="swabCancelDelete">Cancel</button>
          <button type="button" class="btn btn-danger" id="swabConfirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
 // ===== swab.js =====
// Matches your UI code style and controller API

const CONTROLLER_PATH_SWAB = '../../src/Controllers/swab-controller.php';

// DOM
const swabModalOverlay = document.getElementById('swabParametersModal');
const swabForm = swabModalOverlay.querySelector('form');
const swabModalTitle = document.getElementById('swabParametersModalTitle');
const btnCloseSwabModal = swabModalOverlay.querySelector('.btn-close-modal');

const deleteSwabModal = document.getElementById('swabDeleteConfirmModal');
const btnCancelDeleteSwab = document.getElementById('swabCancelDelete');
const btnConfirmDeleteSwab = document.getElementById('swabConfirmDelete');
const btnCloseDeleteSwabModal = deleteSwabModal.querySelector('.btn-close-modal');

const btnNewSwab = document.querySelector('.btn-swab-parameters-new');
const btnFilter = document.querySelector('.btn-swab-parameters-filter');
const inputSearch = document.querySelector('.swab-parameters-card-filter input[type="text"]');
const selectStatus = document.querySelectorAll('.swab-parameters-card-filter select')[0];

const tbody = document.querySelector('.swab-parameters-table tbody');

const swabParameterSelect = document.getElementById('swabParameterSelect'); // select dropdown
const swabParameterPrice = document.getElementById('swabParameterPrice');
const swabParameterStatus = document.getElementById('swabParameterStatus');

// Toast
function showToastSwab(message, type = 'success') {
  const colors = {
    success: 'bg-success text-white',
    warning: 'bg-warning text-dark',
    danger: 'bg-danger text-white'
  };
  const toastContainer = document.getElementById('swabToastContainer') || document.body;
  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center ${colors[type]} border-0 position-fixed bottom-0 end-0 m-3`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto"></button>
    </div>`;
  toastContainer.appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// AJAX helper
function sendAjaxSwab(action, data = {}) {
  return fetch(CONTROLLER_PATH_SWAB, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action, ...data })
  }).then(res => res.json()).catch(() => ({ status: 'error', message: 'Network error!' }));
}

// Open/Close modal
function openSwabModal(editData = null) {
  swabModalOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  swabForm.reset();

  // Ensure dropdown options are loaded
  loadParameterDropdown();

  if (editData) {
    swabModalTitle.textContent = 'Edit Parameter';
    swabForm.dataset.mode = 'edit';
    swabForm.dataset.swabId = editData.swab_param_id || editData.swab_id || '';
    // set selected param in dropdown (param_id may be provided)
    if (editData.param_id) {
      // wait if dropdown not loaded yet - loadParameterDropdown ensures it's loaded
      setTimeout(() => {
        swabParameterSelect.value = editData.param_id;
      }, 150);
    }
    swabParameterPrice.value = editData.price || '';
    swabParameterStatus.value = editData.is_active == 1 ? 'active' : 'inactive';
    // When editing, disable changing parameter selection (optional). If you want to allow change, remove next line.
    swabParameterSelect.disabled = true;
  } else {
    swabModalTitle.textContent = 'New Parameter';
    swabForm.dataset.mode = 'create';
    swabForm.dataset.swabId = '';
    swabParameterSelect.disabled = false;
  }
}

function closeSwabModal() {
  swabModalOverlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

// Delete modal control
function openDeleteSwabModal(swabId) {
  deleteSwabModal.classList.add('active');
  deleteSwabModal.dataset.id = swabId;
}

function closeDeleteSwabModal() {
  deleteSwabModal.classList.remove('active');
  deleteSwabModal.dataset.id = '';
}

// Load table
function loadSwabParameters(filters = {}) {
  sendAjaxSwab('fetchAll', filters).then(res => {
    tbody.innerHTML = '';
    if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
      res.data.forEach(v => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr data-id="${v.swab_param_id}">
            <td>${v.name}</td>
            <td>${v.code || '--'}</td>
            <td>${v.price ? ( v.price) : '--'}</td>
            <td>
              <span class="badge-status bg-${v.is_active == 1 ? 'success' : 'secondary'}">
                ${v.is_active == 1 ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>
              <button class="btn-swab-parameters-edit" data-id="${v.swab_param_id}"><i class="fas fa-edit"></i></button>
              <button class="btn-swab-parameters-delete" data-id="${v.swab_param_id}"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        `);
      });
      attachRowEvents();
    } else {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No parameters found</td></tr>`;
    }
  });
}

// Attach edit/delete events
function attachRowEvents() {
  document.querySelectorAll('.btn-swab-parameters-edit').forEach(btn => {
    btn.onclick = async (e) => {
      const swabId = btn.dataset.id;
      const res = await sendAjaxSwab('getById', { swab_param_id: swabId });
      if (res.status === 'success') {
        openSwabModal(res.data);
      } else {
        showToastSwab(res.message || 'Failed to load record', 'danger');
      }
    };
  });

  document.querySelectorAll('.btn-swab-parameters-delete').forEach(btn => {
    btn.onclick = (e) => {
      const row = e.target.closest('tr');
      openDeleteSwabModal(row.dataset.id);
    };
  });
}

// Save (insert/update)
swabForm.addEventListener('submit', e => {
  e.preventDefault();
  const mode = swabForm.dataset.mode;
  const swabId = swabForm.dataset.swabId || '';
  const paramId = swabParameterSelect.value;
  const priceVal = swabParameterPrice.value.trim();
  const isActive = swabParameterStatus.value === 'active' ? 1 : 0;

  if (!paramId) {
    showToastSwab('Please select a parameter', 'warning');
    return;
  }

  if (priceVal === '') {
    showToastSwab('Please enter price (or 0.00)', 'warning');
    return;
  }

  const data = {
    csrf_token: document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '',
    price: priceVal,
    is_active: isActive
  };

  if (mode === 'edit') {
    data.swab_param_id = swabId;
    // update
    sendAjaxSwab('update', data).then(res => {
      if (res.status === 'success') {
        showToastSwab(res.message || 'Updated', 'success');
        loadSwabParameters();
        closeSwabModal();
      } else {
        showToastSwab(res.message || 'Update failed', 'danger');
      }
    });
  } else {
    // create
    data.param_id = paramId;
    sendAjaxSwab('insert', data).then(res => {
      if (res.status === 'success') {
        showToastSwab(res.message || 'Inserted', 'success');
        loadSwabParameters();
        closeSwabModal();
      } else {
        showToastSwab(res.message || 'Insert failed', 'danger');
      }
    });
  }
});

// Delete confirmation
btnConfirmDeleteSwab.onclick = () => {
  const id = deleteSwabModal.dataset.id;
  if (!id) return;
  sendAjaxSwab('delete', { csrf_token: document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '', swab_param_id: id })
    .then(res => {
      if (res.status === 'success') {
        showToastSwab(res.message || 'Deleted', 'danger');
        loadSwabParameters();
      } else {
        showToastSwab(res.message || 'Delete failed', 'danger');
      }
      closeDeleteSwabModal();
    });
};

// Cancel buttons / modal controls
btnCancelDeleteSwab.onclick = closeDeleteSwabModal;
btnCloseDeleteSwabModal.onclick = closeDeleteSwabModal;
deleteSwabModal.addEventListener('click', e => { if (e.target === deleteSwabModal) closeDeleteSwabModal(); });

btnNewSwab.onclick = () => openSwabModal();
btnCloseSwabModal.onclick = closeSwabModal;
swabModalOverlay.addEventListener('click', e => { if (e.target === swabModalOverlay) closeSwabModal(); });

// Filter
btnFilter.onclick = () => {
  const filters = {};
  if (selectStatus.value === 'Active') filters.is_active = 1;
  else if (selectStatus.value === 'Inactive') filters.is_active = 0;
  loadSwabParameters(filters);
};

inputSearch.addEventListener('input', e => {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.swab-parameters-table tbody tr').forEach(tr => {
    const name = tr.children[0]?.textContent?.toLowerCase() || '';
    tr.style.display = name.includes(search) ? '' : 'none';
  });
});

// Load parameters dropdown for selection
let _dropdownLoaded = false;
function loadParameterDropdown(force = false) {
  if (_dropdownLoaded && !force) return Promise.resolve();
  // clear existing
  if (!swabParameterSelect) return Promise.resolve();
  swabParameterSelect.innerHTML = '<option value="">-- Select Parameter --</option>';
  return sendAjaxSwab('fetchDropdown').then(res => {
    if (res.status === 'success' && Array.isArray(res.data)) {
      res.data.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.parameter_id;
        opt.textContent = `${p.parameter_name} (${p.parameter_code || ''})`;
        swabParameterSelect.appendChild(opt);
      });
      _dropdownLoaded = true;
    } else {
      showToastSwab(res.message || 'Failed to load parameters', 'danger');
    }
  });
}

// Initial load
loadParameterDropdown().then(() => loadSwabParameters());

</script>