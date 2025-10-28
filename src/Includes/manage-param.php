 <div class="page-manage-parameters">
    <div class="parameters-container container">
      <!-- Filter + New -->
      <div class="parameters-card-filter">
        <input type="text" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
        <select class="form-select" style="max-width:120px;">
          <option>All Status</option>
          <option>Active</option>
          <option>Inactive</option>
        </select>
        <button class="btn btn-parameters-filter">Filter</button>
        <div class="ms-auto">
          <button class="btn-parameters-new">+ New Parameter</button>
        </div>
      </div>

      <!-- Table -->
      <div class="parameters-table-container">
        <table class="parameters-table table table-hover align-middle">
          <thead>
            <tr>
              <th>Parameter Name</th>
              <th>Parameter Code</th>
              <th>Base Unit</th>
              <th>No. of Variants</th>
              <th>Status</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Aerobic Plate Count</td>
              <td>APC</td>
              <td>CFU/g</td>
              <td>5</td>
              <td><span class="badge-status bg-success">Active</span></td>
              <td>
                <button class="btn-parameters-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-parameters-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Coliform Test</td>
              <td>CT</td>
              <td>MPN/g</td>
              <td>3</td>
              <td><span class="badge-status bg-secondary">Inactive</span></td>
              <td>
                <button class="btn-parameters-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-parameters-delete"><i class="fas fa-trash"></i></button>
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
          <button class="btn-close-modal">&times;</button>
        </div>
        <form>
  <div class="mb-3">
    <label class="parameters-form-label">Parameter Name</label>
    <input type="text" class="parameters-form-control" id="paramName" placeholder="Enter name" required>
  </div>
  <div class="mb-3">
    <label class="parameters-form-label">Base Unit</label>
    <input type="text" class="parameters-form-control" id="paramBaseUnit" placeholder="Enter base unit (e.g., CFU/g, mg/L)">
  </div>
  <div class="mb-3">
    <label class="parameters-form-label">Swab Test</label>
    <select class="parameters-form-select" id="paramSwab">
      <option value="enabled">Select</option>
      <option value="enabled">Enabled</option>
      <option value="disabled">Disabled</option>
    </select>
  </div>
  <div class="mb-3">
    <label class="parameters-form-label">Status</label>
    <select class="parameters-form-select" id="paramStatus">
      <option value="active">Selelct Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
  <div class="parameters-modal-footer-btns">
    <button type="button" class="btn btn-secondary">Cancel</button>
    <button type="submit" class="btn btn-success">Save</button>
  </div>
</form>

      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="parameters-modal-overlay" id="deleteConfirmModal">
      <div class="parameters-modal-form">
        <div class="parameters-modal-header">
          <h5>Confirm Delete</h5>
          <button class="btn-close-modal">&times;</button>
        </div>
        <div style="padding:24px;">
          <p>Are you sure you want to delete this parameter?</p>
          <div class="parameters-modal-footer-btns">
            <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
    // Add/Edit Modal
    const modalOverlay = document.getElementById('parametersModal');
    const btnNewParam = document.querySelector('.btn-parameters-new');
    const btnCloseModal = modalOverlay.querySelector('.btn-close-modal');
    const btnCancelModal = modalOverlay.querySelector('.btn-secondary');
    const modalTitle = document.getElementById('parametersModalTitle');
    const form = modalOverlay.querySelector('form');
    const inputName = document.getElementById('paramName');
    const inputBaseUnit = document.getElementById('paramBaseUnit');
    const selectStatus = document.getElementById('paramStatus');

    let editingRow = null; // Track which row we are editing

    btnNewParam.addEventListener('click', () => {
      modalTitle.textContent = 'New Parameter';
      inputName.value = '';
      inputBaseUnit.value = '';
      selectStatus.value = 'active';
      editingRow = null;
      modalOverlay.classList.add('active');
    });

    btnCloseModal.addEventListener('click', () => modalOverlay.classList.remove('active'));
    btnCancelModal.addEventListener('click', () => modalOverlay.classList.remove('active'));
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) modalOverlay.classList.remove('active');
    });

    // Delete Modal
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const btnCancelDelete = document.getElementById('cancelDelete');
    const btnConfirmDelete = document.getElementById('confirmDelete');
    const closeDeleteModalBtn = deleteConfirmModal.querySelector('.btn-close-modal');
    let rowToDelete = null;

    document.querySelectorAll('.btn-parameters-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        rowToDelete = btn.closest('tr');
        deleteConfirmModal.classList.add('active');
      });
    });

    btnCancelDelete.addEventListener('click', () => {
      rowToDelete = null;
      deleteConfirmModal.classList.remove('active');
    });

    closeDeleteModalBtn.addEventListener('click', () => {
      rowToDelete = null;
      deleteConfirmModal.classList.remove('active');
    });

    deleteConfirmModal.addEventListener('click', (e) => {
      if (e.target === deleteConfirmModal) {
        rowToDelete = null;
        deleteConfirmModal.classList.remove('active');
      }
    });

    btnConfirmDelete.addEventListener('click', () => {
      if (rowToDelete) rowToDelete.remove();
      deleteConfirmModal.classList.remove('active');
    });

    // Edit & Add Row
    function attachRowListeners(row) {
      row.querySelector('.btn-parameters-delete').addEventListener('click', () => {
        rowToDelete = row;
        deleteConfirmModal.classList.add('active');
      });

      row.querySelector('.btn-parameters-edit').addEventListener('click', () => {
        editingRow = row;
        inputName.value = row.children[0].textContent;
        inputBaseUnit.value = row.children[2].textContent;
        selectStatus.value = row.children[4].textContent.trim() === 'Active' ? 'active' : 'inactive';
        modalTitle.textContent = 'Edit Parameter';
        modalOverlay.classList.add('active');
      });
    }

    document.querySelectorAll('.parameters-table tbody tr').forEach(attachRowListeners);

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = inputName.value.trim();
      const baseUnit = inputBaseUnit.value.trim();
      const status = selectStatus.value;
      if (!name) return;

      if (editingRow) {
        editingRow.children[0].textContent = name;
        editingRow.children[2].textContent = baseUnit || '--';
        editingRow.children[4].innerHTML = status === 'active'
          ? '<span class="badge-status bg-success">Active</span>'
          : '<span class="badge-status bg-secondary">Inactive</span>';
      } else {
        const tableBody = document.querySelector('.parameters-table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${name}</td>
            <td>--</td>
            <td>${baseUnit || '--'}</td>
            <td>0</td>
            <td>${status === 'active'
            ? '<span class="badge-status bg-success">Active</span>'
            : '<span class="badge-status bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn-parameters-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-parameters-delete"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(newRow);
        attachRowListeners(newRow);
      }

      modalOverlay.classList.remove('active');
    });
  </script>