<div class="page-swab-parameters">
  <div class="swab-parameters-container container">
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
      <div class="ms-auto">
        <button class="btn-swab-parameters-new" id="btnNewSwab">+ New Swab Param</button>
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
            <td colspan="5" class="text-center">
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
        <input type="hidden" id="csrfToken" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="hidden" id="swabParamId">
        <input type="hidden" id="formMode" value="create">

        <!-- Parameter Dropdown (only for create mode) -->
        <div class="mb-3" id="parameterSelectRow">
          <label class="swab-parameters-form-label">Parameter <span class="text-danger">*</span></label>
          <select class="swab-parameters-form-select" id="parameterSelect">
            <option value="">-- Select Parameter --</option>
          </select>
          <small class="text-muted">Only swab-enabled parameters without swab pricing are shown</small>
        </div>

        <!-- Read-only fields (only for edit mode) -->
        <div class="mb-3" id="parameterNameRow" style="display: none;">
          <label class="swab-parameters-form-label">Parameter Name</label>
          <input type="text" class="swab-parameters-form-control" id="parameterName" readonly style="background-color: #f0f0f0;">
        </div>

        <div class="mb-3" id="parameterCodeRow" style="display: none;">
          <label class="swab-parameters-form-label">Parameter Code</label>
          <input type="text" class="swab-parameters-form-control" id="parameterCode" readonly style="background-color: #f0f0f0;">
        </div>

        <div class="mb-3">
          <label class="swab-parameters-form-label">Price <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="swab-parameters-form-control" id="swabPrice" placeholder="0.00" required>
        </div>

        <div class="mb-3">
          <label class="swab-parameters-form-label">Status</label>
          <select class="swab-parameters-form-select" id="swabStatus">
            <option value="0">Select Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
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
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
  <div id="toastContainer"></div>
</div>

<script>
const CONTROLLER_PATH_SWAB = '../../src/Controllers/swab-controller.php';

// DOM Elements
const swabModalOverlay = document.getElementById('swabParametersModal');
const swabPriceForm = document.getElementById('swabPriceForm');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');
const btnNewSwab = document.getElementById('btnNewSwab');
const btnFilter = document.getElementById('btnFilter');
const btnReset = document.getElementById('btnReset');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const tbody = document.querySelector('.swab-parameters-table tbody');
const deleteConfirmModal = document.getElementById('deleteConfirmModal');
const btnCancelDelete = document.getElementById('cancelDelete');
const btnConfirmDelete = document.getElementById('confirmDelete');
const btnCloseDeleteModal = document.getElementById('btnCloseDeleteModal');

let currentFilters = {};
let deleteSwabId = null;

// === TOAST ===
function showToast(message, type = 'success') {
  const colors = {
    success: 'bg-success text-white',
    warning: 'bg-warning text-dark',
    danger: 'bg-danger text-white',
    info: 'bg-info text-white'
  };
  
  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center ${colors[type]} border-0`;
  toastEl.setAttribute('role', 'alert');
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close ${type === 'warning' ? 'btn-close-black' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  
  document.getElementById('toastContainer').appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// === AJAX ===
async function sendAjax(action, data = {}) {
  try {
    const formData = new URLSearchParams({ action, ...data });
    const response = await fetch(CONTROLLER_PATH_SWAB, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData
    });
    
    const result = await response.json();
    console.log(`Action: ${action}`, result); // Debug logging
    return result;
  } catch (error) {
    console.error('AJAX Error:', error);
    return { status: 'error', message: 'Network error occurred' };
  }
}

// === LOAD PARAMETERS DROPDOWN ===
async function loadParametersDropdown() {
  const result = await sendAjax('fetchDropdown');
  const select = document.getElementById('parameterSelect');
  
  select.innerHTML = '<option value="">-- Select Parameter --</option>';
  
  if (result.status === 'success') {
    if (result.data && result.data.length > 0) {
      result.data.forEach(p => {
        const option = document.createElement('option');
        option.value = p.parameter_id;
        option.textContent = `${p.parameter_name} (${p.parameter_code})`;
        select.appendChild(option);
      });
    } else {
      select.innerHTML = '<option value="">No available parameters</option>';
      showToast('All swab-enabled parameters already have pricing configured', 'info');
    }
  } else {
    console.error('Failed to load dropdown:', result);
    showToast('Failed to load parameters', 'danger');
  }
}

// === LOAD SWAB PRICES ===
async function loadSwabPrices() {
  tbody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';
  
  const result = await sendAjax('fetchAll', currentFilters);
  
  if (result.status === 'success') {
    renderTable(result.data);
  } else {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>';
  }
}

// === RENDER TABLE ===
function renderTable(data) {
  if (!data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No swab parameters found</td></tr>';
    return;
  }

  tbody.innerHTML = data.map(v => `
    <tr data-id="${v.swab_param_id}" data-name="${v.name}">
      <td>${v.name}</td>
      <td>${v.code || '--'}</td>
      <td>${v.price}</td>
      <td>
        <span class="badge-status bg-${v.is_active == 1 ? 'success' : 'secondary'}">
          ${v.is_active == 1 ? 'Active' : 'Inactive'}
        </span>
      </td>
      <td>
        <button class="btn-swab-parameters-edit" data-id="${v.swab_param_id}" title="Edit">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn-swab-parameters-delete" data-id="${v.swab_param_id}" title="Delete">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');

  attachRowEvents();
}

