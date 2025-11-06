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
          <!-- Data will be loaded via AJAX -->
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
        <input type="hidden" id="parameterId">

        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="parameters-form-label">Parameter Name <span class="text-danger">*</span></label>
            <input type="text" class="parameters-form-control" id="paramName" placeholder="Enter parameter name" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Parameter Code</label>
            <input type="text" class="parameters-form-control" id="paramCode" placeholder="Auto-generated (A-Z)" readonly style="background-color: #f0f0f0;">
            <small class="text-muted">Code is automatically assigned</small>
          </div>
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Category</label>
            <input type="text" class="parameters-form-control" id="paramCategory" placeholder="Enter category (optional)">
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
            <select class="parameters-form-select" id="paramSwab">
              <option>Select Status</option>
              <option value="1">Enabled</option>
              <option value="0">Disabled</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="parameters-form-label">Status</label>
            <select class="parameters-form-select" id="paramStatus">
              <option>Select Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <div class="parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
          <button type="submit" class="btn btn-success" id="btnSave">Save Parameter</button>
          <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Parameter</button>
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
  // ===== PARAMETER MANAGEMENT SCRIPT =====

  // === DOM ELEMENTS ===
  const modalOverlay = document.getElementById('parametersModal');
  const parameterForm = document.getElementById('parameterForm');
  const btnNewParam = document.getElementById('btnNewParam');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const modalTitle = document.getElementById('parametersModalTitle');

  const deleteConfirmModal = document.getElementById('deleteConfirmModal');
  const btnCancelDelete = document.getElementById('cancelDelete');
  const btnConfirmDelete = document.getElementById('confirmDelete');
  const btnCloseDeleteModal = document.getElementById('btnCloseDeleteModal');

  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const btnFilter = document.getElementById('btnFilter');
  const toastContainer = document.getElementById('toastContainer');

  let deleteParamId = null;
  let originalData = {};

  const CONTROLLER_PATH = '../../src/Controllers/parameter-controller.php';

  // === TOAST FUNCTION ===
  function showToast(message, type = 'success') {
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
    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, {
      delay: 2500
    });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // === AJAX HELPER ===
  function sendAjax(action, data) {
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

 // === LOAD PARAMETERS ===
function loadParameters(filters = {}) {
  sendAjax('fetchAll', filters).then(res => {
    const tbody = document.querySelector('#parametersTable tbody');
    tbody.innerHTML = '';

    if (res.status === 'success' && Array.isArray(res.data)) {
      res.data.forEach(param => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr data-id="${param.parameter_id}"
              data-code="${param.parameter_code}"
              data-name="${param.parameter_name}"
              data-category="${param.parameter_category || ''}"
              data-unit="${param.base_unit || ''}"
              data-swab="${param.swab_enabled}"
              data-active="${param.is_active}">
            <td>${param.parameter_name}</td>
            <td>${param.parameter_code}</td>
            <td>${param.parameter_category || '--'}</td>
            <td>${param.base_unit || '--'}</td>
            <td>
              <span class="badge bg-${param.swab_enabled == "1" ? 'info' : 'danger'}">
                ${param.swab_enabled == "1" ? 'Yes' : 'No'}
              </span>
            </td>
            <td>${param.variant_count || 0}</td>
            <td>
              <span class="badge-status bg-${param.is_active == "1" ? 'success' : 'secondary'}">
                ${param.is_active == "1" ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>
              <button class="btn-parameters-edit"><i class="fas fa-edit"></i></button>
              <button class="btn-parameters-delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        `);
      });
      attachRowEvents();
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No parameters found</td></tr>`;
    }
  });
}

  // === MODAL CONTROL ===
  function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (mode === 'create') {
      parameterForm.reset();
      document.getElementById('parameterId').value = '';
      document.getElementById('paramCode').value = 'Auto-assigned';
      
      btnSave.classList.remove('d-none');
      btnUpdate.classList.add('d-none');
      modalTitle.textContent = 'New Parameter';
    } else {
      document.getElementById('paramStatus').disabled = false;
      btnSave.classList.add('d-none');
      btnUpdate.classList.remove('d-none');
      modalTitle.textContent = 'Update Parameter';
    }
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    parameterForm.reset();
    originalData = {};
  }

  btnNewParam.onclick = () => openModal('create');
  btnCloseModal.onclick = closeModal;
  btnCancel.onclick = closeModal;
  modalOverlay.onclick = e => {
    if (e.target === modalOverlay) closeModal();
  };

  // === INSERT PARAMETER ===
  parameterForm.addEventListener('submit', e => {
    e.preventDefault();

    const data = {
      parameter_name: document.getElementById('paramName').value.trim(),
      parameter_category: document.getElementById('paramCategory').value.trim(),
      base_unit: document.getElementById('paramBaseUnit').value.trim(),
      swab_enabled: document.getElementById('paramSwab').value
    };

    if (!data.parameter_name) {
      showToast('Parameter name is required', 'warning');
      return;
    }

    sendAjax('insert', data).then(res => {
      if (res.status === 'success') {
        showToast(res.message || 'Parameter created successfully! Code auto-assigned.', 'success');
        loadParameters();
        closeModal();
      } else {
        showToast(res.message || 'Failed to create parameter', 'danger');
      }
    });
  });

  // === ATTACH EDIT & DELETE EVENTS ===
  function attachRowEvents() {
    document.querySelectorAll('.btn-parameters-edit').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        openModal('edit');

        document.getElementById('parameterId').value = row.dataset.id;
        document.getElementById('paramCode').value = row.dataset.code;
        document.getElementById('paramName').value = row.dataset.name;
        document.getElementById('paramCategory').value = row.dataset.category;
        document.getElementById('paramBaseUnit').value = row.dataset.unit;
        document.getElementById('paramSwab').value = row.dataset.swab;
        document.getElementById('paramStatus').value = row.dataset.active;

        originalData = {
          parameter_code: row.dataset.code,
          parameter_name: row.dataset.name,
          parameter_category: row.dataset.category,
          base_unit: row.dataset.unit,
          swab_enabled: row.dataset.swab,
          is_active: row.dataset.active
        };
      };
    });

    document.querySelectorAll('.btn-parameters-delete').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        deleteParamId = row.dataset.id;
        document.getElementById('deleteParamName').textContent = row.dataset.name;
        deleteConfirmModal.classList.add('active');
      };
    });
  }

  // === DELETE PARAMETER ===
  btnConfirmDelete.onclick = () => {
    if (!deleteParamId) return;
    sendAjax('delete', {
      parameter_id: deleteParamId
    }).then(res => {
      if (res.status === 'success') {
        showToast('Parameter deleted successfully!', 'danger');
        loadParameters();
      } else if (res.status === 'warning') {
        showToast(res.message, 'warning');
      } else {
        showToast(res.message || 'Failed to delete parameter', 'danger');
      }
      deleteConfirmModal.classList.remove('active');
      deleteParamId = null;
    });
  };

  btnCancelDelete.onclick = () => deleteConfirmModal.classList.remove('active');
  btnCloseDeleteModal.onclick = () => deleteConfirmModal.classList.remove('active');
  deleteConfirmModal.onclick = e => {
    if (e.target === deleteConfirmModal) deleteConfirmModal.classList.remove('active');
  };

  // === UPDATE PARAMETER ===
  btnUpdate.onclick = () => {
    const id = document.getElementById('parameterId').value;
    const data = {
      parameter_id: id,
      parameter_code: document.getElementById('paramCode').value, // Read-only, just send back
      parameter_name: document.getElementById('paramName').value.trim(),
      parameter_category: document.getElementById('paramCategory').value.trim(),
      base_unit: document.getElementById('paramBaseUnit').value.trim(),
      swab_enabled: document.getElementById('paramSwab').value,
      is_active: document.getElementById('paramStatus').value
    };

    // Check if anything changed
    const changed = Object.keys(data).some(key => {
      if (key === 'parameter_id' || key === 'parameter_code') return false;
      return data[key] != originalData[key];
    });

    if (!changed) {
      showToast('No changes detected', 'warning');
      return;
    }

    if (!data.parameter_name) {
      showToast('Parameter name is required', 'warning');
      return;
    }

    sendAjax('update', data).then(res => {
      if (res.status === 'success') {
        showToast('Parameter updated successfully!', 'success');
        loadParameters();
        closeModal();
      } else {
        showToast(res.message || 'Update failed', 'danger');
      }
    });
  };

  // === SEARCH FILTER (Client-side) ===
  searchInput.addEventListener('input', e => {
    const search = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#parametersTable tbody tr');
    let visibleCount = 0;

    rows.forEach(tr => {
      if (!tr.dataset.name) return;
      const name = tr.dataset.name.toLowerCase();
      const code = tr.dataset.code.toLowerCase();
      if (name.includes(search) || code.includes(search)) {
        tr.style.display = '';
        visibleCount++;
      } else {
        tr.style.display = 'none';
      }
    });

    const noResultsRow = document.querySelector('#parametersTable tbody tr.no-results');
    if (visibleCount === 0 && rows.length > 0) {
      if (!noResultsRow) {
        document.querySelector('#parametersTable tbody').insertAdjacentHTML(
          'beforeend',
          `<tr class="no-results"><td colspan="8" class="text-center text-muted">No matching parameters found</td></tr>`
        );
      }
    } else if (noResultsRow) {
      noResultsRow.remove();
    }
  });

  // === STATUS FILTER (Server-side) ===
  btnFilter.addEventListener('click', () => {
    const filters = {};
    if (statusFilter.value !== '') {
      filters.is_active = statusFilter.value;
    }
    loadParameters(filters);
  });

  // === INITIAL LOAD ===
  loadParameters();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>