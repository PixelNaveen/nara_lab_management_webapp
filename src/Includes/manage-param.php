<div class="page-manage-parameters">
  <div class="parameters-container container-fluide">
    <!-- Filter + New -->
    <div class="parameters-card-filter">
      <input type="text" id="searchInput" placeholder="Search by Parameter Name" class="form-control" style="max-width:250px;">
      <select class="form-select" id="statusFilter" style="max-width:120px;">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
      <button class="btn btn-parameters-filter" id="btnFilter">Filter</button>
      <button class="btn btn-outline-secondary" id="btnReset">Reset</button>

      <div class="ms-auto">
        <button class="btn btn-outline-primary me-2" id="btnTableView">Table View</button>
        <button class="btn-parameters-new" id="btnNewParam">+ New Parameter</button>
      </div>
    </div>

    <!-- Table -->
    <div class="parameters-table-container">
      <table class="parameters-table table table-hover align-middle" id="parametersTable">
        <thead>
          <tr>
            <th>Parameter Name</th>
            <th class="text-center">Swab</th>
            <th class="text-center">SLAB Status</th>
            <th>Status</th>
            <th style="width:120px;" class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="9" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Toast Container (bottom-right) -->
  <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 99999;"></div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong><span id="deleteParamName"></span></strong>?</p>
          <p class="text-muted mb-0">This action can be undone by re-adding the item.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="btnConfirmDelete"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL: ADD / EDIT PARAMETER (Multi-Step)   -->
  <!-- ========================================== -->
  <div class="parameters-modal-overlay" id="paramModal">
    <div class="parameters-modal-form parameters-modal-form--multistep">
      <!-- Header -->
      <div class="parameters-modal-header">
        <h5 id="modalTitle"><i class="fas fa-flask"></i> Add Parameter</h5>
        <button class="btn-close-modal" id="btnCloseModal">&times;</button>
      </div>

      <!-- Step Indicator -->
      <div class="step-indicator">
        <div class="step-item active" data-step="1">
          <div class="step-number">1</div>
          <div class="step-label">Basic Info</div>
        </div>
        <div class="step-connector"></div>
        <div class="step-item" data-step="2">
          <div class="step-number">2</div>
          <div class="step-label">Units & Config</div>
        </div>
      </div>

      <!-- Form -->
      <form id="paramForm" autocomplete="off">
        <input type="hidden" id="paramId" value="">
        <input type="hidden" id="paramCode" value="">

        <!-- ========================= STEP 1: Basic Info ========================= -->
        <div class="step-content active" data-step="1">
          <div class="step-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

          <div class="row mb-3">
            <div class="col-md-8">
              <label class="parameters-form-label">Parameter Name *</label>
              <input type="text" id="paramName" class="parameters-form-control" placeholder="e.g. Coliforms" required>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Short Name</label>
              <input type="text" id="paramShortName" class="parameters-form-control" placeholder="e.g. Coliforms">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="parameters-form-label">Category</label>
              <select id="paramCategory" class="parameters-form-select">
                <option value="">General</option>
                <option value="Microbiology">Microbiology</option>
                <option value="Chemistry">Chemistry</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Display Format</label>
              <select id="paramDisplayFormat" class="parameters-form-select">
                <option value="normal">Normal</option>
                <option value="scientific">Scientific (Italic)</option>
                <option value="superscript">Superscript</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Status</label>
              <select id="paramStatus" class="parameters-form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-check">
                <input type="checkbox" id="paramSwab" class="form-check-input">
                <label class="form-check-label" for="paramSwab">Swab Enabled</label>
              </div>
            </div>
          </div>

          <!-- Result configuration -->
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="parameters-form-label d-block mb-1">Result Rule *</label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paramResultMode" id="resultModeNumeric" value="numeric_or_ND" checked>
                  <label class="form-check-label" for="resultModeNumeric">
                    Numeric or ND
                    <small class="d-block text-muted" style="font-size:0.8rem;">User can enter a number or choose ND (Not Detected)</small>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paramResultMode" id="resultModePA" value="present_or_absent">
                  <label class="form-check-label" for="resultModePA">
                    Present / Absent
                    <small class="d-block text-muted" style="font-size:0.8rem;">Result is a simple Present or Absent</small>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-md-12">
              <div class="form-check">
                <input type="checkbox" id="paramEspc" class="form-check-input">
                <label class="form-check-label" for="paramEspc">
                  ESPC applicable for this parameter
                  <small class="d-block text-muted" style="font-size:0.8rem;">If enabled, result entry can mark values as ESPC and reports will show ESPC with the value.</small>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================= STEP 2: Units & Config ========================= -->
        <div class="step-content" data-step="2">
          <div class="step-section-title"><i class="fas fa-layer-group"></i> Category Configuration</div>
          <p style="font-size:0.85rem; color:#64748b; margin-bottom:12px;">
            Select which sample categories this parameter applies to, then configure units and detection limits for each.
          </p>

          <!-- Category Panels (always shown) -->
          <div class="category-panels" id="categoryPanels">
            <!-- Panel: WATER -->
            <div class="category-panel" data-category-id="1">
              <div class="category-panel-header" style="border-left-color:#3b82f6;">
                <div class="category-panel-title">
                  <input type="checkbox" class="form-check-input cat-checkbox" id="catCheck_1" data-cat="1">
                  <span class="cat-icon">💧</span>
                  <span class="cat-name">Water and Ice</span>
                  <span class="cat-code">WATER</span>
                </div>
              </div>
              <div class="category-panel-body" id="catBody_1" style="display:none;">
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label class="parameters-form-label">Unit *</label>
                    <select class="parameters-form-select cat-unit-select" id="catUnit_1" data-cat="1">
                      <option value="">-- Select Unit --</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate</label>
                    <select class="parameters-form-select cat-cert-select" id="catCert_1" data-cat="1" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_1">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_1" data-cat="1">
                    <label class="form-check-label" for="catSlab_1">SLAB Accredited for Water</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Water)</label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_1">
                    <!-- Populated dynamically -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel: FOOD -->
            <div class="category-panel" data-category-id="2">
              <div class="category-panel-header" style="border-left-color:#f59e0b;">
                <div class="category-panel-title">
                  <input type="checkbox" class="form-check-input cat-checkbox" id="catCheck_2" data-cat="2">
                  <span class="cat-icon">🐟</span>
                  <span class="cat-name">Food</span>
                  <span class="cat-code">FOOD</span>
                </div>
              </div>
              <div class="category-panel-body" id="catBody_2" style="display:none;">
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label class="parameters-form-label">Unit *</label>
                    <select class="parameters-form-select cat-unit-select" id="catUnit_2" data-cat="2">
                      <option value="">-- Select Unit --</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate</label>
                    <select class="parameters-form-select cat-cert-select" id="catCert_2" data-cat="2" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_2">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_2" data-cat="2">
                    <label class="form-check-label" for="catSlab_2">SLAB Accredited for Food</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Food)</label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_2">
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel: SWAB -->
            <div class="category-panel" data-category-id="3">
              <div class="category-panel-header" style="border-left-color:#16a34a;">
                <div class="category-panel-title">
                  <input type="checkbox" class="form-check-input cat-checkbox" id="catCheck_3" data-cat="3">
                  <span class="cat-icon">🧹</span>
                  <span class="cat-name">Surface Swab</span>
                  <span class="cat-code">SWAB</span>
                </div>
              </div>
              <div class="category-panel-body" id="catBody_3" style="display:none;">
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label class="parameters-form-label">Unit *</label>
                    <select class="parameters-form-select cat-unit-select" id="catUnit_3" data-cat="3">
                      <option value="">-- Select Unit --</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate</label>
                    <select class="parameters-form-select cat-cert-select" id="catCert_3" data-cat="3" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_3">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_3" data-cat="3">
                    <label class="form-check-label" for="catSlab_3">SLAB Accredited for Swab</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Swab)</label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_3">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="parameters-modal-footer-btns">
          <button type="button" class="btn btn-secondary" id="btnPrevStep" style="display:none;">
            <i class="fas fa-arrow-left"></i> Back
          </button>
          <div style="flex:1;"></div>
          <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
          <button type="button" class="btn btn-success" id="btnNextStep">
            Next <i class="fas fa-arrow-right"></i>
          </button>
          <button type="button" class="btn btn-success" id="btnSaveParam" style="display:none;">
            <i class="fas fa-save"></i> Save Parameter
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TABLE VIEW OVERLAY                         -->
  <!-- ========================================== -->
  <div class="table-overlay" id="tableOverlay">
    <div class="table-overlay-content">
      <div class="table-overlay-header">
        <h5><i class="fas fa-list-alt me-2"></i>Parameters & Methods</h5>
        <button class="btn-close-table-overlay" id="btnCloseTableOverlay">Close</button>
      </div>
      <div id="tableOverlayBody">
        <div class="table-loading">Loading...</div>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    // ========================================
    // GLOBALS & CONFIG
    // ========================================
    const API_PARAM = 'src/Controllers/parameter-controller.php';
    const API_UNIT = 'src/Controllers/base-unit-controller.php';
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    let currentStep = 1;
    let editMode = false;
    let editId = null;
    let methodMode = 'universal'; // 'universal' or 'category'
    let allMethods = [];
    let unitCache = {}; // category_id → units[]

    // ========================================
    // DOM REFERENCES
    // ========================================
    const $ = id => document.getElementById(id);
    const modal = $('paramModal');
    const form = $('paramForm');

    // ========================================
    // INIT
    // ========================================
    loadParameters();
    loadAllMethods();
    loadCertificates();

    // ========================================
    // TOAST HELPER
    // ========================================
    function showToast(message, type = 'success') {
      const colors = {
        success: 'text-bg-success',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info'
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

    // ========================================
    // LOAD PARAMETERS TABLE
    // ========================================
    function loadParameters() {
      const search = $('searchInput').value.trim();
      const status = $('statusFilter').value;

      const fd = new FormData();
      fd.append('action', 'fetchAll');
      if (search) fd.append('search', search);
      if (status !== '') fd.append('is_active', status);
      fd.append('limit', 100);

      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') return;
          const tbody = document.querySelector('#parametersTable tbody');
          if (!res.data || res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No parameters found</td></tr>';
            return;
          }
          tbody.innerHTML = res.data.map(p => {
            const statusBadge = p.is_active == 1 ?
              '<span class="badge bg-success">Active</span>' :
              '<span class="badge bg-secondary">Inactive</span>';

            const swab = p.swab_enabled == 1 ?
              '<span class="badge bg-success">Yes</span>' :
              '<span class="badge bg-secondary">No</span>';

            const slabFlag = p.is_slab_accredited == 1 ?
              '<span class="badge bg-success-subtle text-success border border-success"><i class="fas fa-check-circle me-1"></i> Accredited</span>' :
              '<span class="badge bg-light text-secondary border border-secondary">Not Accredited</span>';

            const displayName = p.display_format === 'scientific' ? `<em>${p.parameter_name}</em>` : p.parameter_name;
            const shortLabel = p.short_name ? `<br><small class="text-muted">${p.short_name}</small>` : '';

            return `<tr>
            <td class="param-name-cell">${displayName}${shortLabel}</td>
            <td class="text-center">${swab}</td>
            <td class="text-center">${slabFlag}</td>
            <td>${statusBadge}</td>
            <td class="text-center">
              <button class="btn-parameters-edit" onclick="editParam(${p.parameter_id})"><i class="fas fa-edit"></i></button>
              <button class="btn-parameters-delete" onclick="deleteParam(${p.parameter_id}, '${p.parameter_name}')"><i class="fas fa-trash"></i></button>
            </td>
          </tr>`;
          }).join('');
        });
    }

    // ========================================
    // LOAD ALL METHODS (for checkboxes)
    // ========================================
    function loadAllMethods() {
      const fd = new FormData();
      fd.append('action', 'fetchMethods');
      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            allMethods = res.data || [];
            populateCategoryMethodCheckboxes();
          }
        });
    }

    function populateCategoryMethodCheckboxes() {
      [1, 2, 3].forEach(cat => {
        const container = $(`catMethodChecks_${cat}`);
        if (!container) return;
        container.innerHTML = allMethods.map(m =>
          `<div class="form-check form-check-inline">
          <input class="form-check-input cat-method-cb" type="checkbox" 
                 value="${m.method_id}" id="catM_${cat}_${m.method_id}" data-cat="${cat}">
          <label class="form-check-label" for="catM_${cat}_${m.method_id}" style="font-size:0.8rem;">
            ${m.method_name}
          </label>
        </div>`
        ).join('');
      });
    }

    // ========================================
    // LOAD UNITS FOR CATEGORY
    // ========================================
    function loadUnitsForCategory(catId) {
      if (unitCache[catId]) {
        populateUnitDropdown(catId, unitCache[catId]);
        return Promise.resolve();
      }
      const fd = new FormData();
      fd.append('action', 'getUnitsForCategory');
      fd.append('category_id', catId);
      return fetch(API_UNIT, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            unitCache[catId] = res.data;
            populateUnitDropdown(catId, res.data);
          }
        });
    }

    // ========================================
    // SUPERSCRIPT HELPER FOR DROPDOWNS
    // ========================================
    function formatUnitSuperscript(text) {
      // Converts ^0..^9 to Unicode superscript chars
      // e.g., "cfu/cm^2" → "cfu/cm²", "10^1" → "10¹"
      const superMap = {
        '0': '⁰',
        '1': '¹',
        '2': '²',
        '3': '³',
        '4': '⁴',
        '5': '⁵',
        '6': '⁶',
        '7': '⁷',
        '8': '⁸',
        '9': '⁹'
      };
      return text.replace(/\^(\d+)/g, (_, digits) =>
        digits.split('').map(d => superMap[d] || d).join('')
      );
    }

    function populateUnitDropdown(catId, units) {
      const sel = $(`catUnit_${catId}`);
      if (!sel) return;
      const current = sel.value;
      sel.innerHTML = '<option value="">-- Select Unit --</option>' +
        units.map(u => `<option value="${u.base_unit_id}">${formatUnitSuperscript(u.unit_name)}</option>`).join('');
      if (current) sel.value = current;
    }

    // ========================================
    // LOAD CERTIFICATES FOR DROPDOWN
    // ========================================
    let allCertificates = [];

    function loadCertificates() {
      const fd = new FormData();
      fd.append('action', 'fetchCertificates');
      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            allCertificates = res.data || [];
            populateCertificateDropdowns();
          }
        });
    }

    function populateCertificateDropdowns() {
      [1, 2, 3].forEach(catId => {
        const sel = $(`catCert_${catId}`);
        if (!sel) return;
        const current = sel.value;
        sel.innerHTML = '<option value="">-- No Certificate --</option>' +
          allCertificates.map(c =>
            `<option value="${c.certificate_id}">${c.certificate_code}</option>`
          ).join('');
        if (current) sel.value = current;
      });
    }

    // ========================================
    // CATEGORY CHECKBOX TOGGLE
    // ========================================
    document.querySelectorAll('.cat-checkbox').forEach(cb => {
      cb.addEventListener('change', function() {
        const catId = this.dataset.cat;
        const body = $(`catBody_${catId}`);
        if (this.checked) {
          body.style.display = 'block';
          loadUnitsForCategory(catId);
        } else {
          body.style.display = 'none';
        }
      });
    });

    // SLAB checkbox enables/disables certificate dropdown
    document.querySelectorAll('.cat-slab-checkbox').forEach(cb => {
      cb.addEventListener('change', function() {
        const catId = this.dataset.cat;
        const certSel = $(`catCert_${catId}`);
        if (certSel) {
          certSel.disabled = !this.checked;
          if (!this.checked) certSel.value = '';
        }
      });
    });

    // ========================================
    // STEP NAVIGATION
    // ========================================
    $('btnNextStep').addEventListener('click', () => {
      // Validate step 1
      if (!$('paramName').value.trim()) {
        showToast('Parameter name is required', 'warning');
        return;
      }
      goToStep(2);
    });

    $('btnPrevStep').addEventListener('click', () => {
      if (currentStep > 1) goToStep(currentStep - 1);
    });

    function goToStep(step) {
      currentStep = step;
      // Update step content
      document.querySelectorAll('.step-content').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.step) === step);
      });
      // Update step indicators
      document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'completed');
        if (s === step) el.classList.add('active');
        else if (s < step) el.classList.add('completed');
      });
      // Update buttons
      $('btnPrevStep').style.display = step > 1 ? 'inline-block' : 'none';
      $('btnNextStep').style.display = step < 2 ? 'inline-block' : 'none';
      $('btnSaveParam').style.display = step === 2 ? 'inline-block' : 'none';
    }

    // ========================================
    // BUILD SUMMARY (Removed, no longer needed)
    // ========================================

    // ========================================
    // OPEN MODAL (New)
    // ========================================
    $('btnNewParam').addEventListener('click', () => {
      editMode = false;
      editId = null;
      $('modalTitle').innerHTML = '<i class="fas fa-flask"></i> Add Parameter';
      // Reset save button to green "Save Parameter"
      const saveBtn = $('btnSaveParam');
      saveBtn.className = 'btn btn-success';
      saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Parameter';
      form.reset();
      $('paramId').value = '';
      $('paramCode').value = '';

      // Uncheck all categories
      document.querySelectorAll('.cat-checkbox').forEach(cb => {
        cb.checked = false;
        $(`catBody_${cb.dataset.cat}`).style.display = 'none';
      });
      // Reset method checkboxes and SLAB toggles
      document.querySelectorAll('.cat-method-cb').forEach(cb => cb.checked = false);
      document.querySelectorAll('.cat-slab-checkbox').forEach(cb => cb.checked = false);
      // Reset certificate dropdowns
      document.querySelectorAll('.cat-cert-select').forEach(sel => {
        sel.value = '';
        sel.disabled = true;
      });

      goToStep(1);
      modal.classList.add('active');
    });

    // ========================================
    // CLOSE MODAL
    // ========================================
    $('btnCloseModal').addEventListener('click', closeModal);
    $('btnCancel').addEventListener('click', closeModal);
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal();
    });

    function closeModal() {
      modal.classList.remove('active');
    }

    // ========================================
    // EDIT PARAMETER
    // ========================================
    window.editParam = function(id) {
      editMode = true;
      editId = id;
      $('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Parameter';
      // Change save button to yellow "Update Parameter"
      const saveBtn = $('btnSaveParam');
      saveBtn.className = 'btn btn-warning';
      saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Parameter';

      const fd = new FormData();
      fd.append('action', 'getById');
      fd.append('parameter_id', id);

      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') {
            showToast('Error loading parameter', 'danger');
            return;
          }
          const p = res.data;

          // Step 1 fields
          $('paramId').value = p.parameter_id;
          $('paramCode').value = p.parameter_code;
          $('paramName').value = p.parameter_name;
          $('paramShortName').value = p.short_name || '';
          $('paramCategory').value = p.parameter_category || '';
          $('paramDisplayFormat').value = p.display_format || 'normal';
          $('paramStatus').value = p.is_active;
          $('paramSwab').checked = parseInt(p.swab_enabled) === 1;

          // Result config
          const resultMode = p.result_mode || 'numeric_or_ND';
          const espcFlag = parseInt(p.espc_applicable ?? 0) === 1;
          if (resultMode === 'present_or_absent') {
            $('resultModePA').checked = true;
          } else {
            $('resultModeNumeric').checked = true;
          }
          $('paramEspc').checked = espcFlag;

          // Reset all category panels
          document.querySelectorAll('.cat-checkbox').forEach(cb => {
            cb.checked = false;
            $(`catBody_${cb.dataset.cat}`).style.display = 'none';
          });
          document.querySelectorAll('.cat-method-cb').forEach(cb => cb.checked = false);
          document.querySelectorAll('.cat-slab-checkbox').forEach(cb => cb.checked = false);

          // Load category configs
          const configs = p.category_configs || [];
          let loadPromises = [];

          configs.forEach(cfg => {
            const catId = cfg.base_category_id;
            const checkbox = $(`catCheck_${catId}`);
            if (checkbox) {
              checkbox.checked = true;
              $(`catBody_${catId}`).style.display = 'block';
              loadPromises.push(
                loadUnitsForCategory(catId).then(() => {
                  $(`catUnit_${catId}`).value = cfg.base_unit_id || '';
                  $(`catCert_${catId}`).value = cfg.certificate_id || '';
                  const certSel = $(`catCert_${catId}`);
                  certSel.disabled = parseInt(cfg.is_slab_accredited) !== 1;

                  // Set SLAB and Methods
                  const slabCb = document.getElementById(`catSlab_${catId}`);
                  if (slabCb) slabCb.checked = parseInt(cfg.is_slab_accredited) === 1;

                  if (cfg.methods) {
                    cfg.methods.forEach(m => {
                      const cb = document.getElementById(`catM_${catId}_${m.method_id}`);
                      if (cb) cb.checked = true;
                    });
                  }
                })
              );
            }
          });

          goToStep(1);
          modal.classList.add('active');
        });
    };

    // ========================================
    // SAVE PARAMETER
    // ========================================
    $('btnSaveParam').addEventListener('click', saveParameter);

    function saveParameter() {
      const name = $('paramName').value.trim();
      if (!name) {
        showToast('Parameter name is required', 'warning');
        return;
      }

      const fd = new FormData();
      fd.append('action', editMode ? 'update' : 'insert');
      fd.append('csrf_token', CSRF_TOKEN);
      fd.append('parameter_name', name);
      fd.append('parameter_category', $('paramCategory').value);
      fd.append('swab_enabled', $('paramSwab').checked ? 1 : 0);
      fd.append('is_active', $('paramStatus').value);
      fd.append('short_name', $('paramShortName').value);
      fd.append('display_format', $('paramDisplayFormat').value);
      // Result config
      const selectedResultMode = document.querySelector('input[name="paramResultMode"]:checked');
      fd.append('result_mode', selectedResultMode ? selectedResultMode.value : 'numeric_or_ND');
      fd.append('espc_applicable', $('paramEspc').checked ? 1 : 0);

      if (editMode) {
        fd.append('parameter_id', editId);
        fd.append('parameter_code', $('paramCode').value);
      }

      // First save basic parameter
      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') {
            showToast(res.message || 'Failed to save', 'warning');
            return;
          }
          const paramId = editMode ? editId : res.parameter_id;

          // Now save category configs
          saveCategoryConfigs(paramId);
        })
        .catch(err => {
          showToast('Failed saving basic info: ' + err.message, 'danger');
          console.error('Basic save fetch error:', err);
        });
    }

    function saveCategoryConfigs(paramId) {
      const configs = [];
      const deletedCategories = [];

      [1, 2, 3].forEach(catId => {
        const checked = $(`catCheck_${catId}`).checked;
        if (checked) {
          const config = {
            base_category_id: catId,
            base_unit_id: $(`catUnit_${catId}`).value,
            slab_standard: '',
            certificate_id: $(`catCert_${catId}`).value || null,
            is_slab_accredited: $(`catSlab_${catId}`).checked ? 1 : 0,
            methods: []
          };

          // Per-category methods
          document.querySelectorAll(`#catMethodChecks_${catId} .cat-method-cb:checked`).forEach(cb => {
            config.methods.push(parseInt(cb.value));
          });

          configs.push(config);
        } else {
          deletedCategories.push(catId);
        }
      });

      // Validate: if any category is checked, they must have unit assigned
      if (configs.length === 0) {
        showToast('Please select at least one category', 'warning');
        return;
      }

      let valid = true;
      configs.forEach(cfg => {
        if (!cfg.base_unit_id) valid = false;
      });

      if (!valid) {
        showToast('Please assign a unit for all selected categories', 'warning');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'saveCategoryConfigs');
      fd.append('csrf_token', CSRF_TOKEN);
      fd.append('parameter_id', paramId);
      fd.append('configs', JSON.stringify(configs));
      fd.append('deleted_categories', JSON.stringify(deletedCategories));

      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') {
            showToast(res.message || 'Failed to save categories', 'danger');
            return;
          }
          closeModal();
          loadParameters();
          showToast(editMode ? 'Parameter updated successfully' : 'Parameter saved successfully', 'success');
        })
        .catch(err => {
          showToast('Failed saving categories: ' + err.message, 'danger');
          console.error('Category save fetch error:', err);
        });
    }

    // ========================================
    // DELETE PARAMETER
    // ========================================
    let deleteTargetId = null;
    let bsDeleteModal = null;

    function getDeleteModal() {
      if (!bsDeleteModal) {
        const el = document.getElementById('deleteModal');
        bsDeleteModal = new bootstrap.Modal(el);
      }
      return bsDeleteModal;
    }

    window.deleteParam = function(id, name) {
      deleteTargetId = id;
      $('deleteParamName').textContent = name;
      getDeleteModal().show();
    };

    $('btnConfirmDelete').addEventListener('click', () => {
      if (!deleteTargetId) return;
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('csrf_token', CSRF_TOKEN);
      fd.append('parameter_id', deleteTargetId);
      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          getDeleteModal().hide();
          deleteTargetId = null;
          if (res.status === 'success') {
            loadParameters();
            showToast('Parameter deleted successfully', 'danger');
          } else {
            showToast(res.message || 'Failed to delete', 'danger');
          }
        })
        .catch(err => {
          getDeleteModal().hide();
          deleteTargetId = null;
          showToast('Delete failed: ' + err.message, 'danger');
          console.error('Delete fetch error:', err);
        });
    });

    // ========================================
    // FILTER & RESET
    // ========================================
    $('btnFilter').addEventListener('click', loadParameters);
    $('btnReset').addEventListener('click', () => {
      $('searchInput').value = '';
      $('statusFilter').value = '';
      loadParameters();
    });

    $('statusFilter').addEventListener('change', () => {
      loadParameters();
    });

    let searchTimeout;
    $('searchInput').addEventListener('keyup', e => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        loadParameters();
      }, 500);
    });

    // ========================================
    // TABLE VIEW OVERLAY
    // ========================================
    $('btnTableView').addEventListener('click', () => {
      $('tableOverlay').classList.add('active');
      loadTableOverlay();
    });
    $('btnCloseTableOverlay').addEventListener('click', () => {
      $('tableOverlay').classList.remove('active');
    });

    function loadTableOverlay() {
      const fd = new FormData();
      fd.append('action', 'fetchTableView');
      fetch(API_PARAM, {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') return;
          let html = '<table class="overlay-table"><thead><tr>';
          html += '<th style="width:40px;">#</th><th>Parameter Name</th><th>Methods</th>';
          html += '</tr></thead><tbody>';
          res.data.forEach((p, i) => {
            html += `<tr>
            <td style="color:#94a3b8; font-size:0.85rem;">${i + 1}</td>
            <td style="font-weight:500;">${p.parameter_name}</td>
            <td style="font-size:0.85rem; color:#64748b;">${p.method_names || '<span style="color:#cbd5e1;">—</span>'}</td>
          </tr>`;
          });
          html += '</tbody></table>';
          $('tableOverlayBody').innerHTML = html;
        })
        .catch(err => {
          $('tableOverlayBody').innerHTML = '<p class="text-danger p-3">Failed to load data</p>';
          console.error('Table overlay error:', err);
        });
    }

  })();
</script>