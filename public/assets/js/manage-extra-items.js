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
const itemNameInput = document.getElementById('itemName');

// === REAL-TIME FILTERING ===
itemNameInput.addEventListener('input', () => {
    itemNameInput.value = itemNameInput.value.replace(/[^a-zA-Z\s]/g, "");
});

let deleteItemId = null;
const CONTROLLER_PATH = 'src/Controllers/ExtraItemsController.php';
const CSRF_TOKEN = document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '';

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
                        <td data-label="Item Name:"><strong>${item.item_name}</strong></td>
                        <td data-label="Value:"><span class="badge bg-info">${displayValue}</span></td>
                        <td data-label="Price:"><strong>${displayPrice}</strong></td>
                        <td data-label="Description:">${description}</td>
                        <td data-label="Status:">${statusLabel}</td>
                        <td data-label="Actions:" style="text-align: center;">
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
        if (res.status === 'success' || res.status === 'warning') {
            showToast(res.message || 'Item added successfully!', res.status);
            if (res.status === 'success') {
                loadItems();
                loadStatistics();
                closeModal();
            }
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
        if (res.status === 'success' || res.status === 'warning') {
            showToast(res.message || 'Item updated successfully!', res.status);
            if (res.status === 'success') {
                loadItems();
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
