 <div class="page-manage-variants">
    <div class="variants-container container">
      <!-- Filter + New -->
      <div class="variants-card-filter">
        <input type="text" placeholder="Search by Variant Name" class="form-control" style="max-width:250px;">
        <select class="form-select" style="max-width:150px;">
          <option value="">All Parameters</option>
          <option value="APC">Aerobic Plate Count</option>
          <option value="CT">Coliform Test</option>
        </select>
        <select class="form-select" style="max-width:120px;">
          <option>All Status</option>
          <option>Active</option>
          <option>Inactive</option>
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
              <th>Condition Value</th>
              <th>Status</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Aerobic Plate Count</td>
              <td>at 37°C</td>
              <td>37°C</td>
              <td><span class="badge-status bg-success">Active</span></td>
              <td>
                <button class="btn-variants-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-variants-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Aerobic Plate Count</td>
              <td>at 30°C</td>
              <td>30°C</td>
              <td><span class="badge-status bg-success">Active</span></td>
              <td>
                <button class="btn-variants-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-variants-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Coliform Test</td>
              <td>MPN Method</td>
              <td>Standard</td>
              <td><span class="badge-status bg-secondary">Inactive</span></td>
              <td>
                <button class="btn-variants-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-variants-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
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
              <option value="Aerobic Plate Count">Aerobic Plate Count</option>
              <option value="Coliform Test">Coliform Test</option>
              <option value="E. coli Test">E. coli Test</option>
              <option value="Salmonella Test">Salmonella Test</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Variant Name</label>
            <input type="text" class="variants-form-control" id="variantName" placeholder="Enter variant name" required>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Condition Value</label>
            <input type="text" class="variants-form-control" id="variantCondition" placeholder="Enter condition value (e.g., 37°C, Standard)">
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Status</label>
            <select class="variants-form-select" id="variantStatus">
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
    // Add/Edit Modal
    const modalOverlay = document.getElementById('variantsModal');
    const btnNewVariant = document.querySelector('.btn-variants-new');
    const btnCloseModal = modalOverlay.querySelector('.btn-close-modal');
    const btnCancelModal = modalOverlay.querySelector('.btn-secondary');
    const modalTitle = document.getElementById('variantsModalTitle');
    const form = modalOverlay.querySelector('form');
    const inputParameter = document.getElementById('variantParameter');
    const inputName = document.getElementById('variantName');
    const inputCondition = document.getElementById('variantCondition');
    const selectStatus = document.getElementById('variantStatus');

    let editingRow = null;

    btnNewVariant.addEventListener('click', () => {
      modalTitle.textContent = 'New Variant';
      inputParameter.value = '';
      inputName.value = '';
      inputCondition.value = '';
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

    document.querySelectorAll('.btn-variants-delete').forEach(btn => {
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
      row.querySelector('.btn-variants-delete').addEventListener('click', () => {
        rowToDelete = row;
        deleteConfirmModal.classList.add('active');
      });

      row.querySelector('.btn-variants-edit').addEventListener('click', () => {
        editingRow = row;
        inputParameter.value = row.children[0].textContent;
        inputName.value = row.children[1].textContent;
        inputCondition.value = row.children[2].textContent;
        selectStatus.value = row.children[3].textContent.trim() === 'Active' ? 'active' : 'inactive';
        modalTitle.textContent = 'Edit Variant';
        modalOverlay.classList.add('active');
      });
    }

    document.querySelectorAll('.variants-table tbody tr').forEach(attachRowListeners);

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const parameter = inputParameter.value.trim();
      const name = inputName.value.trim();
      const condition = inputCondition.value.trim();
      const status = selectStatus.value;
      
      if (!parameter || !name) {
        alert('Please fill in all required fields');
        return;
      }

      if (editingRow) {
        editingRow.children[0].textContent = parameter;
        editingRow.children[1].textContent = name;
        editingRow.children[2].textContent = condition || '--';
        editingRow.children[3].innerHTML = status === 'active'
          ? '<span class="badge-status bg-success">Active</span>'
          : '<span class="badge-status bg-secondary">Inactive</span>';
      } else {
        const tableBody = document.querySelector('.variants-table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${parameter}</td>
            <td>${name}</td>
            <td>${condition || '--'}</td>
            <td>${status === 'active'
            ? '<span class="badge-status bg-success">Active</span>'
            : '<span class="badge-status bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn-variants-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-variants-delete"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(newRow);
        attachRowListeners(newRow);
      }

      modalOverlay.classList.remove('active');
    });
  </script>