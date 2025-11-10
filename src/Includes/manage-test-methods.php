<div class="container">

  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <input type="text" class="form-control" id="searchInput" placeholder="Search by method name or standard body" style="max-width: 250px;" />

    <select class="form-select" id="standardBodyFilter" style="max-width: 160px;">
      <option>All Standard Bodies</option>
      <option>ISO</option>
      <option>SLS</option>
    </select>

    <select class="form-select" id="statusFilter" style="max-width: 120px;">
        <option value="All Status">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

    <button id="btnFilter" class="btn btn-outline-secondary btn-sm" style="min-width: 80px;">Filter</button> 

    <div class="ms-auto">
      <button class="btn btn-primary btn-sm" id="btnNewTestMethod">+ New Test Method</button>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-12">
      <div class="table-container">
        <table class="table table-hover align-middle testMethodsTable" id="testMethodsTable">
          <thead>
            <tr>
              <th class="d-none">ID</th>
              <th>Method Name</th>
              <th>Standard Body</th>
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

<!-- Test Method Modal -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-form">
    <div class="modal-header">
      <h5 id="formTitle">Create New Test Method</h5>
      <button class="btn-close-modal" id="btnCloseModal"><i class="fas fa-times"></i></button>
    </div>

    <form id="testMethodForm" method="post">
      <input type="hidden" id="testMethodId">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Method Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="methodName" placeholder="Enter method name" name="methodName" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Standard Body <span class="text-danger">*</span></label>
          <select class="form-select" id="standardBody" name="standardBody" required>
            <option value="">Select Standard Body</option>
            <option value="ISO">ISO</option>
            <option value="SLS">SLS</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Status <span class="text-danger">*</span></label>
          <select class="form-select" id="status" name="status" required>
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="modal-footer-btns">
        <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
        <button type="submit" class="btn btn-success" id="btnSave">Save Test Method</button>
        <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Test Method</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <span id="deleteTestMethodName"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
  <div id="toastContainer"></div>
</div>

<script>
  // ===== TEST METHOD MANAGEMENT SCRIPT =====

  // === DOM ELEMENTS ===
  const modalOverlay = document.getElementById('modalOverlay');
  const testMethodForm = document.getElementById('testMethodForm');
  const btnNewTestMethod = document.getElementById('btnNewTestMethod');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const formTitle = document.getElementById('formTitle');
  const deleteModal = document.getElementById('deleteModal');
  const toastContainer = document.getElementById('toastContainer');
  const searchInput = document.getElementById('searchInput');
  const standardBodyFilter = document.getElementById('standardBodyFilter');
  const statusFilter = document.getElementById('statusFilter');
  const btnFilter = document.getElementById('btnFilter');
  let deleteTestMethodId = null;
  let originalData = {};

