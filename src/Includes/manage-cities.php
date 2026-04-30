<?php
// src/Includes/manage-cities.php - FINAL COMPLETE VERSION
?>

<link rel="stylesheet" href="../public/assets/css/manage-cities.css">

<div class="cities-container">
    
    <!-- Statistics Cards - ONLY 3 CARDS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Cities</p>
                    <h3 class="stat-value" id="totalCities">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Pre-defined</p>
                    <h3 class="stat-value" id="predefinedCities">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">User-added</p>
                    <h3 class="stat-value" id="userAddedCities">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="cities-card-filter cities-filters">
        <input type="text" class="form-control" id="searchInput" 
               placeholder="Search cities..." style="max-width: 250px;">

        <select class="form-select" id="filterType" style="max-width: 180px;">
            <option value="all">All Cities</option>
            <option value="predefined">Pre-defined Only</option>
            <option value="user-added">User-added Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 150px;">
            <option value="usage">Sort by Usage</option>
            <option value="name">Sort by Name</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-cities-filter">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-cities-new" id="btnNewCity">
                <i class="fas fa-plus"></i> New City
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="cities-table-container">
        <table class="table cities-table">
            <thead>
                <tr>
                    <th style="width: 22%;">City Name</th>
                    <th style="width: 13%;">Usage Count</th>
                    <th style="width: 13%;">Status</th>
                    <th style="width: 18%;">Added Date</th>
                    <th style="width: 15%;">Added By</th>
                    <th style="width: 19%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="citiesTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="cities-modal-overlay" id="modalOverlay">
    <div class="cities-modal-form">
        <div class="cities-modal-header">
            <h5 id="formTitle">Add New City</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="cityForm">
            <input type="hidden" id="cityId">
            
            <div class="mb-3">
                <label class="cities-form-label">
                    City Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control cities-form-control" 
                       id="cityName" name="cityName" required 
                       placeholder="Enter city name (e.g., Colombo)">
                <small class="text-muted">Will be auto-formatted (e.g., "colombo" → "Colombo")</small>
            </div>

            <div class="cities-modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save City</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update City</button>
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
                <p>Are you sure you want to delete <strong><span id="deleteCityName"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the city.</p>
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
// ===== CITIES MANAGEMENT SCRIPT - FINAL VERSION =====

const modalOverlay = document.getElementById('modalOverlay');
const cityForm = document.getElementById('cityForm');
const btnNewCity = document.getElementById('btnNewCity');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');
const btnSave = document.getElementById('btnSave');
const btnUpdate = document.getElementById('btnUpdate');
const formTitle = document.getElementById('formTitle');
const deleteModal = document.getElementById('deleteModal');
const toastContainer = document.getElementById('toastContainer');

let deleteCityId = null;
const CONTROLLER_PATH = '../../src/Controllers/CityController.php';
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
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
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
            document.getElementById('totalCities').textContent = res.data.total || 0;
            document.getElementById('predefinedCities').textContent = res.data.predefined || 0;
            document.getElementById('userAddedCities').textContent = res.data.user_added || 0;
        }
    });
}

