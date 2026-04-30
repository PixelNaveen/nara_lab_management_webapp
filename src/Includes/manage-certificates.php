<?php
// src/Includes/manage-certificates.php - FINAL COMPLETE VERSION
?>

<link rel="stylesheet" href="public/assets/css/manage-certificates.css">

<div class="certificates-container">

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Certificates</p>
                    <h3 class="stat-value" id="totalCertificates">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Active</p>
                    <h3 class="stat-value" id="activeCertificates">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Expiring Soon</p>
                    <h3 class="stat-value" id="expiringSoon">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Expired</p>
                    <h3 class="stat-value" id="expiredCertificates">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-filter cert-filters d-flex gap-2 align-items-center flex-wrap">
        <input type="text" class="form-control" id="searchInput"
            placeholder="Search certificates..." style="max-width: 250px;">

        <select class="form-select" id="filterStatus" style="max-width: 180px;">
            <option value="all">All Status</option>
            <option value="active">Active Only</option>
            <option value="expired">Expired Only</option>
            <option value="pending">Pending Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 160px;">
            <option value="expiry">Sort by Expiry</option>
            <option value="code">Sort by Code</option>
            <option value="name">Sort by Name</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-outline-primary">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-primary" id="btnNewCertificate">
                <i class="fas fa-plus"></i> New Certificate
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table certificatesTable">
            <thead>
                <tr>
                    <th style="width: 12%;">Code</th>
                    <th style="width: 22%;">Certificate Name</th>
                    <th style="width: 11%;">Valid From</th>
                    <th style="width: 11%;">Valid To</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Current</th>
                    <th style="width: 12%;">Expiry Status</th>
                    <th style="width: 12%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="certificatesTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-form">
        <div class="modal-header">
            <h5 id="formTitle">Add New Certificate</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="certificateForm">
            <input type="hidden" id="certificateId">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Certificate Code <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control"
                        id="certificateCode" name="certificateCode" required
                        placeholder="e.g., TL 010-01">
                    <small class="text-muted">Unique identifier</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select class="form-select"
                        id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Expired</option>
                        <option value="superseded">Superseded</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Certificate Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control"
                    id="certificateName" name="certificateName" required
                    placeholder="e.g., Microbiology Testing Accreditation 2024-2028">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Valid From <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control"
                        id="validFrom" name="validFrom" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Valid To <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control"
                        id="validTo" name="validTo" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Issued Date
                    </label>
                    <input type="date" class="form-control"
                        id="issuedDate" name="issuedDate">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Scope Description
                </label>
                <textarea class="form-control"
                    id="scopeDescription" name="scopeDescription" rows="3"
                    placeholder="What this certificate covers..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Notes
                </label>
                <textarea class="form-control"
                    id="notes" name="notes" rows="2"
                    placeholder="Additional notes..."></textarea>
            </div>

            <div class="mb-3" id="currentCheckboxContainer">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                        id="isCurrent" name="isCurrent">
                    <label class="form-check-label" for="isCurrent">
                        <strong>Set as Current Certificate</strong>
                        <small class="text-muted d-block">
                            (Will unset any other current certificate)
                        </small>
                    </label>
                </div>
            </div>

            <div class="modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save Certificate</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Certificate</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete certificate <strong><span id="deleteCertificateCode"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the certificate.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastContainer"></div>
</div>

