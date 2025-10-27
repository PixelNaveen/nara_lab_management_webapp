<div class="container">

  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <input
      type="text"
      class="form-control"
      id="searchInput"
      placeholder="Search by name, city or phone"
      style="max-width: 250px;" />

    <select class="form-select" id="cityFilter" style="max-width: 160px;">
      <option>All Cities</option>
      <option>Colombo</option>
      <option>Kandy</option>
      <option>Galle</option>
      <option>Jaffna</option>
    </select>

    <button id="btnFilter" class="btn btn-outline-secondary btn-sm" style="min-width: 80px;">Filter</button>

    <!-- New Client button pushed to right -->
    <div class="ms-auto">
      <button class="btn btn-primary btn-sm" id="btnNewClient">+ New Client</button>
    </div>
  </div>

  <div class="row g-4">
    <!-- Clients Table -->
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
                <button class="btn btn-sm btn-edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
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
                <button class="btn btn-sm btn-edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
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
                <button class="btn btn-sm btn-edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Overlay Form -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-form">
    <div class="modal-header">
      <h5 id="formTitle">Create New Client</h5>
      <button class="btn-close-modal" id="btnCloseModal">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <form id="clientForm">
      <input type="hidden" id="clientId">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="clientName" placeholder="Enter client name" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Person <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contactPerson" placeholder="Enter contact person" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="addressLine1" placeholder="Enter street address" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">City <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="city" placeholder="Enter city" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
          <input type="tel" class="form-control" id="phonePrimary" placeholder="Enter phone number" required>
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

<script>
  const modalOverlay = document.getElementById('modalOverlay');
  const btnNewClient = document.getElementById('btnNewClient');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');
  const formTitle = document.getElementById('formTitle');
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const clientForm = document.getElementById('clientForm');

  // Open modal for new client
  btnNewClient.addEventListener('click', () => {
    openModal('create');
  });

  // Close modal
  btnCloseModal.addEventListener('click', closeModal);
  btnCancel.addEventListener('click', closeModal);
  
  // Close modal when clicking outside
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) {
      closeModal();
    }
  });

  // Edit button click
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const row = e.target.closest('tr');
      loadClientData(row);
      openModal('edit');
    });
  });

  // Delete button click
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const row = e.target.closest('tr');
      const clientName = row.dataset.name;
      if (confirm(`Are you sure you want to delete ${clientName}?`)) {
        // Add your delete logic here
        console.log('Deleting client ID:', row.dataset.id);
        row.remove();
      }
    });
  });

  // Form submission
  clientForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Validate phone number (basic validation)
    const phone = document.getElementById('phonePrimary').value;
    if (!/^[0-9+\-\s()]+$/.test(phone)) {
      alert('Please enter a valid phone number');
      return;
    }
    
    // Add your save/update logic here
    const formData = {
      clientId: document.getElementById('clientId').value,
      clientName: document.getElementById('clientName').value,
      addressLine1: document.getElementById('addressLine1').value,
      city: document.getElementById('city').value,
      phonePrimary: document.getElementById('phonePrimary').value,
      contactPerson: document.getElementById('contactPerson').value
    };
    
    console.log('Form submitted:', formData);
    closeModal();
  });

  // Update button click
  btnUpdate.addEventListener('click', () => {
    // Add your update logic here
    const formData = {
      clientId: document.getElementById('clientId').value,
      clientName: document.getElementById('clientName').value,
      addressLine1: document.getElementById('addressLine1').value,
      city: document.getElementById('city').value,
      phonePrimary: document.getElementById('phonePrimary').value,
      contactPerson: document.getElementById('contactPerson').value
    };
    
    console.log('Client updated:', formData);
    closeModal();
  });

  // Search functionality
  document.getElementById('searchInput').addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#clientsTable tbody tr');
    
    rows.forEach(row => {
      const name = row.dataset.name.toLowerCase();
      const city = row.dataset.city.toLowerCase();
      const phone = row.dataset.phone.toLowerCase();
      
      if (name.includes(searchTerm) || city.includes(searchTerm) || phone.includes(searchTerm)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
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
    } else if (mode === 'edit') {
      formTitle.textContent = 'Update Client';
      btnSave.classList.add('d-none');
      btnUpdate.classList.remove('d-none');
    }
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    clientForm.reset();
  }

  function loadClientData(row) {
    document.getElementById('clientId').value = row.dataset.id;
    document.getElementById('clientName').value = row.dataset.name;
    document.getElementById('addressLine1').value = row.dataset.address;
    document.getElementById('city').value = row.dataset.city;
    document.getElementById('phonePrimary').value = row.dataset.phone;
    document.getElementById('contactPerson').value = row.dataset.contact;
  }
</script>