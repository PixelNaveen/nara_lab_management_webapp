 <div class="page-manage-variants">
    <div class="variants-container container">
      <!-- Filter + New -->
      <div class="variants-card-filter">
        <input type="text" placeholder="Search by Variant Name" class="form-control" style="max-width:250px;">
        <select class="form-select" id="filterParameter" style="max-width:150px;">
          <option value="">All Parameters</option>
        </select>
        <select class="form-select" style="max-width:120px;">
          <option value="">All Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
        <button class="btn btn-variants-filter">Filter</button>
        <div class="ms-auto">
          <button class="btn-variants-new">+ New Variant</button>
        </div>
      </div>

      <!-- Table -->
      <div class="variants-table-container">
        <table class="variants-table table table-hover align-middle">
          <thead>
            <tr>
              <th>Parameter Name</th>
              <th>Variant Name</th>
              <th>Full Variant Name</th>
              <th>Status</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="variants-modal-overlay" id="variantsModal">
      <div class="variants-modal-form">
        <div class="variants-modal-header">
          <h5 id="variantsModalTitle">New Variant</h5>
          <button class="btn-close-modal">&times;</button>
        </div>
        <form>
          <div class="mb-3">
            <label class="variants-form-label">Parameter</label>
            <select class="variants-form-select" id="variantParameter" required>
              <option value="">Select Parameter</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Variant Name</label>
            <input type="text" class="variants-form-control" id="variantName" placeholder="Enter variant name" required>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Status</label>
            <select class="variants-form-select" id="variantStatus">
              <option >Select Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="variants-modal-footer-btns">
            <button type="button" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="variants-modal-overlay" id="deleteConfirmModal">
      <div class="variants-modal-form">
        <div class="variants-modal-header">
          <h5>Confirm Delete</h5>
          <button class="btn-close-modal">&times;</button>
        </div>
        <div style="padding:24px;">
          <p>Are you sure you want to delete this variant?</p>
          <div class="variants-modal-footer-btns">
            <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
// ===== VARIANT MANAGEMENT SCRIPT (fixed to match your HTML) =====

// === DOM ELEMENTS ===
const variantModalOverlay = document.getElementById('variantsModal');
const variantForm = variantModalOverlay.querySelector('form');
const variantModalTitle = document.getElementById('variantsModalTitle');
const btnCloseVariantModal = variantModalOverlay.querySelector('.btn-close-modal');

const deleteVariantModal = document.getElementById('deleteConfirmModal');
const btnCancelDeleteVariant = document.getElementById('cancelDelete');
const btnConfirmDeleteVariant = document.getElementById('confirmDelete');
const btnCloseDeleteVariantModal = deleteVariantModal.querySelector('.btn-close-modal');

const btnNewVariant = document.querySelector('.btn-variants-new');
const btnFilter = document.querySelector('.btn-variants-filter');
const inputSearch = document.querySelector('.variants-card-filter input[type="text"]');
const selectFilterParameter = document.getElementById('filterParameter');
const selectStatus = document.querySelectorAll('.variants-card-filter select')[1];

const tbody = document.querySelector('.variants-table tbody');
const CONTROLLER_PATH_VARIANT = '../../src/Controllers/variant-controller.php';

// === TOAST (same as parameter manager) ===
function showToastVariant(message, type = 'success') {
  const colors = {
    success: 'bg-success text-white',
    warning: 'bg-warning text-dark',
    danger: 'bg-danger text-white'
  };
  const toastContainer = document.getElementById('variantToastContainer') || document.body;
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

// === AJAX helper ===
function sendAjaxVariant(action, data = {}) {
  return fetch(CONTROLLER_PATH_VARIANT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action, ...data })
  }).then(res => res.json()).catch(() => ({ status: 'error', message: 'Network error!' }));
}

// === OPEN/CLOSE MODALS ===
function openVariantModal(editData = null) {
  variantModalOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';

  variantForm.reset();
  if (editData) {
    variantModalTitle.textContent = 'Edit Variant';
    variantForm.dataset.mode = 'edit';
    variantForm.dataset.variantId = editData.variant_id;
    document.getElementById('variantParameter').value = editData.parameter_id;
    document.getElementById('variantName').value = editData.variant_name;
    document.getElementById('variantStatus').value = editData.is_active == 1 ? 'active' : 'inactive';
  } else {
    variantModalTitle.textContent = 'New Variant';
    variantForm.dataset.mode = 'create';
  }
}

