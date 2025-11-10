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
            <th style="width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8" class="text-center">
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
    <label class="parameters-form-label">Method</label>
    <select class="parameters-form-select" id="paramMethod">
      <option value="">Select Method</option>
    </select>
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

<script>
const CONTROLLER_PATH = '../../src/Controllers/parameter-controller.php';

// DOM Elements
const modalOverlay = document.getElementById('parametersModal');
const parameterForm = document.getElementById('parameterForm');
const btnNewParam = document.getElementById('btnNewParam');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');
const deleteConfirmModal = document.getElementById('deleteConfirmModal');
const btnCancelDelete = document.getElementById('cancelDelete');
const btnConfirmDelete = document.getElementById('confirmDelete');
const btnCloseDeleteModal = document.getElementById('btnCloseDeleteModal');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const btnFilter = document.getElementById('btnFilter');
const btnReset = document.getElementById('btnReset');
const paramSwab = document.getElementById('paramSwab');
const swabPriceRow = document.getElementById('swabPriceRow');
const tbody = document.querySelector('#parametersTable tbody');

let deleteParamId = null;
let currentPage = 1;
let currentFilters = {};

// === SWAB PRICE VISIBILITY ===
paramSwab.addEventListener('change', () => {
  const mode = document.getElementById('formMode').value;
  // Only show price field when creating AND swab is enabled
  if (mode === 'create' && paramSwab.value === '1') {
    swabPriceRow.style.display = 'block';
  } else {
    swabPriceRow.style.display = 'none';
  }
});

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
    const response = await fetch(CONTROLLER_PATH, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData
    });
    return await response.json();
  } catch (error) {
    console.error('AJAX Error:', error);
    return { status: 'error', message: 'Network error occurred' };
  }
}

// === LOAD ACTIVE TEST METHODS ===
async function loadActiveTestMethods() {
  const result = await sendAjax('fetchActiveMethods', {});
  const methodSelect = document.getElementById('paramMethod');
  
  methodSelect.innerHTML = '<option value="">Select Method</option>';
  
  if (result.status === 'success' && Array.isArray(result.data)) {
    result.data.forEach(method => {
      const displayText = method.standard_body 
        ? `${method.method_name} (${method.standard_body})`
        : method.method_name;
      
      methodSelect.insertAdjacentHTML('beforeend', 
        `<option value="${method.method_id}">${displayText}</option>`
      );
    });
  }
}

// === LOAD PARAMETERS ===
async function loadParameters(page = 1) {
  currentPage = page;
  const filters = { ...currentFilters, page, limit: 50 };
  
  tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';
  
  const result = await sendAjax('fetchAll', filters);
  
  if (result.status === 'success') {
    renderTable(result.data);
  } else {
    showToast(result.message || 'Failed to load parameters', 'danger');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
  }
}

