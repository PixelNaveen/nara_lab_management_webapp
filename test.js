
  // ===== Constants =====
  // ===== Constants =====
  const CONTROLLER_PATH = '../../src/Controllers/parameter-controller.php';

  // ===== DOM Elements =====
  const modalOverlay = document.getElementById('parametersModal');
  const parameterForm = document.getElementById('parameterForm');
  const btnNewParam = document.getElementById('btnNewParam');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCancel = document.getElementById('btnCancel');

  const deleteConfirmModal = document.getElementById('deleteConfirmModal');
  const btnCancelDelete = document.getElementById('cancelDelete');
  const btnConfirmDelete = document.getElementById('confirmDelete');
  const btnCloseDeleteModal = document.getElementById('btnCloseDeleteModal');

  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const btnFilter = document.getElementById('btnFilter');
  const btnReset = document.getElementById('btnReset');

  const paramSwab = document.getElementById('paramSwab');
  const swabPriceRow = document.getElementById('swabPriceRow');
  const tbody = document.querySelector('#parametersTable tbody');

  let deleteParamId = null;
  let currentPage = 1;
  let currentFilters = {};

  // ======== Helper Functions ========

  // Toast notification
  function showToast(message, type = 'success') {
    const colors = {
      success: 'bg-success text-white',
      warning: 'bg-warning text-dark',
      danger: 'bg-danger text-white',
      info: 'bg-info text-white'
    };

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${colors[type]} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close ${type === 'warning' ? 'btn-close-black' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;

    document.getElementById('toastContainer').appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, {
      delay: 3000
    });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  // ✅ FIXED: AJAX helper - handles both URLSearchParams and FormData
  async function sendAjax(action, data = {}) {
    try {
      let body;

      // If data is already FormData, just append action
      if (data instanceof FormData) {
        data.append('action', action);
        body = data;
      } else {
        // Otherwise use URLSearchParams
        const formData = new URLSearchParams({
          action,
          ...data
        });
        body = formData;
      }

      const response = await fetch(CONTROLLER_PATH, {
        method: 'POST',
        body: body
      });
      return await response.json();
    } catch (error) {
      console.error('AJAX Error:', error);
      return {
        status: 'error',
        message: 'Network error occurred'
      };
    }
  }

  // Load active test methods
 let methodSelect; // store TomSelect instance

async function loadTestMethods() {
  const result = await sendAjax('fetchMethods');
  const select = document.getElementById('paramMethod');
  select.innerHTML = '';

  if (result.status === 'success' && Array.isArray(result.data)) {
    result.data.forEach(method => {
      const opt = document.createElement('option');
      opt.value = method.method_id;
      opt.textContent = method.method_name;
      select.appendChild(opt);
    });
  }

  // Initialize Tom Select (only once)
  if (!methodSelect) {
    methodSelect = new TomSelect('#paramMethod', {
      plugins: ['remove_button'],
      maxItems: null,
      placeholder: 'Select method(s)...',
      create: false,
      // sortField: { field: "text", direction: "asc" },
    });
  } else {
    methodSelect.clearOptions();
    result.data.forEach(method => {
      methodSelect.addOption({ value: method.method_id, text: method.method_name });
    });
  }
}


  // Render parameters table
  function renderTable(data) {
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No parameters found</td></tr>';
      return;
    }

    tbody.innerHTML = data.map(p => `
        <tr data-id="${p.parameter_id}">
            <td>${p.parameter_name}</td>
            <td>${p.parameter_code}</td>
            <td>${p.parameter_category || '<em class="text-muted">--</em>'}</td>
            <td>${p.base_unit || '<em class="text-muted">--</em>'}</td>
            <td><span class="badge-status bg-${p.swab_enabled == 1 ? 'success' : 'secondary'}">
                ${p.swab_enabled == 1 ? 'Enabled' : 'Disabled'}</span></td>
            <td>${p.variant_count || 0}</td>
            <td><span class="badge-status bg-${p.is_active == 1 ? 'success' : 'secondary'}">
                ${p.is_active == 1 ? 'Active' : 'Inactive'}</span></td>
            
            <td>
                <button class="btn-parameters-edit" data-id="${p.parameter_id}" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-parameters-delete" data-id="${p.parameter_id}" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    attachRowEvents();

    // <td><small>${p.methods || '<em class="text-muted">--</em>'}</small></td> table data for show methods
  }

  // Load parameters
  async function loadParameters(page = 1) {
    currentPage = page;
    const filters = {
      ...currentFilters,
      page,
      limit: 50
    };
    tbody.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

    const result = await sendAjax('fetchAll', filters);
    if (result.status === 'success') renderTable(result.data);
    else {
      showToast(result.message || 'Failed to load parameters', 'danger');
      tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>';
    }
  }

  // Attach row edit/delete events
  function attachRowEvents() {
    document.querySelectorAll('.btn-parameters-edit').forEach(btn =>
      btn.addEventListener('click', () => editParameter(btn.dataset.id))
    );
    document.querySelectorAll('.btn-parameters-delete').forEach(btn =>
      btn.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        const name = row.children[0].textContent;
        deleteParamId = btn.dataset.id;
        document.getElementById('deleteParamName').textContent = name;
        deleteConfirmModal.classList.add('active');
      })
    );
  }

  // Open/Close Modal
  async function openModal(mode = 'create') {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    parameterForm.reset();
    document.getElementById('formMode').value = mode;
    document.getElementById('parametersModalTitle').textContent =
      mode === 'edit' ? 'Edit Parameter' : 'New Parameter';
    document.getElementById('parameterId').value = '';
    document.getElementById('paramCode').value = '';
    swabPriceRow.style.display = 'none';
    await loadTestMethods();
  }

  function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  // ✅ FIXED: Edit parameter - properly select multiple methods
  async function editParameter(id) {
    const result = await sendAjax('getById', {
      parameter_id: id
    });
    if (result.status === 'success') {
      const data = result.data;
      await openModal('edit');

      document.getElementById('parameterId').value = data.parameter_id;
      document.getElementById('paramCode').value = data.parameter_code;
      document.getElementById('paramName').value = data.parameter_name;
      document.getElementById('paramCategory').value = data.parameter_category || '';
      document.getElementById('paramBaseUnit').value = data.base_unit || '';
      document.getElementById('paramSwab').value = data.swab_enabled;
      document.getElementById('paramStatus').value = data.is_active;

      // ✅ FIXED: Select multiple methods properly
      setTimeout(() => {
        const select = document.getElementById('paramMethod');

        // Clear all selections first
        Array.from(select.options).forEach(opt => opt.selected = false);

        // Select matching method_ids
        if (data.method_ids && Array.isArray(data.method_ids)) {
          data.method_ids.forEach(methodId => {
            Array.from(select.options).forEach(opt => {
              if (parseInt(opt.value) === parseInt(methodId)) {
                opt.selected = true;
              }
            });
          });

          console.log('Selected methods:', data.method_ids);
        }
      }, 100);

      swabPriceRow.style.display = 'none';
    } else {
      showToast(result.message || 'Failed to load parameter', 'danger');
    }
  }

  // ✅ FIXED: Save parameter - properly send array of method_ids
  parameterForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const mode = document.getElementById('formMode').value;
    const id = document.getElementById('parameterId').value;

    // ✅ CRITICAL: Use FormData to properly send arrays
    const formData = new FormData();

    // Add basic fields
    formData.append('csrf_token', document.getElementById('csrfToken').value);
    formData.append('parameter_name', document.getElementById('paramName').value.trim());
    formData.append('parameter_category', document.getElementById('paramCategory').value.trim());
    formData.append('base_unit', document.getElementById('paramBaseUnit').value.trim());
    formData.append('swab_enabled', document.getElementById('paramSwab').value);
    formData.append('is_active', document.getElementById('paramStatus').value);

    // ✅ CRITICAL: Add each selected method_id separately with [] notation
    const methodSelect = document.getElementById('paramMethod');
    const selectedMethods = Array.from(methodSelect.selectedOptions).map(opt => opt.value);

    selectedMethods.forEach(methodId => {
      formData.append('method_ids[]', methodId);
    });

    // Validation
    if (!formData.get('parameter_name')) {
      showToast('Parameter name is required', 'warning');
      return;
    }

    // Handle swab price for new parameters
    if (mode === 'create' && formData.get('swab_enabled') === '1') {
      const priceValue = document.getElementById('paramSwabPrice').value.trim();
      formData.append('swab_price', priceValue !== '' ? priceValue : '0.00');
    }

    // Handle edit mode
    if (mode === 'edit') {
      formData.append('parameter_id', id);
      formData.append('parameter_code', document.getElementById('paramCode').value);
    }

    const action = mode === 'edit' ? 'update' : 'insert';

    // ✅ DEBUG: Log what's being sent
    console.log('Saving with methods:', selectedMethods);

    // ✅ CRITICAL: Pass FormData directly to sendAjax
    const result = await sendAjax(action, formData);

    if (result.status === 'success') {
      showToast(result.message, 'success');
      closeModal();
      loadParameters(currentPage);
    } else {
      showToast(result.message || 'Failed to save parameter', 'danger');
    }
  });

  // Delete parameter
  btnConfirmDelete.addEventListener('click', async () => {
    if (!deleteParamId) return;
    const result = await sendAjax('delete', {
      csrf_token: document.getElementById('csrfToken').value,
      parameter_id: deleteParamId
    });
    if (result.status === 'success') showToast(result.message, 'success');
    else if (result.status === 'warning') showToast(result.message, 'warning');
    else showToast(result.message || 'Failed to delete parameter', 'danger');
    deleteConfirmModal.classList.remove('active');
    deleteParamId = null;
    loadParameters(currentPage);
  });

  // Swab price visibility
  paramSwab.addEventListener('click', () => {
    const mode = document.getElementById('formMode').value;
    swabPriceRow.style.display = (mode === 'create' && paramSwab.value === '1') ? 'block' : 'none';
  });

  // Filter / Reset
  btnFilter.addEventListener('click', () => {
    currentFilters = {
      search: searchInput.value.trim(),
      is_active: statusFilter.value
    };
    loadParameters(1);
  });
  btnReset.addEventListener('click', () => {
    searchInput.value = '';
    statusFilter.value = '';
    currentFilters = {};
    loadParameters(1);
  });
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') btnFilter.click();
  });

  // Modal controls
  btnNewParam.addEventListener('click', () => openModal('create'));
  btnCloseModal.addEventListener('click', closeModal);
  btnCancel.addEventListener('click', closeModal);
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) closeModal();
  });

  btnCancelDelete.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
  btnCloseDeleteModal.addEventListener('click', () => deleteConfirmModal.classList.remove('active'));
  deleteConfirmModal.addEventListener('click', (e) => {
    if (e.target === deleteConfirmModal) deleteConfirmModal.classList.remove('active');
  });

  // Initial load
  window.onload = () => {
    loadTestMethods();
    loadParameters(1);
  };
