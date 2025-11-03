<div class="container">

  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, city or phone" style="max-width: 250px;" />

    <!-- <select class="form-select" id="cityFilter" style="max-width: 160px;">
      <option>All Cities</option>
      <option>Colombo</option>
      <option>Kandy</option>
      <option>Galle</option>
      <option>Jaffna</option>
    </select>

    <button id="btnFilter" class="btn btn-outline-secondary btn-sm" style="min-width: 80px;">Filter</button> -->

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
              <th class="d-none">ID</th>
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
            <!-- Data will be loaded via AJAX -->
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

    <form id="clientForm" method="post">
      <input type="hidden" id="clientId">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="clientName" placeholder="Enter client name" name="clientName" required>

        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Person <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contactPerson" placeholder="Enter contact person" name="contactPerson" required>
           <label id="nameError" style="display: none; color: red;">Invalid Name!</label>

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
          <label id="phoneError" style="display: none; color: red;">Invalid Phone Number!</label>

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
  // ===== CLIENT MANAGEMENT SCRIPT =====

  // === DOM ELEMENTS ===
  const modalOverlay = document.getElementById('modalOverlay');
  const clientForm = document.getElementById('clientForm');
  const btnNewClient = document.getElementById('btnNewClient');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const formTitle = document.getElementById('formTitle');
  const deleteModal = document.getElementById('deleteModal');
  const toastContainer = document.getElementById('toastContainer');
  const nameError = document.getElementById('nameError');
  const phoneError = document.getElementById('phoneError');
  let deleteClientId = null;
  let originalData = {};

  const CONTROLLER_PATH = '../../src/Controllers/client-controller.php';

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

  // === LOAD CLIENTS ===
  function loadClients() {
    sendAjax('fetchAll', {}).then(res => {
      const tbody = document.querySelector('#clientsTable tbody');
      tbody.innerHTML = '';

      if (res.status === 'success' && Array.isArray(res.data)) {
        res.data.forEach(client => {
          tbody.insertAdjacentHTML('beforeend', `
  <tr data-id="${client.client_id}"
      data-name="${client.client_name}"
      data-address="${client.address_line1}"
      data-city="${client.city}"
      data-phone="${client.phone_primary}"
      data-contact="${client.contact_person || ''}">
    <td class="d-none">${client.client_id}</td>  <!-- hide ID -->
    <td>${client.client_name}</td>
    <td>${client.address_line1}</td>
    <td>${client.city}</td>
    <td>${client.phone_primary}</td>
    <td>${client.contact_person || ''}</td>
    <td>${client.registration_date}</td>
    <td>
      <button class="btn btn-sm btn-warning btn-edit"><i class="fas fa-edit"></i></button>
      <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
    </td>
  </tr>
`);

        });
        attachRowEvents();
      } else {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No clients found</td></tr>`;
      }
    });
  }

  // === MODAL CONTROL ===
  function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    nameError.style.display = "none";
    phoneError.style.display = "none";

    if (mode === 'create') {
      clientForm.reset();
      document.getElementById('clientId').value = '';
      btnSave.classList.remove('d-none');
      btnUpdate.classList.add('d-none');
      formTitle.textContent = 'Create New Client';
    } else {
      btnSave.classList.add('d-none');
      btnUpdate.classList.remove('d-none');
      formTitle.textContent = 'Update Client';
    }
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    clientForm.reset();
    nameError.style.display = "none";
    phoneError.style.display = "none";
    originalData = {};
  }

  btnNewClient.onclick = () => openModal('create');
  btnCloseModal.onclick = closeModal;
  btnCancel.onclick = closeModal;
  modalOverlay.onclick = e => {
    if (e.target === modalOverlay) closeModal();
  };

  // === INSERT CLIENT ===
  clientForm.addEventListener('submit', e => {
    e.preventDefault();

    const data = {
      client_name: clientForm.clientName.value.trim(),
      address_line1: clientForm.address.value.trim(),
      city: clientForm.city.value.trim(),
      phone_primary: clientForm.phoneNo.value.trim(),
      contact_person: clientForm.contactPerson.value.trim()
    };

    // if (!/^[0-9+\-\s()]+$/.test(data.phone_primary)) {
    //   showToast('Please enter a valid phone number', 'warning');
    //   return;
    // }

    // if (!/^[A-Za-z\s]+$/.test(data.client_name)) {
    //   nameError.textContent = "Enter Valid Name";
    //   nameError.style.display = "block";
    //   return;
    // } else {
    //   nameError.style.display = "none";
    // }

    sendAjax('insert', data).then(res => {
      if (res.status === 'success') {
        showToast(res.message || 'Client created successfully!', 'success');
        loadClients();
        closeModal();
      } else {
        showToast(res.message || 'Failed to create client', 'danger');
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
        document.getElementById('clientId').value = row.dataset.id;
        clientForm.clientName.value = row.dataset.name;
        clientForm.address.value = row.dataset.address;
        clientForm.city.value = row.dataset.city;
        clientForm.phoneNo.value = row.dataset.phone;
        clientForm.contactPerson.value = row.dataset.contact;

        originalData = {
          client_name: row.dataset.name,
          address_line1: row.dataset.address,
          city: row.dataset.city,
          phone_primary: row.dataset.phone,
          contact_person: row.dataset.contact
        };
      };
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.onclick = e => {
        const row = e.target.closest('tr');
        deleteClientId = row.dataset.id;
        document.getElementById('deleteClientName').textContent = row.dataset.name;
        new bootstrap.Modal(deleteModal).show();
      };
    });
  }

  // === DELETE CLIENT ===
  document.getElementById('confirmDeleteBtn').onclick = () => {
    if (!deleteClientId) return;
    sendAjax('delete', {
      client_id: deleteClientId
    }).then(res => {
      if (res.status === 'success') {
        showToast('Client deleted successfully!', 'danger');
        loadClients();
      } else {
        showToast(res.message || 'Failed to delete client', 'danger');
      }
      const modal = bootstrap.Modal.getInstance(deleteModal);
      modal.hide();
      deleteClientId = null;
    });
  };

  // === UPDATE CLIENT ===
  btnUpdate.onclick = () => {
    const id = document.getElementById('clientId').value;
    const data = {
      client_id: id,
      client_name: clientForm.clientName.value.trim(),
      address_line1: clientForm.address.value.trim(),
      city: clientForm.city.value.trim(),
      phone_primary: clientForm.phoneNo.value.trim(),
      contact_person: clientForm.contactPerson.value.trim()
    };

   // Strict validation
  // const nameRegex = /^[A-Za-z\s]+$/;
  // const phoneRegex = /^[0-9+\-\s()]+$/;

  // if (!nameRegex.test(name)) {
  //   nameError.textContent = "Enter a valid name (letters only)";
  //   nameError.style.display = "block";
  //   return; // stop further execution
  // } else {
  //   nameError.style.display = "none";
  // }

  // if (!phoneRegex.test(phone)) {
  //   phoneError.textContent = "Invalid phone number!";
  //   phoneError.style.display = "block";
  //   return;
  // } else {
  //   phoneError.style.display = "none";
  // }

    const changed = Object.keys(data).some(key => data[key] !== originalData[key]);
    if (!changed) {
      showToast('No changes detected', 'warning');
      return;
    }

    sendAjax('update', data).then(res => {
      if (res.status === 'success') {
        showToast('Client updated successfully!', 'success');
        loadClients();
        closeModal();
      } else {
        showToast(res.message || 'Update failed', 'danger');
      }
    });
  };

  // === SEARCH FILTER ===
  document.getElementById('searchInput').addEventListener('input', e => {
    const search = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#clientsTable tbody tr');
    let visibleCount = 0;

    rows.forEach(tr => {
      const combined = `${tr.dataset.name} ${tr.dataset.city} ${tr.dataset.phone}`.toLowerCase();
      if (combined.includes(search)) {
        tr.style.display = '';
        visibleCount++;
      } else {
        tr.style.display = 'none';
      }
    });

    const noResultsRow = document.querySelector('#clientsTable tbody tr.no-results');
    if (visibleCount === 0) {
      if (!noResultsRow) {
        document.querySelector('#clientsTable tbody').insertAdjacentHTML(
          'beforeend',
          `<tr class="no-results"><td colspan="8" class="text-center text-muted">No matching clients found</td></tr>`
        );
      }
    } else if (noResultsRow) {
      noResultsRow.remove();
    }
  });


  // === INITIAL LOAD ===
  loadClients();
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>