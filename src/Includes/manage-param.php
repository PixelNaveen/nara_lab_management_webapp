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
              <label class="parameters-form-label">Parameter Name <span class="text-danger">*</span></label>
              <input type="text" id="paramName" class="form-control" placeholder="e.g. Coliforms" required>
              <div class="invalid-feedback" id="paramNameError"></div>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Short Name</label>
              <input type="text" id="paramShortName" class="form-control" placeholder="e.g. Coliforms">
              <div class="invalid-feedback" id="paramShortNameError"></div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="parameters-form-label">Category <span class="text-danger">*</span></label>
              <select id="paramCategory" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Microbiology">Microbiology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="General">General</option>
              </select>
              <div class="invalid-feedback" id="paramCategoryError"></div>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Display Format <span class="text-danger">*</span></label>
              <select id="paramDisplayFormat" class="form-select" required>
                <option value="">-- Select Format --</option>
                <option value="normal">Normal</option>
                <option value="scientific">Scientific (Italic)</option>
                <option value="superscript">Superscript</option>
              </select>
              <div class="invalid-feedback" id="paramDisplayFormatError"></div>
            </div>
            <div class="col-md-4">
              <label class="parameters-form-label">Status <span class="text-danger">*</span></label>
              <select id="paramStatus" class="form-select" required>
                <option value="">-- Select Status --</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
              <div class="invalid-feedback" id="paramStatusError"></div>
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
              <label class="parameters-form-label d-block mb-1">Result Rule <span class="text-danger">*</span></label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="paramResultMode" id="resultModeNumeric" value="numeric_or_ND">
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
              <div class="invalid-feedback" id="paramResultModeError" style="display:none; color: var(--bs-form-invalid-color); font-size: .875em; margin-top: .25rem;"></div>
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
                    <label class="parameters-form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select cat-unit-select" id="catUnit_1" data-cat="1">
                      <option value="">-- Select Unit --</option>
                    </select>
                    <div class="invalid-feedback" id="catUnitError_1"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate <span class="text-danger d-none slab-cert-req-1">*</span></label>
                    <select class="form-select cat-cert-select" id="catCert_1" data-cat="1" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                    <div class="invalid-feedback" id="catCertError_1"></div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;"><i class="fas fa-info-circle"></i> Check "SLAB Accredited" below to enable.</small>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_1">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_1" data-cat="1">
                    <label class="form-check-label" for="catSlab_1">SLAB Accredited for Water</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Water) <span class="text-danger">*</span></label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_1" style="border: 1px solid transparent; padding: 5px; border-radius: 4px;">
                    <!-- Populated dynamically -->
                  </div>
                  <div class="invalid-feedback" id="catMethodError_1" style="display:none; color: var(--bs-form-invalid-color); font-size: .875em;"></div>
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
                    <label class="parameters-form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select cat-unit-select" id="catUnit_2" data-cat="2">
                      <option value="">-- Select Unit --</option>
                    </select>
                    <div class="invalid-feedback" id="catUnitError_2"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate <span class="text-danger d-none slab-cert-req-2">*</span></label>
                    <select class="form-select cat-cert-select" id="catCert_2" data-cat="2" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                    <div class="invalid-feedback" id="catCertError_2"></div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;"><i class="fas fa-info-circle"></i> Check "SLAB Accredited" below to enable.</small>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_2">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_2" data-cat="2">
                    <label class="form-check-label" for="catSlab_2">SLAB Accredited for Food</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Food) <span class="text-danger">*</span></label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_2" style="border: 1px solid transparent; padding: 5px; border-radius: 4px;">
                  </div>
                  <div class="invalid-feedback" id="catMethodError_2" style="display:none; color: var(--bs-form-invalid-color); font-size: .875em;"></div>
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
                    <label class="parameters-form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select cat-unit-select" id="catUnit_3" data-cat="3">
                      <option value="">-- Select Unit --</option>
                    </select>
                    <div class="invalid-feedback" id="catUnitError_3"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="parameters-form-label">SLAB Certificate <span class="text-danger d-none slab-cert-req-3">*</span></label>
                    <select class="form-select cat-cert-select" id="catCert_3" data-cat="3" disabled>
                      <option value="">-- No Certificate --</option>
                    </select>
                    <div class="invalid-feedback" id="catCertError_3"></div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;"><i class="fas fa-info-circle"></i> Check "SLAB Accredited" below to enable.</small>
                  </div>
                </div>
                <!-- Per-category methods & SLAB -->
                <div class="cat-methods-section mt-3" id="catMethods_3">
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input cat-slab-checkbox" id="catSlab_3" data-cat="3">
                    <label class="form-check-label" for="catSlab_3">SLAB Accredited for Swab</label>
                  </div>
                  <label class="parameters-form-label mt-2">Methods (Swab) <span class="text-danger">*</span></label>
                  <div class="cat-method-checkboxes" id="catMethodChecks_3" style="border: 1px solid transparent; padding: 5px; border-radius: 4px;">
                  </div>
                  <div class="invalid-feedback" id="catMethodError_3" style="display:none; color: var(--bs-form-invalid-color); font-size: .875em;"></div>
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

<!-- CSRF Token for AJAX -->
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

<!-- Load External JS -->
<script src="public/assets/js/manage-param.js"></script>