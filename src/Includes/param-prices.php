<div class="page-manage-parameters">
  <div class="parameters-container container">
    <!-- Filter + Add -->
    <div class="parameters-card-filter d-flex align-items-center gap-2 mb-3">
      <input type="text" id="searchInput" placeholder="Search by Name" class="form-control" style="max-width:250px;">
      <select id="statusFilter" class="form-select" style="max-width:120px;">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
      <select id="typeFilter" class="form-select" style="max-width:150px;">
        <option value="">All Types</option>
        <option value="individual">Individual</option>
        <option value="combo">Combo</option>
      </select>
      <button class="btn btn-parameters-filter" id="btnFilter">Filter</button>
      <button class="btn btn-outline-secondary" id="btnReset">Reset</button>
      <div class="ms-auto d-flex gap-2">
        <button class="btn-parameters-new" data-type="individual">+ Add Individual</button>
        <button class="btn-parameters-new" data-type="combo">+ Add Combo</button>
      </div>
    </div>

    <!-- Table -->
    <div class="parameters-table-container">
      <table class="parameters-table table table-hover align-middle" id="pricingTable">
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
          <tr>
            <td colspan="6" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
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
        <h5 id="parametersModalTitle">New Price</h5>
        <button class="btn-close-modal btn btn-sm" id="btnCloseModal">&times;</button>
      </div>
      <form id="pricingForm">
        <?php
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        ?>
        <input type="hidden" id="csrfToken" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="id">
        <input type="hidden" id="type" value="individual">

        <!-- Individual: Parameter Dropdown -->
        <div class="mb-3" id="individualFields">
          <label class="parameters-form-label">Parameter <span class="text-danger">*</span></label>
          <select class="parameters-form-select" id="parameterId">
            <option value="">Select Parameter</option>
          </select>
        </div>

        <!-- Combo: Multi-Select Parameters -->
        <div class="combo-fields d-none">
          <div class="mb-3">
            <label class="parameters-form-label">Select Parameters <span class="text-danger">*</span></label>
            <select class="parameters-form-select" id="comboParameters" multiple></select>
            <small class="text-muted">Select at least 2 parameters</small>
          </div>
          
          <!-- Live Preview -->
          <div id="comboPreview" class="alert alert-info d-none mb-3">
            <strong>Preview:</strong> <span id="comboPreviewText"></span>
          </div>
          
          <!-- Auto-generated code display (only in edit mode) -->
          <div class="mb-3 d-none" id="comboCodeDisplay">
            <label class="parameters-form-label">Combo Code</label>
            <input type="text" class="parameters-form-control" id="comboCodeReadonly" readonly style="background-color: #f0f0f0;">
            <small class="text-muted">Auto-generated</small>
          </div>
        </div>

        <!-- Common: Price, Status -->
        <div class="mb-3">
          <label class="parameters-form-label">Price <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="parameters-form-control" id="testCharge" placeholder="0.00" required>
        </div>
        <div class="mb-3">
          <label class="parameters-form-label">Status <span class="text-danger">*</span></label>
          <select class="parameters-form-select" id="isActive" required>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="parameters-modal-footer-btns d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
          <button type="submit" class="btn btn-success" id="btnSave">Save</button>
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
        <p>Are you sure you want to delete this price?</p>
        <div class="parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnCancelDelete">Cancel</button>
          <button type="button" class="btn btn-danger" id="btnConfirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
  <div id="toastContainer"></div>
</div>

<!-- Choices.js CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
// ========== CONSTANTS ==========
const CONTROLLER_PATH = '../../src/Controllers/pricing-controller.php';

// ========== DOM ELEMENTS ==========
const modalOverlay = document.getElementById('parametersModal');
const modalTitle = document.getElementById('parametersModalTitle');
const pricingForm = document.getElementById('pricingForm');
const typeInput = document.getElementById('type');
const idInput = document.getElementById('id');
const individualFields = document.getElementById('individualFields');
const comboFields = modalOverlay.querySelector('.combo-fields');
const parameterSelect = document.getElementById('parameterId');
const comboParameters = document.getElementById('comboParameters');
const comboPreview = document.getElementById('comboPreview');
const comboPreviewText = document.getElementById('comboPreviewText');
const comboCodeDisplay = document.getElementById('comboCodeDisplay');
const comboCodeReadonly = document.getElementById('comboCodeReadonly');
const testCharge = document.getElementById('testCharge');
const isActive = document.getElementById('isActive');
const tableBody = document.querySelector('#pricingTable tbody');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const typeFilter = document.getElementById('typeFilter');
const btnFilter = document.getElementById('btnFilter');
const btnReset = document.getElementById('btnReset');
const deleteConfirmModal = document.getElementById('deleteConfirmModal');
const btnCancelDelete = document.getElementById('btnCancelDelete');
const btnConfirmDelete = document.getElementById('btnConfirmDelete');
const btnCloseDeleteModal = document.getElementById('btnCloseDeleteModal');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');

let choices = null;
let deleteType = '';
let deleteId = '';
let currentFilters = {};

// ========== TOAST HELPER ==========
function showToast(message, type = 'success') {
    const colors = {
        success: 'bg-success text-white',
        warning: 'bg-warning text-dark',
        error: 'bg-danger text-white',
        danger: 'bg-danger text-white'
    };
    
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${colors[type]} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close ${type === 'warning' ? 'btn-close-black' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.getElementById('toastContainer').appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// ========== AJAX HELPER ==========
async function sendAjax(action, data = {}) {
    try {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const key in data) {
            if (Array.isArray(data[key])) {
                data[key].forEach(val => formData.append(`${key}[]`, val));
            } else {
                formData.append(key, data[key]);
            }
        }
        
        const response = await fetch(CONTROLLER_PATH, {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { status: 'error', message: 'Network error occurred' };
    }
}


// ========== LOAD ACTIVE PARAMETERS ==========
async function loadActiveParameters() {
    const result = await sendAjax('fetchActiveParameters');
    
    if (result.status === 'success') {
        // For individual dropdown
        const options = result.data.map(p => 
            `<option value="${p.parameter_id}">${p.parameter_name}</option>`
        ).join('');
        parameterSelect.innerHTML = '<option value="">Select Parameter</option>' + options;
        
        // For combo multi-select (Choices.js)
        // Destroy existing instance if any
        if (choices) {
            choices.destroy();
            choices = null;
        }
        
        // Initialize Choices.js
        choices = new Choices(comboParameters, {
            removeItemButton: true,
            searchEnabled: true,
            placeholderValue: 'Select parameters',
            shouldSort: false,
            removeItems: true,
            removeItemButton: true,
        });
        
        const choiceOptions = result.data.map(p => ({
            value: `${p.parameter_id}`,
            label: p.parameter_name
        }));
        
        choices.setChoices(choiceOptions, 'value', 'label', true);
        
        console.log('Choices.js initialized:', choices); // Debug
    } else {
        showToast(result.message || 'Failed to load parameters', 'error');
    }
}