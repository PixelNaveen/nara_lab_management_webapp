<?php
// src/Includes/manage-extra-items.php - UPDATED WITHOUT DISPLAY_ORDER
?>

<link rel="stylesheet" href="../public/assets/css/manage-extra-items.css">

<div class="items-container">
    
    <!-- Statistics Cards -->
    <!-- <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Items</p>
                    <h3 class="stat-value" id="totalItems">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Active</p>
                    <h3 class="stat-value" id="activeItems">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-secondary">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Inactive</p>
                    <h3 class="stat-value" id="inactiveItems">0</h3>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Filter Card -->
    <div class="items-card-filter">
        <input type="text" class="form-control" id="searchInput" 
               placeholder="Search items..." style="max-width: 250px;">

        <select class="form-select" id="filterStatus" style="max-width: 150px;">
            <option value="">All Status</option>
            <option value="1">Active Only</option>
            <option value="0">Inactive Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 150px;">
            <option value="name">Sort by Name</option>
            <option value="price">Sort by Price</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-items-filter">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-items-new" id="btnNewItem">
                <i class="fas fa-plus"></i> New Item
            </button>
        </div>
    </div>

    <!-- Table - REMOVED ORDER COLUMN -->
    <div class="items-table-container">
        <table class="table items-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Item Name</th>
                    <th style="width: 15%;">Value</th>
                    <th style="width: 13%;">Price (Rs.)</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal - REMOVED DISPLAY ORDER FIELD -->
<div class="items-modal-overlay" id="modalOverlay">
    <div class="items-modal-form">
        <div class="items-modal-header">
            <h5 id="formTitle">Add New Item</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="itemForm">
            <input type="hidden" id="itemId">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="items-form-label">
                        Item Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control items-form-control" 
                           id="itemName" name="itemName" required 
                           placeholder="e.g., Water Bottle">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="items-form-label">
                        Value <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control items-form-control" 
                           id="itemValue" name="itemValue" required 
                           step="0.01" min="0.01" placeholder="500">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="items-form-label">
                        Unit <span class="text-danger">*</span>
                    </label>
                    <select class="form-select items-form-select" 
                            id="itemUnit" name="itemUnit" required>
                        <option value="">Select</option>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="piece">piece</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="items-form-label">
                    Price (Rs.) <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control items-form-control" 
                       id="itemPrice" name="itemPrice" required 
                       step="0.01" min="0.01" placeholder="50.00">
            </div>

            <div class="mb-3">
                <label class="items-form-label">Description</label>
                <textarea class="form-control items-form-control" 
                          id="itemDescription" name="itemDescription" 
                          rows="3" placeholder="Optional description"></textarea>
            </div>

            <div class="items-modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save Item</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Item</button>
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
                <p>Are you sure you want to delete <strong><span id="deleteItemName"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the item.</p>
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
// ===== EXTRA ITEMS MANAGEMENT SCRIPT - UPDATED WITHOUT DISPLAY_ORDER =====

const modalOverlay = document.getElementById('modalOverlay');
const itemForm = document.getElementById('itemForm');
const btnNewItem = document.getElementById('btnNewItem');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');
const btnSave = document.getElementById('btnSave');
const btnUpdate = document.getElementById('btnUpdate');
const formTitle = document.getElementById('formTitle');
const deleteModal = document.getElementById('deleteModal');
const toastContainer = document.getElementById('toastContainer');

let deleteItemId = null;
const CONTROLLER_PATH = '../../src/Controllers/ExtraItemsController.php';
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
            document.getElementById('totalItems').textContent = res.data.total || 0;
            document.getElementById('activeItems').textContent = res.data.active || 0;
            document.getElementById('inactiveItems').textContent = res.data.inactive || 0;
        }
    });
}

