<?php

/**
 * Manage Sample Names Page
 * CRUD interface for sample names with category management
 * Uses system-matched CSS from manage-sample-names.css
 * 
 * @version 1.1
 */

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<div class="sn-container container-fluid">
    <!-- Category Stats Cards -->
    <div class="row g-3 mb-4" id="categoryStatsRow">
        <!-- Populated by JS -->
    </div>

    <!-- Filter + New (matches manage-param layout) -->
    <div class="parameters-card-filter sn-filters">
        <input type="text" id="searchInput" placeholder="Search by Sample Name" class="form-control" style="max-width:250px;">
        <select class="form-select" id="filterCategory" style="max-width:180px;">
            <option value="">All Categories</option>
        </select>
        <div class="ms-auto">
            <button class="btn-parameters-new" onclick="openAddModal()">+ New Sample Name</button>
        </div>
    </div>

    <!-- Table -->
    <div class="sn-table-container">
        <table class="sn-table" id="sampleNamesTable">
            <thead>
                <tr>
                    <th>Sample Name</th>
                    <th>Category</th>
                    <th>Accreditation</th>
                    <th style="width:100px;">Usage</th>
                    <th style="width:120px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="sampleNamesBody">
                <tr>
                    <td colspan="5" class="sn-loading">
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
<div class="modal fade sn-modal" id="sampleNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus-circle me-2"></i>Add Sample Name
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId" value="">

                <div class="mb-3">
                    <label for="sampleNameInput" class="sn-form-label">Sample Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control sn-form-control" id="sampleNameInput" placeholder="e.g. Drinking Water, Fish, Surface Swab" maxlength="200">
                    <div id="sampleNameError" class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="categorySelect" class="sn-form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select sn-form-select" id="categorySelect">
                        <option value="">Select category...</option>
                    </select>
                    <div id="categoryError" class="invalid-feedback"></div>
                    <div class="sn-category-hint" id="categoryHint"></div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="isSlabAccredited">
                    <label class="form-check-label sn-form-label" for="isSlabAccredited">SLAB Accredited</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sn-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="sn-btn-save" id="saveBtn" onclick="saveSampleName()">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade sn-modal sn-delete-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Delete <strong id="deleteName"></strong>?</p>
                <small class="text-muted">This action cannot be undone.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="sn-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="sn-btn-confirm-delete" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="sn-toast-container">
    <div id="toast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- CSRF Token for AJAX -->
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

<!-- Load External JS -->
<script src="public/assets/js/manage-sample-names.js"></script>