<script>
    // ===== CERTIFICATES MANAGEMENT SCRIPT - FINAL VERSION =====

    const modalOverlay = document.getElementById('modalOverlay');
    const certificateForm = document.getElementById('certificateForm');
    const btnNewCertificate = document.getElementById('btnNewCertificate');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancel');
    const btnSave = document.getElementById('btnSave');
    const btnUpdate = document.getElementById('btnUpdate');
    const formTitle = document.getElementById('formTitle');
    const deleteModal = document.getElementById('deleteModal');
    const toastContainer = document.getElementById('toastContainer');

    let deleteCertificateId = null;
    const CONTROLLER_PATH = 'src/Controllers/CertificateController.php';
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    // === TOAST FUNCTION ===
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-success text-white',
            warning: 'bg-warning text-dark',
            danger: 'bg-danger text-white',
            error: 'bg-danger text-white'
        };

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${colors[type] || 'bg-success text-white'} border-0 mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
        `;

        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, {
            delay: 3000
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
                    csrf_token: CSRF_TOKEN,
                    ...data
                })
            })
            .then(res => res.json())
            .catch(() => ({
                status: 'error',
                message: 'Network error!'
            }));
    }

    // === LOAD STATISTICS ===
    function loadStatistics() {
        sendAjax('getStatistics', {}).then(res => {
            if (res.status === 'success') {
                document.getElementById('totalCertificates').textContent = res.data.total || 0;
                document.getElementById('activeCertificates').textContent = res.data.active || 0;
                document.getElementById('expiringSoon').textContent = res.data.expiring_soon || 0;
                document.getElementById('expiredCertificates').textContent = res.data.expired || 0;
            }
        });
    }

    // === FORMAT DATE ===
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // === GET EXPIRY STATUS BADGE ===
    function getExpiryStatusBadge(daysUntilExpiry, expiryStatus) {
        if (expiryStatus === 'expired' || daysUntilExpiry < 0) {
            return '<span class="badge bg-danger">Expired</span>';
        } else if (expiryStatus === 'critical' || daysUntilExpiry <= 30) {
            return '<span class="badge bg-danger">Critical (' + daysUntilExpiry + 'd)</span>';
        } else if (expiryStatus === 'warning' || daysUntilExpiry <= 90) {
            return '<span class="badge bg-warning">Warning (' + daysUntilExpiry + 'd)</span>';
        } else {
            return '<span class="badge bg-success">Valid (' + daysUntilExpiry + 'd)</span>';
        }
    }

    // === LOAD CERTIFICATES ===
    function loadCertificates() {
        const filters = {
            search: document.getElementById('searchInput').value,
            status: document.getElementById('filterStatus').value,
            sort: document.getElementById('sortBy').value
        };

        sendAjax('fetchAll', filters).then(res => {
            const tbody = document.getElementById('certificatesTableBody');
            tbody.innerHTML = '';

            if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                res.data.forEach(cert => {
                    const statusBadge = cert.status === 'active' ?
                        '<span class="badge bg-success">Active</span>' :
                        cert.status === 'expired' ?
                        '<span class="badge bg-danger">Expired</span>' :
                        cert.status === 'pending' ?
                        '<span class="badge bg-warning">Pending</span>' :
                        '<span class="badge bg-secondary">Superseded</span>';

                    const currentBadge = cert.is_current == 1 ?
                        '<span class="badge bg-primary"><i class="fas fa-star"></i> Current</span>' :
                        '<button class="btn btn-sm btn-outline-primary set-current-btn" title="Set as Current">Set Current</button>';

                    const expiryBadge = getExpiryStatusBadge(
                        parseInt(cert.days_until_expiry),
                        cert.expiry_status
                    );

                    tbody.insertAdjacentHTML('beforeend', `
                    <tr data-id="${cert.certificate_id}"
                        data-code="${cert.certificate_code}"
                        data-name="${cert.certificate_name}"
                        data-from="${cert.valid_from}"
                        data-to="${cert.valid_to}"
                        data-issued="${cert.issued_date || ''}"
                        data-status="${cert.status}"
                        data-scope="${(cert.scope_description || '').replace(/"/g, '&quot;')}"
                        data-notes="${(cert.notes || '').replace(/"/g, '&quot;')}"
                        data-current="${cert.is_current}">
                        <td data-label="Code:"><strong>${cert.certificate_code}</strong></td>
                        <td data-label="Certificate Name:">${cert.certificate_name}</td>
                        <td data-label="Valid From:">${formatDate(cert.valid_from)}</td>
                        <td data-label="Valid To:">${formatDate(cert.valid_to)}</td>
                        <td data-label="Status:">${statusBadge}</td>
                        <td data-label="Current:">${currentBadge}</td>
                        <td data-label="Expiry Status:">${expiryBadge}</td>
                        <td data-label="Actions:" style="text-align: center;">
                            <button class="btn btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-delete" 
                                    title="Delete" ${cert.is_current == 1 ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                });
                attachRowEvents();
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No certificates found</td></tr>';
            }
        });
    }

    // === MODAL CONTROL ===
    function openModal(mode) {
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (mode === 'create') {
            certificateForm.reset();
            document.getElementById('certificateId').value = '';
            btnSave.classList.remove('d-none');
            btnUpdate.classList.add('d-none');
            formTitle.textContent = 'Add New Certificate';
            document.getElementById('currentCheckboxContainer').classList.remove('d-none');
        } else {
            btnSave.classList.add('d-none');
            btnUpdate.classList.remove('d-none');
            formTitle.textContent = 'Update Certificate';
            document.getElementById('currentCheckboxContainer').classList.add('d-none');
        }
    }

    function closeModal() {
        modalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
        certificateForm.reset();
    }

    btnNewCertificate.onclick = () => openModal('create');
    btnCloseModal.onclick = closeModal;
    btnCancel.onclick = closeModal;
    modalOverlay.onclick = e => {
        if (e.target === modalOverlay) closeModal();
    };

    // === INSERT CERTIFICATE ===
    certificateForm.addEventListener('submit', e => {
        e.preventDefault();

        const data = {
            certificate_code: document.getElementById('certificateCode').value.trim(),
            certificate_name: document.getElementById('certificateName').value.trim(),
            valid_from: document.getElementById('validFrom').value,
            valid_to: document.getElementById('validTo').value,
            issued_date: document.getElementById('issuedDate').value,
            status: document.getElementById('status').value,
            scope_description: document.getElementById('scopeDescription').value.trim(),
            notes: document.getElementById('notes').value.trim()
        };

        if (document.getElementById('isCurrent').checked) {
            data.is_current = '1';
        }

        if (data.certificate_code === '') {
            showToast('Certificate code is required', 'warning');
            return;
        }

        if (data.certificate_name === '') {
            showToast('Certificate name is required', 'warning');
            return;
        }

        if (data.valid_from === '' || data.valid_to === '') {
            showToast('Valid from and to dates are required', 'warning');
            return;
        }

        sendAjax('insert', data).then(res => {
            if (res.status === 'success') {
                showToast(res.message || 'Certificate added successfully!', 'success');
                loadCertificates();
                loadStatistics();
                closeModal();
            } else {
                showToast(res.message || 'Failed to add certificate', 'error');
            }
        });
    });

    // === ATTACH EDIT, DELETE, SET CURRENT EVENTS ===
    function attachRowEvents() {
        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.onclick = e => {
                const row = e.target.closest('tr');
                openModal('edit');

                document.getElementById('certificateId').value = row.dataset.id;
                document.getElementById('certificateCode').value = row.dataset.code;
                document.getElementById('certificateName').value = row.dataset.name;
                document.getElementById('validFrom').value = row.dataset.from;
                document.getElementById('validTo').value = row.dataset.to;
                document.getElementById('issuedDate').value = row.dataset.issued;
                document.getElementById('status').value = row.dataset.status;
                document.getElementById('scopeDescription').value = row.dataset.scope.replace(/&quot;/g, '"');
                document.getElementById('notes').value = row.dataset.notes.replace(/&quot;/g, '"');
            };
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.onclick = e => {
                const row = e.target.closest('tr');
                deleteCertificateId = row.dataset.id;
                document.getElementById('deleteCertificateCode').textContent = row.dataset.code;
                new bootstrap.Modal(deleteModal).show();
            };
        });

        // Set Current buttons
        document.querySelectorAll('.set-current-btn').forEach(btn => {
            btn.onclick = e => {
                const row = e.target.closest('tr');
                const certificateId = row.dataset.id;
                const certificateCode = row.dataset.code;

                if (confirm(`Set "${certificateCode}" as the current certificate?\n\nThis will unset any other current certificate.`)) {
                    sendAjax('setAsCurrent', {
                        certificate_id: certificateId
                    }).then(res => {
                        if (res.status === 'success') {
                            showToast('Certificate set as current successfully!', 'success');
                            loadCertificates();
                            loadStatistics();
                        } else {
                            showToast(res.message || 'Failed to set as current', 'error');
                        }
                    });
                }
            };
        });
    }

    // === DELETE CERTIFICATE ===
    document.getElementById('confirmDeleteBtn').onclick = () => {
        if (!deleteCertificateId) return;

        sendAjax('delete', {
            certificate_id: deleteCertificateId
        }).then(res => {
            if (res.status === 'success') {
                showToast('Certificate deleted successfully!', 'success');
                loadCertificates();
                loadStatistics();
            } else {
                showToast(res.message || 'Failed to delete certificate', 'error');
            }

            const modal = bootstrap.Modal.getInstance(deleteModal);
            modal.hide();
            deleteCertificateId = null;
        });
    };

    // === UPDATE CERTIFICATE ===
    btnUpdate.onclick = () => {
        const id = document.getElementById('certificateId').value;
        const data = {
            certificate_id: id,
            certificate_code: document.getElementById('certificateCode').value.trim(),
            certificate_name: document.getElementById('certificateName').value.trim(),
            valid_from: document.getElementById('validFrom').value,
            valid_to: document.getElementById('validTo').value,
            issued_date: document.getElementById('issuedDate').value,
            status: document.getElementById('status').value,
            scope_description: document.getElementById('scopeDescription').value.trim(),
            notes: document.getElementById('notes').value.trim()
        };

        if (data.certificate_code === '') {
            showToast('Certificate code is required', 'warning');
            return;
        }

        if (data.certificate_name === '') {
            showToast('Certificate name is required', 'warning');
            return;
        }

        sendAjax('update', data).then(res => {
            if (res.status === 'success') {
                showToast('Certificate updated successfully!', 'success');
                loadCertificates();
                loadStatistics();
                closeModal();
            } else {
                showToast(res.message || 'Update failed', 'error');
            }
        });
    };

    // === SEARCH & FILTER ===
    document.getElementById('searchInput').addEventListener('input', () => {
        loadCertificates();
    });

    document.getElementById('btnFilter').addEventListener('click', () => {
        loadCertificates();
    });

    document.getElementById('filterStatus').addEventListener('change', () => {
        loadCertificates();
    });

    document.getElementById('sortBy').addEventListener('change', () => {
        loadCertificates();
    });

    // === INITIAL LOAD ===
    loadCertificates();
    loadStatistics();
</script>