const CONTROLLER_PATH = '/src/Controllers/test-method-controller.php';

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

  // === LOAD TEST METHODS ===
  function loadTestMethods() {
    sendAjax('fetchAll', {}).then(res => {
      const tbody = document.querySelector('#testMethodsTable tbody');
      tbody.innerHTML = '';

      if (res.status === 'success' && Array.isArray(res.data)) {
        res.data.forEach(testMethod => {
  const statusBadge = testMethod.status === 'active'
    ? '<span class="badge bg-success">Active</span>'
    : '<span class="badge bg-secondary">Inactive</span>';

  tbody.insertAdjacentHTML('beforeend', `
<tr data-id="${testMethod.method_id}"
    data-name="${testMethod.method_name}"
    data-standard-body="${testMethod.standard_body}"
    data-status="${testMethod.status}">
  <td class="d-none">${testMethod.method_id}</td>
  <td>${testMethod.method_name}</td>
  <td>${testMethod.standard_body}</td>
  <td>${statusBadge}</td>
  <td>
    <button class="btn btn-sm btn-warning btn-edit"><i class="fas fa-edit"></i></button>
    <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
  </td>
</tr>
  `);
});

        attachRowEvents();
        filterTable();  // Apply filters after loading
      } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No test methods found</td></tr>`;
      }
    });
  }

  // === MODAL CONTROL ===
  function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (mode === 'create') {
      testMethodForm.reset();
      document.getElementById('testMethodId').value = '';
      btnSave.classList.remove('d-none');
      btnUpdate.classList.add('d-none');
      formTitle.textContent = 'Create New Test Method';
    } else {
      btnSave.classList.add('d-none');
      btnUpdate.classList.remove('d-none');
      formTitle.textContent = 'Update Test Method';
    }
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    testMethodForm.reset();
    originalData = {};
  }

  btnNewTestMethod.onclick = () => openModal('create');
  btnCloseModal.onclick = closeModal;
  btnCancel.onclick = closeModal;
  modalOverlay.onclick = e => {
    if (e.target === modalOverlay) closeModal();
  };

  // === INSERT TEST METHOD ===
  testMethodForm.addEventListener('submit', e => {
    e.preventDefault();

    const data = {
      method_name: testMethodForm.methodName.value.trim(),
      standard_body: testMethodForm.standardBody.value,
      status: testMethodForm.status.value
    };

    sendAjax('insert', data).then(res => {
      if (res.status === 'success') {
        showToast(res.message || 'Test method created successfully!', 'success');
        loadTestMethods();
        closeModal();
      } else {
        showToast(res.message || 'Failed to create test method', 'danger');
        closeModal();
      }
    });
  });

  // === ATTACH EDIT & DELETE EVENTS ===
  function attachRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        openModal('edit');
        document.getElementById('testMethodId').value = row.dataset.id;
        testMethodForm.methodName.value = row.dataset.name;
        testMethodForm.standardBody.value = row.dataset.standardBody;
        testMethodForm.status.value = row.dataset.status;

        originalData = {
          method_name: row.dataset.name,
          standard_body: row.dataset.standardBody,
          status: row.dataset.status
        };
      };
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        deleteTestMethodId = row.dataset.id;
        document.getElementById('deleteTestMethodName').textContent = row.dataset.name;
        new bootstrap.Modal(deleteModal).show();
      };
    });
  }

  // === DELETE TEST METHOD ===
  document.getElementById('confirmDeleteBtn').onclick = () => {
    if (!deleteTestMethodId) return;
    sendAjax('delete', {
      method_id: deleteTestMethodId
    }).then(res => {
      if (res.status === 'success') {
        showToast('Test method deleted successfully!', 'danger');
        loadTestMethods();
      } else {
        showToast(res.message || 'Failed to delete test method', 'danger');
      }
      const modal = bootstrap.Modal.getInstance(deleteModal);
      modal.hide();
      deleteTestMethodId = null;
    });
  };

  // === UPDATE TEST METHOD ===
  btnUpdate.onclick = () => {
    const id = document.getElementById('testMethodId').value;
    const data = {
      method_id: id,
      method_name: testMethodForm.methodName.value.trim(),
      standard_body: testMethodForm.standardBody.value,
      status: testMethodForm.status.value
    };

    const changed = data.method_name !== originalData.method_name ||
                    data.standard_body !== originalData.standard_body ||
                    data.status !== originalData.status;
    if (!changed) {
      showToast('No changes detected', 'warning');
      return;
    }

    sendAjax('update', data).then(res => {
      if (res.status === 'success') {
        showToast('Test method updated successfully!', 'success');
        loadTestMethods();
        closeModal();
      } else {
        showToast(res.message || 'Update failed', 'danger');
      }
    });
  };

  // === FILTER TABLE (INCLUDES SEARCH, STANDARD BODY, STATUS) ===
  function filterTable() {
    const search = searchInput.value.toLowerCase();
    const standard = standardBodyFilter.value;
    const stat = statusFilter.value;
    const rows = document.querySelectorAll('#testMethodsTable tbody tr');
    let visibleCount = 0;

    rows.forEach(tr => {
      if (tr.classList.contains('no-results')) return;
      const combined = `${tr.dataset.name} ${tr.dataset.standardBody} ${tr.dataset.status}`.toLowerCase();
      let match = true;

      if (search && !combined.includes(search)) match = false;
      if (standard !== 'All Standard Bodies' && tr.dataset.standardBody !== standard) match = false;
      if (stat !== 'All Status' && tr.dataset.status !== stat.toLowerCase()) match = false;

      tr.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    let noResultsRow = document.querySelector('#testMethodsTable tbody tr.no-results');
    if (visibleCount === 0) {
      if (!noResultsRow) {
        document.querySelector('#testMethodsTable tbody').insertAdjacentHTML(
          'beforeend',
          `<tr class="no-results"><td colspan="5" class="text-center text-muted">No matching test methods found</td></tr>`
        );
      }
    } else if (noResultsRow) {
      noResultsRow.remove();
    }
  }

  // Attach events for live search and filter button
  searchInput.addEventListener('input', filterTable);
  standardBodyFilter.addEventListener('change', filterTable);
  statusFilter.addEventListener('change', filterTable);
  btnFilter.addEventListener('click', filterTable);

  // === INITIAL LOAD ===
  loadTestMethods();
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>