// === ATTACH EVENTS ===
function attachRowEvents() {
  document.querySelectorAll('.btn-swab-parameters-edit').forEach(btn => {
    btn.addEventListener('click', async () => {
      const swabId = btn.dataset.id;
      const result = await sendAjax('getById', { swab_param_id: swabId });
      
      if (result.status === 'success') {
        openEditModal(result.data);
      } else {
        showToast(result.message || 'Failed to load record', 'danger');
      }
    });
  });

  document.querySelectorAll('.btn-swab-parameters-delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const row = e.target.closest('tr');
      const name = row.dataset.name;
      deleteSwabId = btn.dataset.id;
      document.getElementById('deleteParamName').textContent = name;
      deleteConfirmModal.classList.add('active');
    });
  });
}

// === OPEN/CLOSE MODAL ===
function openCreateModal() {
  swabModalOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  swabPriceForm.reset();
  
  document.getElementById('formMode').value = 'create';
  document.getElementById('swabModalTitle').textContent = 'New Swab Parameter';
  document.getElementById('swabParamId').value = '';
  document.getElementById('btnSave').textContent = 'Save';
  
  // Show dropdown, hide read-only fields
  document.getElementById('parameterSelectRow').style.display = 'block';
  document.getElementById('parameterNameRow').style.display = 'none';
  document.getElementById('parameterCodeRow').style.display = 'none';
  
  loadParametersDropdown();
}

function openEditModal(data) {
  swabModalOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  
  document.getElementById('formMode').value = 'edit';
  document.getElementById('swabModalTitle').textContent = 'Edit Swab Parameter';
  document.getElementById('swabParamId').value = data.swab_param_id;
  document.getElementById('swabPrice').value = data.swab_price;
  document.getElementById('swabStatus').value = data.is_active;
  document.getElementById('btnSave').textContent = 'Update';
  
  // Hide dropdown, show read-only fields
  document.getElementById('parameterSelectRow').style.display = 'none';
  document.getElementById('parameterNameRow').style.display = 'block';
  document.getElementById('parameterCodeRow').style.display = 'block';
  document.getElementById('parameterName').value = data.parameter_name;
  document.getElementById('parameterCode').value = data.parameter_code;
}

function closeModal() {
  swabModalOverlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

// === SAVE (INSERT/UPDATE) ===
swabPriceForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const mode = document.getElementById('formMode').value;
  const price = document.getElementById('swabPrice').value.trim();
  
  if (!price || parseFloat(price) < 0) {
    showToast('Please enter a valid price', 'warning');
    return;
  }
  
  const data = {
    csrf_token: document.getElementById('csrfToken').value,
    price: price,
    is_active: document.getElementById('swabStatus').value
  };

  if (mode === 'create') {
    const paramId = document.getElementById('parameterSelect').value;
    
    if (!paramId) {
      showToast('Please select a parameter', 'warning');
      return;
    }
    
    data.param_id = paramId;
  } else {
    data.swab_param_id = document.getElementById('swabParamId').value;
  }

  const action = mode === 'create' ? 'insert' : 'update';
  const result = await sendAjax(action, data);
  
  if (result.status === 'success') {
    showToast(result.message, 'success');
    closeModal();
    loadSwabPrices();
  } else {
    showToast(result.message || 'Failed to save', 'danger');
  }
});

// === DELETE ===
btnConfirmDelete.addEventListener('click', async () => {
  if (!deleteSwabId) return;
  
  const result = await sendAjax('delete', {
    csrf_token: document.getElementById('csrfToken').value,
    swab_param_id: deleteSwabId
  });
  
  if (result.status === 'success') {
    showToast(result.message, 'danger');
    deleteConfirmModal.classList.remove('active');
    loadSwabPrices();
  } else {
    showToast(result.message || 'Failed to delete', 'danger');
  }
  
  deleteSwabId = null;
});

// === FILTER ===
btnFilter.addEventListener('click', () => {
  currentFilters = {
    search: searchInput.value.trim(),
    is_active: statusFilter.value
  };
  loadSwabPrices();
});

btnReset.addEventListener('click', () => {
  searchInput.value = '';
  statusFilter.value = '';
  currentFilters = {};
  loadSwabPrices();
});

searchInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') btnFilter.click();
});

// === MODAL CONTROLS ===
btnNewSwab.addEventListener('click', openCreateModal);
btnCloseModal.addEventListener('click', closeModal);
btnCancel.addEventListener('click', closeModal);
swabModalOverlay.addEventListener('click', (e) => {
  if (e.target === swabModalOverlay) closeModal();
});

btnCancelDelete.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
btnCloseDeleteModal.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
deleteConfirmModal.addEventListener('click', (e) => {
  if (e.target === deleteConfirmModal) deleteConfirmModal.classList.remove('active');
});

// === INITIAL LOAD ===
loadSwabPrices();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>