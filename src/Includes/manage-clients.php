<div class="container">

  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, city or phone" style="max-width: 250px;" />

    <select class="form-select" id="cityFilter" style="max-width: 160px;">
      <option>All Cities</option>
      <option>Colombo</option>
      <option>Kandy</option>
      <option>Galle</option>
      <option>Jaffna</option>
    </select>

    <button id="btnFilter" class="btn btn-outline-secondary btn-sm" style="min-width: 80px;">Filter</button>

    <div class="ms-auto">
      <button class="btn btn-primary btn-sm" id="btnNewClient">+ New Client</button>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-12">
      <div class="table-container">
        <table class="table table-hover align-middle clientsTable" id="clientsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client Name</th>
              <th>Address</th>
              <th>City</th>
              <th>Phone</th>
              <th>Contact Person</th>
              <th>Registration Date</th>
              <th style="width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr data-id="1" data-name="ABC Medical Center" data-address="123 Galle Road" data-city="Colombo" data-phone="0112345678" data-contact="Dr. Perera" data-regdate="2024-01-15">
              <td>1</td>
              <td>ABC Medical Center</td>
              <td>123 Galle Road</td>
              <td>Colombo</td>
              <td>0112345678</td>
              <td>Dr. Perera</td>
              <td>2024-01-15</td>
              <td>
                <button class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr data-id="2" data-name="City Hospital" data-address="456 Main Street" data-city="Kandy" data-phone="0812345678" data-contact="Dr. Silva" data-regdate="2024-02-20">
              <td>2</td>
              <td>City Hospital</td>
              <td>456 Main Street</td>
              <td>Kandy</td>
              <td>0812345678</td>
              <td>Dr. Silva</td>
              <td>2024-02-20</td>
              <td>
                <button class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr data-id="3" data-name="Green Valley Clinic" data-address="789 Beach Road" data-city="Galle" data-phone="0912345678" data-contact="Dr. Fernando" data-regdate="2024-03-10">
              <td>3</td>
              <td>Green Valley Clinic</td>
              <td>789 Beach Road</td>
              <td>Galle</td>
              <td>0912345678</td>
              <td>Dr. Fernando</td>
              <td>2024-03-10</td>
              <td>
                <button class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Client Modal -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-form">
    <div class="modal-header">
      <h5 id="formTitle">Create New Client</h5>
      <button class="btn-close-modal" id="btnCloseModal"><i class="fas fa-times"></i></button>
    </div>

    <form id="clientForm" method="post" >
      <input type="hidden" id="clientId">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="clientName" placeholder="Enter client name" name="clientName" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Person <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contactPerson" placeholder="Enter contact person" name="contactPerson" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="addressLine1" placeholder="Enter street address" name="address" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">City <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="city" placeholder="Enter city" name="city" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
          <input type="tel" class="form-control" id="phonePrimary" placeholder="Enter phone number" name="phoneNo" required>
        </div>
      </div>

      <div class="modal-footer-btns">
        <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
        <button type="submit" class="btn btn-success" id="btnSave">Save Client</button>
        <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Client</button>
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
        Are you sure you want to delete <span id="deleteClientName"></span>?
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
  const modalOverlay = document.getElementById('modalOverlay');
  const btnNewClient = document.getElementById('btnNewClient');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');
  const formTitle = document.getElementById('formTitle');
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const clientForm = document.getElementById('clientForm');
  let deleteClientId = null;
  let originalData = {};

  function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const bgColor = {
      success: 'bg-success text-white',
      warning: 'bg-warning text-dark',
      danger: 'bg-danger text-white'
    } [type] || 'bg-success text-white';
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${bgColor} border-0 mb-2`;
    toastEl.role = 'alert';
    toastEl.ariaLive = 'assertive';
    toastEl.ariaAtomic = 'true';
    toastEl.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" c
    lass="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, {
      delay: 2500
    });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // Modal Open/Close
  btnNewClient.addEventListener('click', () => openModal('create'));
  btnCloseModal.addEventListener('click', closeModal);
  btnCancel.addEventListener('click', closeModal);
  modalOverlay.addEventListener('click', e => {
    if (e.target === modalOverlay) closeModal();
  });

  function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (mode === 'create') {
      formTitle.textContent = 'Create New Client';
      clientForm.reset();
      document.getElementById('clientId').value = '';
      btnSave.classList.remove('d-none');
      btnUpdate.classList.add('d-none');
    } else {
      formTitle.textContent = 'Update Client';
      btnSave.classList.add('d-none');
      btnUpdate.classList.remove('d-none');
    }
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    clientForm.reset();
    originalData = {};
  }

  function loadClientData(row) {
    document.getElementById('clientId').value = row.dataset.id;
    document.getElementById('clientName').value = row.dataset.name;
    document.getElementById('addressLine1').value = row.dataset.address;
    document.getElementById('city').value = row.dataset.city;
    document.getElementById('phonePrimary').value = row.dataset.phone;
    document.getElementById('contactPerson').value = row.dataset.contact;

    originalData = {
      name: row.dataset.name,
      address: row.dataset.address,
      city: row.dataset.city,
      phone: row.dataset.phone,
      contact: row.dataset.contact
    };
  }

  // Edit & Delete buttons
  function attachRowButtons() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        loadClientData(row);
        openModal('edit');
      });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const row = e.target.closest('tr');
        deleteClientId = row.dataset.id;
        document.getElementById('deleteClientName').textContent = row.dataset.name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
      });
    });
  }

  attachRowButtons();

  document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!deleteClientId) return;
    const row = document.querySelector(`tr[data-id='${deleteClientId}']`);
    // TODO: PHP delete
    row.remove();
    showToast('Client deleted successfully', 'danger');
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    deleteClientId = null;
  });

  // Form submit for creating client
  clientForm.addEventListener('submit', e => {
    e.preventDefault();
    const phone = document.getElementById('phonePrimary').value;
    if (!/^[0-9+\-\s()]+$/.test(phone)) return showToast('Please enter a valid phone number', 'warning');

    const formData = {
      clientId: document.getElementById('clientId').value,
      clientName: document.getElementById('clientName').value,
      addressLine1: document.getElementById('addressLine1').value,
      city: document.getElementById('city').value,
      phonePrimary: document.getElementById('phonePrimary').value,
      contactPerson: document.getElementById('contactPerson').value
    };

    if (btnSave.classList.contains('d-none')) {
      // handled by btnUpdate
      return;
    } else {
      // TODO: PHP insert
      const tbody = document.querySelector('#clientsTable tbody');
      const newId = tbody.children.length + 1;
      const tr = document.createElement('tr');
      tr.dataset.id = newId;
      tr.dataset.name = formData.clientName;
      tr.dataset.address = formData.addressLine1;
      tr.dataset.city = formData.city;
      tr.dataset.phone = formData.phonePrimary;
      tr.dataset.contact = formData.contactPerson;
      tr.dataset.regdate = new Date().toISOString().split('T')[0];

      tr.innerHTML = `
      <td>${newId}</td>
      <td>${formData.clientName}</td>
      <td>${formData.addressLine1}</td>
      <td>${formData.city}</td>
      <td>${formData.phonePrimary}</td>
      <td>${formData.contactPerson}</td>
      <td>${tr.dataset.regdate}</td>
      <td>
        <button class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
      </td>`;
      tbody.appendChild(tr);
      attachRowButtons();
      showToast('Client created successfully', 'success');
    }

    closeModal();
  });

  // Update button
  btnUpdate.addEventListener('click', () => {
    const clientId = document.getElementById('clientId').value;
    const row = document.querySelector(`tr[data-id='${clientId}']`);
    if (!row) return;

    const newData = {
      name: document.getElementById('clientName').value,
      address: document.getElementById('addressLine1').value,
      city: document.getElementById('city').value,
      phone: document.getElementById('phonePrimary').value,
      contact: document.getElementById('contactPerson').value
    };

    const isChanged = Object.keys(newData).some(key => newData[key] !== originalData[key]);
    if (!isChanged) {
      showToast('No changes detected!', 'warning');
      closeModal();
      return;
    }

    // TODO: PHP update

    row.dataset.name = newData.name;
    row.dataset.address = newData.address;
    row.dataset.city = newData.city;
    row.dataset.phone = newData.phone;
    row.dataset.contact = newData.contact;

    row.cells[1].textContent = newData.name;
    row.cells[2].textContent = newData.address;
    row.cells[3].textContent = newData.city;
    row.cells[4].textContent = newData.phone;
    row.cells[5].textContent = newData.contact;

    showToast('Client updated successfully!', 'success');
    closeModal();
  });

  // Search
  document.getElementById('searchInput').addEventListener('input', e => {
    const searchTerm = e.target.value.toLowerCase();
    document.querySelectorAll('#clientsTable tbody tr').forEach(row => {
      const name = row.dataset.name.toLowerCase(); 
      const city = row.dataset.city.toLowerCase();
      const phone = row.dataset.phone.toLowerCase();
      row.style.display = (name.includes(searchTerm) || city.includes(searchTerm) || phone.includes(searchTerm)) ? '' : 'none';
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>