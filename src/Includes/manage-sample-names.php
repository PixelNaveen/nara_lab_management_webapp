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

<div class="sn-container">
    <!-- Category Stats Cards -->
    <div class="row g-3 mb-4" id="categoryStatsRow">
        <!-- Populated by JS -->
    </div>

    <!-- Filter + New (matches manage-param layout) -->
    <div class="parameters-card-filter">
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
                    <th style="width: 5%">#</th>
                    <th style="width: 30%">Sample Name</th>
                    <th style="width: 22%">Category</th>
                    <th style="width: 15%">Accreditation</th>
                    <th style="width: 10%">Usage</th>
                    <th style="width: 18%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="sampleNamesBody">
                <tr>
                    <td colspan="6" class="sn-loading">
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
                    <i class="bi bi-plus-circle me-2"></i>Add Sample Name
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId" value="">

                <div class="mb-3">
                    <label for="sampleNameInput" class="sn-form-label">Sample Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control sn-form-control" id="sampleNameInput" placeholder="e.g. Drinking Water, Fish, Surface Swab" maxlength="200">
                </div>

                <div class="mb-3">
                    <label for="categorySelect" class="sn-form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select sn-form-select" id="categorySelect">
                        <option value="">Select category...</option>
                    </select>
                    <div class="sn-category-hint" id="categoryHint"></div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="isSlabAccredited">
                    <label class="form-check-label sn-form-label" for="isSlabAccredited">SLAB Accredited</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sn-btn-cancel" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
                <button type="button" class="sn-btn-save" id="saveBtn" onclick="saveSampleName()">
                    <i class="bi bi-check-lg me-1"></i> Save
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
                <h5 class="modal-title"><i class="bi bi-trash3 me-2"></i>Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Delete <strong id="deleteName"></strong>?</p>
                <small class="text-muted">This action cannot be undone.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="sn-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="sn-btn-confirm-delete" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="bi bi-trash3 me-1"></i> Delete
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
    const API_URL = 'src/Controllers/sample-names-controller.php';

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
        'WAT': '💧',
        'FSH': '🐟',
        'SWB': '🧹',
        'OTH': '📦'
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
                <div class="card-body">
                    <div class="sn-stat-icon">${icon}</div>
                    <div class="sn-stat-info">
                        <h6>${s.category_name}</h6>
                        <span class="sn-stat-count">${s.name_count} sample name${s.name_count != 1 ? 's' : ''}</span>
                        <span class="sn-stat-usage">${s.total_usage} total uses</span>
                    </div>
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
            <i class="bi bi-inbox"></i>No sample names found
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
                '<span class="sn-badge-accredited"><i class="bi bi-patch-check-fill me-1"></i>Accredited</span>' :
                '<span class="sn-badge-non-slab">Non-SLAB</span>';

            return `
        <tr>
            <td>${i + 1}</td>
            <td class="fw-medium">${escapeHtml(n.sample_name)}</td>
            <td>${catBadge}</td>
            <td>${slabBadge}</td>
            <td><span class="sn-badge-usage">${n.usage_count || 0}</span></td>
            <td class="text-center">
                <button class="sn-btn-edit" onclick="openEditModal(${n.sample_name_id})" title="Edit">
                    <i class="bi bi-pencil-fill me-1"></i>Edit
                </button>
                <button class="sn-btn-delete" onclick="openDeleteModal(${n.sample_name_id}, '${escapeHtml(n.sample_name)}')" title="Delete">
                    <i class="bi bi-trash3-fill"></i>
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
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Sample Name';

        const btn = document.getElementById('saveBtn');
        btn.className = 'sn-btn-save';
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';

        new bootstrap.Modal(document.getElementById('sampleNameModal')).show();
    }

    async function openEditModal(id) {
        try {
            const res = await fetch(`${API_URL}?action=getById&id=${id}`);
            const data = await res.json();
            if (data.success) {
                document.getElementById('editId').value = data.data.sample_name_id;
                document.getElementById('sampleNameInput').value = data.data.sample_name;
                document.getElementById('categorySelect').value = data.data.category_id || '';
                document.getElementById('isSlabAccredited').checked = data.data.is_slab_accredited == 1;
                document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Update Sample Name';

                const btn = document.getElementById('saveBtn');
                btn.className = 'sn-btn-update';
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update';

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
        const name = document.getElementById('sampleNameInput').value.trim();
        const categoryId = document.getElementById('categorySelect').value;
        const isSlabAccredited = document.getElementById('isSlabAccredited').checked ? 1 : 0;

        if (!name) {
            showToast('Sample name is required', 'warning');
            return;
        }
        if (!categoryId) {
            showToast('Category is required', 'warning');
            return;
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
                showToast(data.message, 'danger');
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
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMessage');
        toast.className = `toast align-items-center border-0 text-white bg-${type}`;
        toastMsg.textContent = message;
        new bootstrap.Toast(toast, {
            delay: 3000
        }).show();
    }

    function updateCategoryHint(catId) {
        const hint = document.getElementById('categoryHint');
        const cat = categories.find(c => c.category_id == catId);
        if (cat) {
            hint.innerHTML = cat.description;
        } else {
            hint.textContent = '';
        }
    }

    document.getElementById('categorySelect')?.addEventListener('change', function() {
        updateCategoryHint(this.value);
    });
</script>