function closeVariantModal() {
  variantModalOverlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

// === DELETE MODAL CONTROL ===
function openDeleteModal(variantId) {
  deleteVariantModal.classList.add('active');
  deleteVariantModal.dataset.id = variantId;
}

function closeDeleteModal() {
  deleteVariantModal.classList.remove('active');
  deleteVariantModal.dataset.id = '';
}

// === LOAD PARAMETERS FOR SELECTS ===
function loadParametersForSelect(selectId) {
  sendAjaxVariant('fetchParams').then(res => {
    if (res.status === 'success' && Array.isArray(res.data)) {
      const select = document.getElementById(selectId);
      select.innerHTML = selectId === 'filterParameter' ? '<option value="">All Parameters</option>' : '<option value="">Select Parameter</option>';
      res.data.forEach(p => {
        select.insertAdjacentHTML('beforeend', `<option value="${p.parameter_id}">${p.parameter_name}</option>`);
      });
    }
  });
}

// === LOAD VARIANTS ===
function loadVariants(filters = {}) {
  sendAjaxVariant('fetchAll', filters).then(res => {
    tbody.innerHTML = '';
    if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
      res.data.forEach(v => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr data-id="${v.variant_id}" data-parameter-id="${v.parameter_id}">
            <td>${v.parameter_name}</td>
            <td>${v.variant_name}</td>
            <td>${v.full_variant_name || '--'}</td>
            <td>
              <span class="badge-status bg-${v.is_active == 1 ? 'success' : 'secondary'}">
                ${v.is_active == 1 ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>
              <button class="btn-variants-edit"><i class="fas fa-edit"></i></button>
              <button class="btn-variants-delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        `);
      });
      attachRowEvents();
    } else {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No variants found</td></tr>`;
    }
  });
}

// === ATTACH EDIT/DELETE EVENTS ===
function attachRowEvents() {
  document.querySelectorAll('.btn-variants-edit').forEach(btn => {
    btn.onclick = e => {
      const row = e.target.closest('tr');
      const data = {
        variant_id: row.dataset.id,
        parameter_id: row.dataset.parameterId,
        variant_name: row.children[1].textContent,
        is_active: row.children[3].querySelector('.badge-status').classList.contains('bg-success') ? 1 : 0
      };
      openVariantModal(data);
    };
  });

  document.querySelectorAll('.btn-variants-delete').forEach(btn => {
    btn.onclick = e => {
      const row = e.target.closest('tr');
      openDeleteModal(row.dataset.id);
    };
  });
}

// === SAVE (INSERT/UPDATE) ===
variantForm.addEventListener('submit', e => {
  e.preventDefault();
  const mode = variantForm.dataset.mode;
  const data = {
    variant_id: variantForm.dataset.variantId || '',
    parameter_id: document.getElementById('variantParameter').value.trim(),
    variant_name: document.getElementById('variantName').value.trim(),
    is_active: document.getElementById('variantStatus').value === 'active' ? 1 : 0
  };

  if (!data.parameter_id || !data.variant_name) {
    showToastVariant('Please fill all required fields', 'warning');
    return;
  }

  const action = mode === 'edit' ? 'update' : 'insert';
  sendAjaxVariant(action, data).then(res => {
    if (res.status === 'success') {
      showToastVariant(res.message || 'Saved successfully!', 'success');
      loadVariants();
      closeVariantModal();
    } else {
      showToastVariant(res.message || 'Failed to save variant', 'danger');
    }
  });
});

variantForm.querySelector('.btn-secondary').addEventListener('click', closeVariantModal);

// === DELETE CONFIRMATION ===
btnConfirmDeleteVariant.onclick = () => {
  const id = deleteVariantModal.dataset.id;
  if (!id) return;
  sendAjaxVariant('delete', { variant_id: id }).then(res => {
    if (res.status === 'success') {
      showToastVariant(res.message || 'Variant deleted', 'danger');
      loadVariants();
    } else {
      showToastVariant(res.message || 'Failed to delete', 'danger');
    }
    closeDeleteModal();
  });
};

btnCancelDeleteVariant.onclick = closeDeleteModal;
btnCloseDeleteVariantModal.onclick = closeDeleteModal;
deleteVariantModal.addEventListener('click', e => {
  if (e.target === deleteVariantModal) closeDeleteModal();
});

// === OPEN/CLOSE CREATE MODAL ===
btnNewVariant.onclick = () => openVariantModal();
btnCloseVariantModal.onclick = closeVariantModal;
variantModalOverlay.addEventListener('click', e => {
  if (e.target === variantModalOverlay) closeVariantModal();
});

// === FILTER (simple status + search) ===
btnFilter.onclick = () => {
  const filters = {};
  if (selectFilterParameter.value) filters.parameter_id = selectFilterParameter.value;
  if (selectStatus.value === 'Active') filters.is_active = 1;
  else if (selectStatus.value === 'Inactive') filters.is_active = 0;
  loadVariants(filters);
};

inputSearch.addEventListener('input', e => {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.variants-table tbody tr').forEach(tr => {
    const name = tr.children[1]?.textContent?.toLowerCase() || '';
    tr.style.display = name.includes(search) ? '' : 'none';
  });
});

// === INITIAL LOAD ===
loadParametersForSelect('variantParameter');
loadParametersForSelect('filterParameter');
loadVariants();

  </script>