// === RENDER TABLE ===
function renderTable(data) {
  if (!data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No parameters found</td></tr>';
    return;
  }

  tbody.innerHTML = data.map(p => `
    <tr data-id="${p.parameter_id}">
      <td>${p.parameter_name}</td>
      <td>${p.parameter_code}</td>
      <td>${p.parameter_category || '<em class="text-muted">--</em>'}</td>
      <td>${p.base_unit || '<em class="text-muted">--</em>'}</td>
      <td>
        <span class="badge-status bg-${p.swab_enabled == 1 ? 'success' : 'secondary'}">
          ${p.swab_enabled == 1 ? 'Enabled' : 'Disabled'}
        </span>
      </td>
      <td>${p.variant_count || 0}</td>
      <td>
        <span class="badge-status bg-${p.is_active == 1 ? 'success' : 'secondary'}">
          ${p.is_active == 1 ? 'Active' : 'Inactive'}
        </span>
      </td>
      <td>
        <button class="btn-parameters-edit" data-id="${p.parameter_id}" title="Edit">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn-parameters-delete" data-id="${p.parameter_id}" title="Delete">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');

  attachRowEvents();
}

// === OPEN/CLOSE MODAL ===
async function openModal(mode = 'create') {
  modalOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  parameterForm.reset();
  document.getElementById('formMode').value = mode;
  document.getElementById('parametersModalTitle').textContent = 
    mode === 'edit' ? 'Edit Parameter' : 'New Parameter';
  document.getElementById('parameterId').value = '';
  document.getElementById('paramCode').value = '';
  swabPriceRow.style.display = 'none';
  
  // Load active test methods when opening modal
  await loadActiveTestMethods();
}

function closeModal() {
  modalOverlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

// === EDIT PARAMETER ===
async function editParameter(id) {
  const result = await sendAjax('getById', { parameter_id: id });
  
  if (result.status === 'success') {
    const data = result.data;
    await openModal('edit');
    
    document.getElementById('parameterId').value = data.parameter_id;
    document.getElementById('paramCode').value = data.parameter_code;
    document.getElementById('paramName').value = data.parameter_name;
    document.getElementById('paramCategory').value = data.parameter_category || '';
    document.getElementById('paramBaseUnit').value = data.base_unit || '';
    document.getElementById('paramSwab').value = data.swab_enabled;
    document.getElementById('paramStatus').value = data.is_active;
    
    // Set method_id after methods are loaded
    setTimeout(() => {
      document.getElementById('paramMethod').value = data.method_id || '';
    }, 100);
    
    // Note: Swab price editing is handled in separate Swab Prices page
    swabPriceRow.style.display = 'none';
  } else {
    showToast(result.message || 'Failed to load parameter', 'danger');
  }
}

// === ATTACH EVENTS ===
function attachRowEvents() {
  document.querySelectorAll('.btn-parameters-edit').forEach(btn => {
    btn.addEventListener('click', () => editParameter(btn.dataset.id));
  });

  document.querySelectorAll('.btn-parameters-delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const row = e.target.closest('tr');
      const name = row.children[0].textContent;
      deleteParamId = btn.dataset.id;
      document.getElementById('deleteParamName').textContent = name;
      deleteConfirmModal.classList.add('active');
    });
  });
}

// === SAVE PARAMETER ===
parameterForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const mode = document.getElementById('formMode').value;
  const id = document.getElementById('parameterId').value;
  
  const data = {
    csrf_token: document.getElementById('csrfToken').value,
    parameter_name: document.getElementById('paramName').value.trim(),
    method_id: document.getElementById('paramMethod').value, // Include method_id
    parameter_category: document.getElementById('paramCategory').value.trim(),
    base_unit: document.getElementById('paramBaseUnit').value.trim(),
    swab_enabled: document.getElementById('paramSwab').value,
    is_active: document.getElementById('paramStatus').value
  };

  if (!data.parameter_name) {
    showToast('Parameter name is required', 'warning');
    return;
  }

  // Include initial swab price only when creating AND swab is enabled
  if (mode === 'create' && data.swab_enabled === '1') {
    const priceValue = document.getElementById('paramSwabPrice').value.trim();
    data.swab_price = priceValue !== '' ? priceValue : '0.00';
  }

  if (mode === 'edit') {
    data.parameter_id = id;
    data.parameter_code = document.getElementById('paramCode').value;
  }

  const action = mode === 'edit' ? 'update' : 'insert';
  const result = await sendAjax(action, data);
  
  if (result.status === 'success') {
    showToast(result.message, 'success');
    closeModal();
    loadParameters(currentPage);
  } else {
    showToast(result.message || 'Failed to save parameter', 'danger');
  }
});

// === DELETE ===
btnConfirmDelete.addEventListener('click', async () => {
  if (!deleteParamId) return;
  
  const result = await sendAjax('delete', {
    csrf_token: document.getElementById('csrfToken').value,
    parameter_id: deleteParamId
  });
  
  if (result.status === 'success') {
    showToast(result.message, 'success');
    deleteConfirmModal.classList.remove('active');
    loadParameters(currentPage);
  } else if (result.status === 'warning') {
    showToast(result.message, 'warning');
  } else {
    showToast(result.message || 'Failed to delete parameter', 'danger');
  }
  
  deleteParamId = null;
});

// === FILTER ===
btnFilter.addEventListener('click', () => {
  currentFilters = {
    search: searchInput.value.trim(),
    is_active: statusFilter.value
  };
  loadParameters(1);
});

btnReset.addEventListener('click', () => {
  searchInput.value = '';
  statusFilter.value = '';
  currentFilters = {};
  loadParameters(1);
});

searchInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') btnFilter.click();
});

// === MODAL CONTROLS ===
btnNewParam.addEventListener('click', () => openModal('create'));
btnCloseModal.addEventListener('click', closeModal);
btnCancel.addEventListener('click', closeModal);
modalOverlay.addEventListener('click', (e) => {
  if (e.target === modalOverlay) closeModal();
});

btnCancelDelete.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
btnCloseDeleteModal.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
deleteConfirmModal.addEventListener('click', (e) => {
  if (e.target === deleteConfirmModal) deleteConfirmModal.classList.remove('active');
});

// === INITIAL LOAD ===
loadParameters(1);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>