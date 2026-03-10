<!-- ============================================================
   MANAGE SIGNATORIES PAGE
   Laboratory Management System
   CRUD interface for report_signatories table
   ============================================================ -->

<link rel="stylesheet" href="public/assets/css/test-report.css">

<div class="container-fluid px-4 py-4">

    <!-- ==================== PAGE HEADER ==================== -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-pen me-2"></i>Report Signatories</h4>
        <button class="btn btn-primary" onclick="SignatoryMgr.openModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Signatory
        </button>
    </div>

    <!-- ==================== INFO ALERT ==================== -->
    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Signatories appear on the final test report signature block. You need at least one <strong>Scientist</strong> (left block) and one <strong>Head</strong> (right block).
    </div>

    <!-- ==================== TABLE ==================== -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="signatoryTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">FULL NAME</th>
                            <th class="px-3 py-3">TITLE</th>
                            <th class="px-3 py-3">DIVISION</th>
                            <th class="px-3 py-3 text-center">ROLE</th>
                            <th class="px-3 py-3 text-center">DEFAULT</th>
                            <th class="px-3 py-3 text-center" style="width: 150px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="signatoryTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading signatories...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
   ADD/EDIT SIGNATORY MODAL
   ============================================================ -->
<div class="modal fade" id="signatoryModal" tabindex="-1" aria-labelledby="signatoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="signatoryModalLabel">
                    <i class="bi bi-person-plus me-2"></i><span id="sigModalTitle">Add Signatory</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="signatoryForm">
                    <input type="hidden" id="sigEditId" value="">

                    <div class="mb-3">
                        <label for="sigFullName" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sigFullName" placeholder="e.g., P. Ginigaddarage" required>
                    </div>

                    <div class="mb-3">
                        <label for="sigTitle" class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sigTitle" placeholder="e.g., Senior Scientist" required>
                    </div>

                    <div class="mb-3">
                        <label for="sigDivision" class="form-label fw-semibold">Division <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sigDivision" placeholder="e.g., Post Harvest Technology Division" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sigRoleType" class="form-label fw-semibold">Role Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="sigRoleType" required>
                                <option value="scientist">Scientist (Left Block)</option>
                                <option value="head">Head (Right Block)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sigDisplayOrder" class="form-label fw-semibold">Display Order</label>
                            <input type="number" class="form-control" id="sigDisplayOrder" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="sigIsDefault" checked>
                        <label class="form-check-label" for="sigIsDefault">
                            Set as default signatory for new reports
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSig" onclick="SignatoryMgr.save()">
                    <i class="bi bi-check-lg me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
   TOAST CONTAINER
   ============================================================ -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080" id="sigToastContainer"></div>

<!-- ============================================================
   JAVASCRIPT
   ============================================================ -->
