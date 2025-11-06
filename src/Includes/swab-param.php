
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
          <input type="text" class="swab-parameters-form-control" id="swabParameterName" placeholder="Enter parameter name" required>
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
  // ===== SWAB PARAMETER MANAGEMENT SCRIPT =====

  // === DOM ELEMENTS ===
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
  const CONTROLLER_PATH_SWAB = '../../src/Controllers/swab-controller.php'; // Assume a controller for swab

  // === TOAST ===
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

  // === AJAX helper ===
  function sendAjaxSwab(action, data = {}) {
    return fetch(CONTROLLER_PATH_SWAB, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action, ...data })
    }).then(res => res.json()).catch(() => ({ status: 'error', message: 'Network error!' }));
  }

  // === OPEN/CLOSE MODALS ===
  function openSwabModal(editData = null) {
    swabModalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    swabForm.reset();
    if (editData) {
      swabModalTitle.textContent = 'Edit Parameter';
      swabForm.dataset.mode = 'edit';
      swabForm.dataset.swabId = editData.swab_id;
      document.getElementById('swabParameterName').value = editData.name;
      document.getElementById('swabParameterCode').value = editData.code;
      document.getElementById('swabParameterPrice').value = editData.price;
      document.getElementById('swabParameterStatus').value = editData.is_active == 1 ? 'active' : 'inactive';
    } else {
      swabModalTitle.textContent = 'New Parameter';
      swabForm.dataset.mode = 'create';
    }
  }

  function closeSwabModal() {
    swabModalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  // === DELETE MODAL CONTROL ===
  function openDeleteSwabModal(swabId) {
    deleteSwabModal.classList.add('active');
    deleteSwabModal.dataset.id = swabId;
  }

  function closeDeleteSwabModal() {
    deleteSwabModal.classList.remove('active');
    deleteSwabModal.dataset.id = '';
  }

  // === LOAD SWAB PARAMETERS ===
  function loadSwabParameters(filters = {}) {
    sendAjaxSwab('fetchAll', filters).then(res => {
      tbody.innerHTML = '';
      if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
        res.data.forEach(v => {
          tbody.insertAdjacentHTML('beforeend', `
            <tr data-id="${v.swab_id}">
              <td>${v.name}</td>
              <td>${v.code}</td>
              <td>${v.price || '--'}</td>
              <td>
                <span class="badge-status bg-${v.is_active == 1 ? 'success' : 'secondary'}">
                  ${v.is_active == 1 ? 'Active' : 'Inactive'}
                </span>
              </td>
              <td>
                <button class="btn-swab-parameters-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-swab-parameters-delete"><i class="fas fa-trash"></i></button>
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

  // === ATTACH EDIT/DELETE EVENTS ===
  function attachRowEvents() {
    document.querySelectorAll('.btn-swab-parameters-edit').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        const data = {
          swab_id: row.dataset.id,
          name: row.children[0].textContent,
          code: row.children[1].textContent,
          price: row.children[2].textContent.replace('$', ''),
          is_active: row.children[3].querySelector('.badge-status').classList.contains('bg-success') ? 1 : 0
        };
        openSwabModal(data);
      };
    });

    document.querySelectorAll('.btn-swab-parameters-delete').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        openDeleteSwabModal(row.dataset.id);
      };
    });
  }

  // === SAVE (INSERT/UPDATE) ===
  swabForm.addEventListener('submit', e => {
    e.preventDefault();
    const mode = swabForm.dataset.mode;
    const data = {
      swab_id: swabForm.dataset.swabId || '',
      name: document.getElementById('swabParameterName').value.trim(),
      code: document.getElementById('swabParameterCode').value.trim(),
      price: document.getElementById('swabParameterPrice').value.trim(),
      is_active: document.getElementById('swabParameterStatus').value === 'active' ? 1 : 0
    };

    if (!data.name || !data.price) {
      showToastSwab('Please fill all required fields', 'warning');
      return;
    }

    const action = mode === 'edit' ? 'update' : 'insert';
    sendAjaxSwab(action, data).then(res => {
      if (res.status === 'success') {
        showToastSwab(res.message || 'Saved successfully!', 'success');
        loadSwabParameters();
        closeSwabModal();
      } else {
        showToastSwab(res.message || 'Failed to save parameter', 'danger');
      }
    });
  });

  swabForm.querySelector('.btn-secondary').addEventListener('click', closeSwabModal);

  // === DELETE CONFIRMATION ===
  btnConfirmDeleteSwab.onclick = () => {
    const id = deleteSwabModal.dataset.id;
    if (!id) return;
    sendAjaxSwab('delete', { swab_id: id }).then(res => {
      if (res.status === 'success') {
        showToastSwab(res.message || 'Parameter deleted', 'danger');
        loadSwabParameters();
      } else {
        showToastSwab(res.message || 'Failed to delete', 'danger');
      }
      closeDeleteSwabModal();
    });
  };

  btnCancelDeleteSwab.onclick = closeDeleteSwabModal;
  btnCloseDeleteSwabModal.onclick = closeDeleteSwabModal;
  deleteSwabModal.addEventListener('click', e => {
    if (e.target === deleteSwabModal) closeDeleteSwabModal();
  });

  // === OPEN/CLOSE CREATE MODAL ===
  btnNewSwab.onclick = () => openSwabModal();
  btnCloseSwabModal.onclick = closeSwabModal;
  swabModalOverlay.addEventListener('click', e => {
    if (e.target === swabModalOverlay) closeSwabModal();
  });

  // === FILTER (simple status + search) ===
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

  // === INITIAL LOAD ===
  loadSwabParameters();

</script>