// === LOAD ITEMS ===
function loadItems() {
    const filters = {
        search: document.getElementById('searchInput').value,
        is_active: document.getElementById('filterStatus').value,
        sort: document.getElementById('sortBy').value
    };
    
    sendAjax('fetchAll', filters).then(res => {
        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = '';
        
        if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
            res.data.forEach(item => {
                const statusLabel = item.is_active == 1 
                    ? '<span class="badge badge-status bg-success">Active</span>' 
                    : '<span class="badge badge-status bg-secondary">Inactive</span>';
                
                const displayValue = `${item.item_value} ${item.item_unit}`;
                const displayPrice = `Rs. ${parseFloat(item.item_price).toFixed(2)}`;
                const description = item.item_description 
                    ? (item.item_description.length > 50 
                        ? item.item_description.substring(0, 50) + '...' 
                        : item.item_description)
                    : '<span class="text-muted">-</span>';
                
                tbody.insertAdjacentHTML('beforeend', `
                    <tr data-id="${item.item_id}"
                        data-name="${item.item_name}"
                        data-value="${item.item_value}"
                        data-unit="${item.item_unit}"
                        data-price="${item.item_price}"
                        data-description="${item.item_description || ''}">
                        <td><strong>${item.item_name}</strong></td>
                        <td><span class="badge bg-info">${displayValue}</span></td>
                        <td><strong>${displayPrice}</strong></td>
                        <td>${description}</td>
                        <td>${statusLabel}</td>
                        <td style="text-align: center;">
                            <button class="btn btn-items-edit btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-items-delete btn-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            attachRowEvents();
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No items found</td></tr>';
        }
    });
}

// === MODAL CONTROL ===
function openModal(mode) {
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (mode === 'create') {
        itemForm.reset();
        document.getElementById('itemId').value = '';
        btnSave.classList.remove('d-none');
        btnUpdate.classList.add('d-none');
        formTitle.textContent = 'Add New Item';
    } else {
        btnSave.classList.add('d-none');
        btnUpdate.classList.remove('d-none');
        formTitle.textContent = 'Update Item';
    }
}

function closeModal() {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
    itemForm.reset();
}

btnNewItem.onclick = () => openModal('create');
btnCloseModal.onclick = closeModal;
btnCancel.onclick = closeModal;
modalOverlay.onclick = e => {
    if (e.target === modalOverlay) closeModal();
};

// === INSERT ITEM ===
itemForm.addEventListener('submit', e => {
    e.preventDefault();
    
    const data = {
        item_name: document.getElementById('itemName').value.trim(),
        item_value: document.getElementById('itemValue').value,
        item_unit: document.getElementById('itemUnit').value,
        item_price: document.getElementById('itemPrice').value,
        item_description: document.getElementById('itemDescription').value.trim()
    };
    
    if (data.item_name === '') {
        showToast('Item name is required', 'warning');
        return;
    }
    
    if (parseFloat(data.item_value) <= 0) {
        showToast('Value must be greater than 0', 'warning');
        return;
    }
    
    if (data.item_unit === '') {
        showToast('Unit is required', 'warning');
        return;
    }
    
    if (parseFloat(data.item_price) <= 0) {
        showToast('Price must be greater than 0', 'warning');
        return;
    }
    
    sendAjax('insert', data).then(res => {
        if (res.status === 'success') {
            showToast(res.message || 'Item added successfully!', 'success');
            loadItems();
            loadStatistics();
            closeModal();
        } else {
            showToast(res.message || 'Failed to add item', 'error');
        }
    });
});

// === ATTACH EDIT & DELETE EVENTS ===
function attachRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = e => {
            const row = e.target.closest('tr');
            openModal('edit');
            document.getElementById('itemId').value = row.dataset.id;
            document.getElementById('itemName').value = row.dataset.name;
            document.getElementById('itemValue').value = row.dataset.value;
            document.getElementById('itemUnit').value = row.dataset.unit;
            document.getElementById('itemPrice').value = row.dataset.price;
            document.getElementById('itemDescription').value = row.dataset.description;
        };
    });
    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.onclick = e => {
            const row = e.target.closest('tr');
            deleteItemId = row.dataset.id;
            const displayName = `${row.dataset.name} (${row.dataset.value}${row.dataset.unit})`;
            document.getElementById('deleteItemName').textContent = displayName;
            new bootstrap.Modal(deleteModal).show();
        };
    });
}

// === DELETE ITEM ===
document.getElementById('confirmDeleteBtn').onclick = () => {
    if (!deleteItemId) return;
    
    sendAjax('delete', { item_id: deleteItemId }).then(res => {
        if (res.status === 'success') {
            showToast('Item deleted successfully!', 'success');
            loadItems();
            loadStatistics();
        } else {
            showToast(res.message || 'Failed to delete item', 'error');
        }
        
        const modal = bootstrap.Modal.getInstance(deleteModal);
        modal.hide();
        deleteItemId = null;
    });
};

// === UPDATE ITEM ===
btnUpdate.onclick = () => {
    const data = {
        item_id: document.getElementById('itemId').value,
        item_name: document.getElementById('itemName').value.trim(),
        item_value: document.getElementById('itemValue').value,
        item_unit: document.getElementById('itemUnit').value,
        item_price: document.getElementById('itemPrice').value,
        item_description: document.getElementById('itemDescription').value.trim()
    };
    
    if (data.item_name === '') {
        showToast('Item name is required', 'warning');
        return;
    }
    
    if (parseFloat(data.item_value) <= 0) {
        showToast('Value must be greater than 0', 'warning');
        return;
    }
    
    if (data.item_unit === '') {
        showToast('Unit is required', 'warning');
        return;
    }
    
    if (parseFloat(data.item_price) <= 0) {
        showToast('Price must be greater than 0', 'warning');
        return;
    }
    
    sendAjax('update', data).then(res => {
        if (res.status === 'success') {
            showToast('Item updated successfully!', 'success');
            loadItems();
            loadStatistics();
            closeModal();
        } else {
            showToast(res.message || 'Update failed', 'error');
        }
    });
};

// === SEARCH & FILTER ===
document.getElementById('searchInput').addEventListener('input', () => {
    loadItems();
});

document.getElementById('btnFilter').addEventListener('click', () => {
    loadItems();
});

document.getElementById('filterStatus').addEventListener('change', () => {
    loadItems();
});

document.getElementById('sortBy').addEventListener('change', () => {
    loadItems();
});

// === INITIAL LOAD ===
loadItems();
loadStatistics();
</script>