<script>
    const SignatoryMgr = (() => {
        const API_URL = 'src/Controllers/signatory-controller.php';
        let allSignatories = [];

        function init() {
            loadSignatories();
        }

        function loadSignatories() {
            const formData = new FormData();
            formData.append('action', 'fetchAll');

            fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        allSignatories = res.data || [];
                        renderTable(allSignatories);
                    } else {
                        showToast(res.message || 'Failed to load', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Load error:', err);
                    showToast('Network error loading signatories', 'danger');
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('signatoryTableBody');

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size: 2rem; display: block; opacity: 0.5;"></i>
                No signatories found. Click "Add Signatory" to create one.
            </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map((s, idx) => {
                const roleBadge = s.role_type === 'scientist' ?
                    '<span class="badge bg-primary">Scientist</span>' :
                    '<span class="badge bg-danger">Head</span>';

                const defaultBadge = s.is_default == 1 ?
                    '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Yes</span>' :
                    '<span class="badge bg-secondary">No</span>';

                return `<tr>
                <td class="px-3 py-2">${idx + 1}</td>
                <td class="px-3 py-2 fw-semibold">${escHtml(s.full_name)}</td>
                <td class="px-3 py-2">${escHtml(s.title)}</td>
                <td class="px-3 py-2"><small>${escHtml(s.division)}</small></td>
                <td class="px-3 py-2 text-center">${roleBadge}</td>
                <td class="px-3 py-2 text-center">${defaultBadge}</td>
                <td class="px-3 py-2 text-center">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="SignatoryMgr.edit(${s.signatory_id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="SignatoryMgr.remove(${s.signatory_id}, '${escHtml(s.full_name)}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
            }).join('');
        }

        function openModal(sigId = null) {
            document.getElementById('sigEditId').value = '';
            document.getElementById('sigFullName').value = '';
            document.getElementById('sigTitle').value = '';
            document.getElementById('sigDivision').value = '';
            document.getElementById('sigRoleType').value = 'scientist';
            document.getElementById('sigDisplayOrder').value = '0';
            document.getElementById('sigIsDefault').checked = true;
            document.getElementById('sigModalTitle').textContent = 'Add Signatory';

            const modal = new bootstrap.Modal(document.getElementById('signatoryModal'));
            modal.show();
        }

        function edit(sigId) {
            const sig = allSignatories.find(s => s.signatory_id == sigId);
            if (!sig) return;

            document.getElementById('sigEditId').value = sig.signatory_id;
            document.getElementById('sigFullName').value = sig.full_name;
            document.getElementById('sigTitle').value = sig.title;
            document.getElementById('sigDivision').value = sig.division;
            document.getElementById('sigRoleType').value = sig.role_type;
            document.getElementById('sigDisplayOrder').value = sig.display_order || 0;
            document.getElementById('sigIsDefault').checked = sig.is_default == 1;
            document.getElementById('sigModalTitle').textContent = 'Edit Signatory';

            const modal = new bootstrap.Modal(document.getElementById('signatoryModal'));
            modal.show();
        }

        function save() {
            const fullName = document.getElementById('sigFullName').value.trim();
            const title = document.getElementById('sigTitle').value.trim();
            const division = document.getElementById('sigDivision').value.trim();

            if (!fullName || !title || !division) {
                showToast('Please fill all required fields', 'warning');
                return;
            }

            const editId = document.getElementById('sigEditId').value;
            const isEdit = editId && editId !== '';

            const formData = new FormData();
            formData.append('action', isEdit ? 'update' : 'create');
            if (isEdit) formData.append('signatory_id', editId);
            formData.append('full_name', fullName);
            formData.append('title', title);
            formData.append('division', division);
            formData.append('role_type', document.getElementById('sigRoleType').value);
            formData.append('display_order', document.getElementById('sigDisplayOrder').value);
            formData.append('is_default', document.getElementById('sigIsDefault').checked ? 1 : 0);

            const btn = document.getElementById('btnSaveSig');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

            fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast(res.message || 'Saved successfully', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('signatoryModal')).hide();
                        loadSignatories();
                    } else {
                        showToast(res.message || 'Save failed', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    showToast('Network error', 'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save';
                });
        }

        function remove(sigId, name) {
            if (!confirm(`Delete signatory "${name}"?\n\nThis is a soft delete — the record will be deactivated, not permanently removed.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('signatory_id', sigId);

            fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast('Signatory deleted', 'success');
                        loadSignatories();
                    } else {
                        showToast(res.message || 'Delete failed', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showToast('Network error', 'danger');
                });
        }

        // ==================== HELPERS ====================
        function escHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('sigToastContainer');
            const id = 'toast_' + Date.now();
            const html = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            const toastEl = document.getElementById(id);
            new bootstrap.Toast(toastEl).show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        return {
            init,
            openModal,
            edit,
            save,
            remove
        };
    })();

    document.addEventListener('DOMContentLoaded', () => {
        SignatoryMgr.init();
        console.log('✅ SignatoryMgr Module: Initialized');
    });
</script>