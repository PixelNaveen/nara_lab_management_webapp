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

<script>
    const CSRF_TOKEN = '<?= $csrfToken ?>';
    const API_URL = 'src/Controllers/SampleNamesController.php';

    let allNames = [];
    let categories = [];
    let deleteId = null;

    const CAT_CLASSES = {
        'WAT': 'cat-water',
        'FSH': 'cat-fish',
        'SWB': 'cat-swab',
        'OTH': 'cat-other'
    };
    const CAT_ICONS = {
        'WAT': '<i class="fas fa-tint"></i>',
        'FSH': '<i class="fas fa-fish"></i>',
        'SWB': '<i class="fas fa-broom"></i>',
        'OTH': '<i class="fas fa-box"></i>'
    };

    // =================== INIT ===================
    document.addEventListener('DOMContentLoaded', () => {
        loadCategories().then(() => {
            loadSampleNames();
            loadCategoryStats();
        });

        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterTable, 300);
        });

        document.getElementById('filterCategory').addEventListener('change', filterTable);
    });

    // =================== LOAD CATEGORIES ===================
    async function loadCategories() {
        try {
            const res = await fetch(`${API_URL}?action=getCategories`);
            const data = await res.json();
            if (data.success) {
                categories = data.data;
                populateCategoryDropdowns();
            }
        } catch (err) {
            console.error('Failed to load categories:', err);
        }
    }

    function populateCategoryDropdowns() {
        const select = document.getElementById('categorySelect');
        select.innerHTML = '<option value="">Select category...</option>';
        categories.forEach(cat => {
            select.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
        });

        const filter = document.getElementById('filterCategory');
        filter.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(cat => {
            filter.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
        });
    }

    // =================== LOAD CATEGORY STATS ===================
    async function loadCategoryStats() {
        try {
            const res = await fetch(`${API_URL}?action=getCategoryStats`);
            const data = await res.json();
            if (data.success) renderCategoryStats(data.data);
        } catch (err) {
            console.error('Failed to load stats:', err);
        }
    }

    function renderCategoryStats(stats) {
        const row = document.getElementById('categoryStatsRow');
        row.innerHTML = stats.map(s => {
            const catClass = CAT_CLASSES[s.category_code] || 'cat-other';
            const icon = CAT_ICONS[s.category_code] || '📦';
            return `
        <div class="col-xl-3 col-sm-6">
            <div class="sn-stat-card ${catClass}">
                <div class="sn-stat-icon-wrapper">${icon}</div>
                <div class="sn-stat-details">
                    <h6>${s.category_name}</h6>
                    <div class="sn-stat-value">${s.name_count}</div>
                    <span class="sn-stat-usage">${s.total_usage} uses</span>
                </div>
            </div>
        </div>`;
        }).join('');
    }

    // =================== LOAD SAMPLE NAMES ===================
    async function loadSampleNames() {
        try {
            const res = await fetch(`${API_URL}?action=getAll`);
            const data = await res.json();
            if (data.success) {
                allNames = data.data;
                filterTable();
            } else {
                showToast(data.message || 'Failed to load', 'danger');
            }
        } catch (err) {
            showToast('Network error loading sample names', 'danger');
        }
    }

    // =================== FILTER & RENDER ===================
    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        const catFilter = document.getElementById('filterCategory').value;

        let filtered = allNames.filter(n => {
            const matchSearch = !search || n.sample_name.toLowerCase().includes(search) ||
                (n.category_name && n.category_name.toLowerCase().includes(search));
            const matchCat = !catFilter || n.category_id == catFilter;
            return matchSearch && matchCat;
        });

        renderTable(filtered);
    }

    function renderTable(names) {
        const tbody = document.getElementById('sampleNamesBody');

        if (names.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="sn-empty-state">
            <i class="fas fa-inbox"></i>No sample names found
        </td></tr>`;
            return;
        }

        tbody.innerHTML = names.map((n, i) => {
            const cat = categories.find(c => c.category_id == n.category_id);
            const catCode = cat ? cat.category_code : '';
            const catClass = CAT_CLASSES[catCode] || 'cat-none';

            const catBadge = n.category_name ?
                `<span class="sn-badge-category ${catClass}">${n.category_name}</span>` :
                '<span class="sn-badge-category cat-none">Uncategorized</span>';

            const slabBadge = n.is_slab_accredited == 1 ?
                '<span class="sn-badge-accredited"><i class="fas fa-check-circle me-1"></i>Accredited</span>' :
                '<span class="sn-badge-non-slab">Non-SLAB</span>';

            return `
        <tr>
            <td data-label="Sample Name:" class="sn-sample-name-cell">${escapeHtml(n.sample_name)}</td>
            <td data-label="Category:">${catBadge}</td>
            <td data-label="Accreditation:">${slabBadge}</td>
            <td data-label="Usage:"><span class="sn-badge-usage">${n.usage_count || 0}</span></td>
            <td data-label="Actions:" class="text-center">
                <button class="sn-btn-edit" onclick="openEditModal(${n.sample_name_id})" title="Edit Sample Name">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="sn-btn-delete" onclick="openDeleteModal(${n.sample_name_id}, '${escapeHtml(n.sample_name)}')" title="Delete Sample Name">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
        }).join('');
    }

    // =================== MODAL ACTIONS ===================
    function openAddModal() {
        document.getElementById('editId').value = '';
        document.getElementById('sampleNameInput').value = '';
        document.getElementById('categorySelect').value = '';
        document.getElementById('isSlabAccredited').checked = false;
        document.getElementById('categoryHint').textContent = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Add Sample Name';

        const btn = document.getElementById('saveBtn');
        btn.className = 'sn-btn-save';
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';

        new bootstrap.Modal(document.getElementById('sampleNameModal')).show();
    }

    let initialFormState = null;

    async function openEditModal(id) {
        try {
            const res = await fetch(`${API_URL}?action=getById&id=${id}`);
            const data = await res.json();
            if (data.success) {
                document.getElementById('editId').value = data.data.sample_name_id;
                document.getElementById('sampleNameInput').value = data.data.sample_name;
                document.getElementById('categorySelect').value = data.data.category_id || '';
                document.getElementById('isSlabAccredited').checked = data.data.is_slab_accredited == 1;
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Update Sample Name';

                const btn = document.getElementById('saveBtn');
                btn.className = 'sn-btn-update';
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Update';

                // Save initial state for change detection
                initialFormState = {
                    name: data.data.sample_name,
                    categoryId: data.data.category_id || '',
                    isSlabAccredited: data.data.is_slab_accredited == 1
                };

                // Trigger category hint
                updateCategoryHint(data.data.category_id);

                new bootstrap.Modal(document.getElementById('sampleNameModal')).show();
            } else {
                showToast(data.message, 'danger');
            }
        } catch (err) {
            showToast('Failed to load sample name', 'danger');
        }
    }

    function openDeleteModal(id, name) {
        deleteId = id;
        document.getElementById('deleteName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // =================== SAVE ===================
    async function saveSampleName() {
        const id = document.getElementById('editId').value;
        const nameInput = document.getElementById('sampleNameInput');
        const categorySelect = document.getElementById('categorySelect');
        const name = nameInput.value.trim();
        const categoryId = categorySelect.value;
        const isSlabAccredited = document.getElementById('isSlabAccredited').checked ? 1 : 0;

        // Clear previous state
        clearErrors();

        let hasError = false;

        // Basic frontend validation
        if (!name) {
            showInputError(nameInput, 'sampleNameError', 'Sample name is required');
            hasError = true;
        } else if (name.length < 2) {
            showInputError(nameInput, 'sampleNameError', 'Sample name must be at least 2 characters');
            hasError = true;
        } else {
            // Regex: Letters, spaces, hyphens, and parentheses (NO numbers)
            const nameRegex = /^[a-zA-Z\s\-\(\)]+$/;
            if (!nameRegex.test(name)) {
                showInputError(nameInput, 'sampleNameError', 'Only letters, spaces, hyphens, and parentheses are allowed');
                hasError = true;
            }
        }

        if (!categoryId) {
            showInputError(categorySelect, 'categoryError', 'Category is required');
            hasError = true;
        }

        if (hasError) return;

        // Change detection for updates
        if (id && initialFormState) {
            const isChanged =
                name !== initialFormState.name ||
                categoryId != initialFormState.categoryId ||
                (isSlabAccredited == 1) !== initialFormState.isSlabAccredited;

            if (!isChanged) {
                showToast('No changes detected', 'warning');
                return;
            }
        }

        const formData = new FormData();
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('sample_name', name);
        formData.append('category_id', categoryId);
        formData.append('is_slab_accredited', isSlabAccredited);

        if (id) {
            formData.append('action', 'update');
            formData.append('sample_name_id', id);
        } else {
            formData.append('action', 'insert');
        }

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('sampleNameModal')).hide();
                loadSampleNames();
                loadCategoryStats();
            } else {
                // Handle backend validation errors with labels if possible
                if (data.type === 'duplicate') {
                    showInputError(nameInput, 'sampleNameError', data.message);
                } else if (data.message.includes('name')) {
                    showInputError(nameInput, 'sampleNameError', data.message);
                } else if (data.message.includes('Category')) {
                    showInputError(categorySelect, 'categoryError', data.message);
                } else {
                    const toastType = (data.type === 'warning') ? 'warning' : 'danger';
                    showToast(data.message, toastType);
                }
            }
        } catch (err) {
            showToast('Network error', 'danger');
        }
    }

    // =================== DELETE ===================
    async function confirmDelete() {
        if (!deleteId) return;

        const formData = new FormData();
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('action', 'delete');
        formData.append('sample_name_id', deleteId);

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                loadSampleNames();
                loadCategoryStats();
            } else {
                showToast(data.message, 'danger');
            }
        } catch (err) {
            showToast('Network error', 'danger');
        }
        deleteId = null;
    }

    // =================== HELPERS ===================
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMessage');
        const textColor = (type === 'warning') ? 'text-dark' : 'text-white';
        toast.className = `toast align-items-center border-0 ${textColor} bg-${type}`;
        toastMsg.textContent = message;
        new bootstrap.Toast(toast, {
            delay: 3000
        }).show();
    }

    function updateCategoryHint(catId) {
        // Disabled description display as per request
        const hint = document.getElementById('categoryHint');
        if (hint) hint.textContent = '';
    }

    function showInputError(input, errorId, message) {
        input.classList.add('is-invalid');
        const errorDiv = document.getElementById(errorId);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    function validateSampleNameLive() {
        const input = document.getElementById('sampleNameInput');
        const name = input.value.trim();
        const errorId = 'sampleNameError';
        
        // Reset state
        input.classList.remove('is-invalid');
        const errDiv = document.getElementById(errorId);
        if (errDiv) errDiv.style.display = 'none';

        if (!name) {
            showInputError(input, errorId, 'Sample name is required');
            return false;
        } else if (name.length < 2) {
            showInputError(input, errorId, 'Sample name must be at least 2 characters');
            return false;
        } else {
            const nameRegex = /^[a-zA-Z\s\-\(\)]+$/;
            if (!nameRegex.test(name)) {
                showInputError(input, errorId, 'Only letters, spaces, hyphens, and parentheses are allowed');
                return false;
            }
        }
        return true;
    }

    function validateCategoryLive() {
        const select = document.getElementById('categorySelect');
        const catId = select.value;
        const errorId = 'categoryError';

        // Reset state
        select.classList.remove('is-invalid');
        const errDiv = document.getElementById(errorId);
        if (errDiv) errDiv.style.display = 'none';

        if (!catId) {
            showInputError(select, errorId, 'Category is required');
            return false;
        }
        return true;
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        ['sampleNameError', 'categoryError'].forEach(id => {
            const div = document.getElementById(id);
            if (div) {
                div.textContent = '';
                div.style.display = 'none';
            }
        });
    }

    // Live Validation Listeners
    document.getElementById('sampleNameInput').addEventListener('input', validateSampleNameLive);
    
    document.getElementById('categorySelect').addEventListener('change', function() {
        validateCategoryLive();
        updateCategoryHint(this.value);
    });

    // Reset errors when modal hidden
    document.getElementById('sampleNameModal').addEventListener('hidden.bs.modal', clearErrors);
</script>