
<div class="page-manage-parameters">
  <div class="parameters-container container">
    <!-- Filter + Add -->
    <div class="parameters-card-filter d-flex align-items-center gap-2 mb-3">
      <input type="text" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
      <select class="form-select" style="max-width:120px;">
        <option>All Status</option>
        <option>Active</option>
        <option>Inactive</option>
      </select>
      <button class="btn btn-parameters-filter">Filter</button>
      <div class="ms-auto d-flex gap-2">
        <button class="btn-parameters-new" data-type="individual">+ Add Individual</button>
        <button class="btn-parameters-new" data-type="combo">+ Add Combo</button>
      </div>
    </div>

    <!-- Table -->
    <div class="parameters-table-container">
      <table class="parameters-table table table-hover align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Price</th>
            <th>Type</th>
            <th>Status</th>
            <th style="width:160px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr data-type="individual">
            <td>Aerobic Plate Count</td>
            <td>APC</td>
            <td>50</td>
            <td>Individual</td>
            <td><span class="badge-status bg-success">Active</span></td>
            <td>
              <button class="btn-parameters-edit"><i class="fas fa-edit"></i></button>
              <button class="btn-parameters-delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
          <tr data-type="combo" data-components="Aerobic Plate Count, Coliform Test">
            <td>Combo Test 1</td>
            <td>CT1</td>
            <td>120</td>
            <td>Combo</td>
            <td><span class="badge-status bg-success">Active</span></td>
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
      <div class="parameters-modal-header d-flex justify-content-between align-items-center mb-3">
        <h5 id="parametersModalTitle">New Parameter</h5>
        <button class="btn-close-modal btn btn-sm">&times;</button>
      </div>
      <form>
        <!-- Individual Parameter Name (hidden for combo) -->
        <div class="mb-3" id="individualNameField">
          <label class="parameters-form-label">Parameter Name</label> 
          <input type="text" class="parameters-form-control" id="paramName" placeholder="Enter name" required>
        </div>

        <!-- Combo Parameters Multi-Select -->
        <div class="mb-3 combo-fields d-none">
          <label class="parameters-form-label">Select Parameters</label>
          <select class="parameters-form-select" id="comboComponents" multiple>
            <option value="param1">Coliform</option>
            <option value="param2">Faecal Coliform</option>
            <option value="param3">E. Coli</option>
            <option value="param4">Total Coliform</option>
            <option value="param5">Aerobic Plate Count</option>
            <option value="param6">Yeast & Mold</option>
          </select>
        </div>

        <!-- Price -->
        <div class="mb-3">
          <label class="parameters-form-label">Price</label>
          <input type="number" class="parameters-form-control" id="paramPrice" placeholder="Enter price">
        </div>

        <!-- Status -->
        <div class="mb-3">
          <label class="parameters-form-label">Status</label>
          <select class="parameters-form-select" id="paramStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="parameters-modal-footer-btns d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
const modalOverlay = document.getElementById('parametersModal');
const modalTitle = document.getElementById('parametersModalTitle');
const form = modalOverlay.querySelector('form');
const inputName = document.getElementById('paramName');
const inputPrice = document.getElementById('paramPrice');
const selectStatus = document.getElementById('paramStatus');
const comboFields = modalOverlay.querySelector('.combo-fields');
const individualNameField = document.getElementById('individualNameField');
const comboSelect = document.getElementById('comboComponents');

// Initialize Choices.js for multi-select
const choices = new Choices(comboSelect, {
  removeItemButton: true,
  searchEnabled: true,
  placeholderValue: 'Select parameters',
  shouldSort: false
});

let editingRow = null;
let currentType = 'individual';

// Open modal
document.querySelectorAll('.btn-parameters-new').forEach(btn => {
  btn.addEventListener('click', () => {
    currentType = btn.dataset.type;
    modalTitle.textContent = currentType === 'individual' ? 'Add Individual Parameter' : 'Add Combo Parameter';
    inputName.value = '';
    inputPrice.value = '';
    selectStatus.value = 'active';
    editingRow = null;

    comboFields.classList.toggle('d-none', currentType === 'individual');
    individualNameField.classList.toggle('d-none', currentType === 'combo');

    modalOverlay.classList.add('active');
  });
});

// Close modal
modalOverlay.querySelectorAll('.btn-close-modal, .btn-secondary').forEach(btn => {
  btn.addEventListener('click', () => modalOverlay.classList.remove('active'));
});

// Table actions
function attachRowListeners(row) {
  row.querySelector('.btn-parameters-edit').addEventListener('click', () => {
    editingRow = row;
    currentType = row.dataset.type;
    modalTitle.textContent = currentType === 'individual' ? 'Edit Individual Parameter' : 'Edit Combo Parameter';

    if(currentType==='individual') {
      inputName.value = row.children[0].textContent;
    } else {
      const selected = (row.dataset.components||'').split(', ');
      choices.removeActiveItems();
      choices.setValue(selected.map(val=>({value:val,label:val})));
    }

    inputPrice.value = row.children[2].textContent;
    selectStatus.value = row.children[4].textContent.trim() === 'Active' ? 'active' : 'inactive';

    comboFields.classList.toggle('d-none', currentType==='individual');
    individualNameField.classList.toggle('d-none', currentType==='combo');

    modalOverlay.classList.add('active');
  });

  row.querySelector('.btn-parameters-delete').addEventListener('click', () => {
    if(confirm("Are you sure you want to delete this parameter?")) row.remove();
  });
}

document.querySelectorAll('.parameters-table tbody tr').forEach(attachRowListeners);

// Form submit
form.addEventListener('submit', e => {
  e.preventDefault();
  const name = inputName.value.trim();
  const price = inputPrice.value.trim();
  const status = selectStatus.value;
  if(currentType==='individual' && !name) return;

  let components = currentType==='combo' ? choices.getValue(true) : null;

  if(editingRow) {
    if(currentType==='individual') editingRow.children[0].textContent = name;
    editingRow.children[2].textContent = price;
    editingRow.children[3].textContent = currentType==='individual'?'Individual':'Combo';
    editingRow.children[4].innerHTML = status==='active'?'<span class="badge-status bg-success">Active</span>':'<span class="badge-status bg-secondary">Inactive</span>';
    if(currentType==='combo') editingRow.dataset.components = components.join(', ');
  } else {
    const tableBody = document.querySelector('.parameters-table tbody');
    const newRow = document.createElement('tr');
    newRow.dataset.type = currentType;
    if(currentType==='combo') newRow.dataset.components = components.join(', ');
    newRow.innerHTML = `
      <td>${currentType==='individual'?name:'New Combo'}</td>
      <td>--</td>
      <td>${price}</td>
      <td>${currentType==='individual'?'Individual':'Combo'}</td>
      <td>${status==='active'?'<span class="badge-status bg-success">Active</span>':'<span class="badge-status bg-secondary">Inactive</span>'}</td>
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