// === LOAD CITIES ===
function loadCities() {
    const filters = {
        search: document.getElementById('searchInput').value,
        type: document.getElementById('filterType').value,
        sort: document.getElementById('sortBy').value
    };
    
    sendAjax('fetchAll', filters).then(res => {
        const tbody = document.getElementById('citiesTableBody');
        tbody.innerHTML = '';
        
        if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
            res.data.forEach(city => {
                const statusLabel = city.is_active == 1 
                    ? '<span class="badge badge-status bg-success">Active</span>' 
                    : '<span class="badge badge-status bg-secondary">Inactive</span>';
                
                const addedByDisplay = city.created_by || 'system';
                const addedByBadge = city.created_by === 'system' 
                    ? '<span class="badge bg-secondary">system</span>'
                    : '<span class="badge bg-info">' + addedByDisplay + '</span>';
                
                tbody.insertAdjacentHTML('beforeend', `
                    <tr data-id="${city.city_id}"
                        data-name="${city.city_name}">
                        <td data-label="City Name:"><strong>${city.city_name}</strong></td>
                        <td data-label="Usage Count:"><span class="badge bg-info">${city.usage_count}</span></td>
                        <td data-label="Status:">${statusLabel}</td>
                        <td data-label="Added Date:">${city.created_at}</td>
                        <td data-label="Added By:">${addedByBadge}</td>
                        <td data-label="Actions:" style="text-align: center;">
                            <button class="btn btn-cities-edit btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-cities-delete btn-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            attachRowEvents();
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No cities found</td></tr>';
        }
    });
}

// === MODAL CONTROL ===
function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (mode === 'create') {
        cityForm.reset();
        document.getElementById('cityId').value = '';
        btnSave.classList.remove('d-none');
        btnUpdate.classList.add('d-none');
        formTitle.textContent = 'Add New City';
    } else {
        btnSave.classList.add('d-none');
        btnUpdate.classList.remove('d-none');
        formTitle.textContent = 'Update City';
    }
}

function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    cityForm.reset();
}

btnNewCity.onclick = () => openModal('create');
btnCloseModal.onclick = closeModal;
btnCancel.onclick = closeModal;
modalOverlay.onclick = e => {
    if (e.target === modalOverlay) closeModal();
};

// === INSERT CITY ===
cityForm.addEventListener('submit', e => {
    e.preventDefault();
    
    const data = {
        city_name: document.getElementById('cityName').value.trim()
    };
    
    if (data.city_name === '') {
        showToast('City name is required', 'warning');
        return;
    }
    
    sendAjax('insert', data).then(res => {
        if (res.status === 'success' || res.status === 'warning') {
            showToast(res.message || 'City added successfully!', res.status);
            if (res.status === 'success') {
                loadCities();
                loadStatistics();
                closeModal();
            }
        } else {
            showToast(res.message || 'Failed to add city', 'error');
        }
    });
});

// === ATTACH EDIT & DELETE EVENTS ===
function attachRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = e => {
            const row = e.target.closest('tr');
            openModal('edit');
            document.getElementById('cityId').value = row.dataset.id;
            document.getElementById('cityName').value = row.dataset.name;
        };
    });
    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.onclick = e => {
            const row = e.target.closest('tr');
            deleteCityId = row.dataset.id;
            document.getElementById('deleteCityName').textContent = row.dataset.name;
            new bootstrap.Modal(deleteModal).show();
        };
    });
}

// === DELETE CITY ===
document.getElementById('confirmDeleteBtn').onclick = () => {
    if (!deleteCityId) return;
    
    sendAjax('delete', { city_id: deleteCityId }).then(res => {
        if (res.status === 'success') {
            showToast('City deleted successfully!', 'success');
            loadCities();
            loadStatistics();
        } else {
            showToast(res.message || 'Failed to delete city', 'error');
        }
        
        const modal = bootstrap.Modal.getInstance(deleteModal);
        modal.hide();
        deleteCityId = null;
    });
};

// === UPDATE CITY ===
btnUpdate.onclick = () => {
    const id = document.getElementById('cityId').value;
    const data = {
        city_id: id,
        city_name: document.getElementById('cityName').value.trim()
    };
    
    if (data.city_name === '') {
        showToast('City name is required', 'warning');
        return;
    }
    
    sendAjax('update', data).then(res => {
        if (res.status === 'success' || res.status === 'warning') {
            showToast(res.message || 'City updated successfully!', res.status);
            if (res.status === 'success') {
                loadCities();
                loadStatistics();
                closeModal();
            }
        } else {
            showToast(res.message || 'Update failed', 'error');
        }
    });
};

// === SEARCH & FILTER ===
document.getElementById('searchInput').addEventListener('input', () => {
    loadCities();
});

document.getElementById('btnFilter').addEventListener('click', () => {
    loadCities();
});

document.getElementById('filterType').addEventListener('change', () => {
    loadCities();
});

document.getElementById('sortBy').addEventListener('change', () => {
    loadCities();
});

// === INITIAL LOAD ===
loadCities();
loadStatistics();
</script>