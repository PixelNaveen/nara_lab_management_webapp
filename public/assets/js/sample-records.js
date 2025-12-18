/**
 * Sample Status Management System - FIXED VERSION
 * Handles AJAX operations, filtering, searching, and status updates
 * Bug Fixes: Path issues, error handling, UI updates
 */

// ✅ FIXED: Use correct controller path from view
// This will be set by the view file
const CONTROLLER_PATH = CONTROLLER_PATH || 'src/Controllers/sample-records-controller.php';

// Global variables
let currentFilter = 'all';
let currentSearch = '';
let isUpdating = false;
let searchTimeout = null;
let lastUpdateTime = null;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sample Status Management - Initializing...');
    console.log('📍 Controller Path:', CONTROLLER_PATH);
    
    // Check for required dependencies
    if (typeof bootstrap === 'undefined') {
        console.error('❌ Bootstrap JS not loaded!');
        showErrorMessage('Bootstrap JS is required but not loaded.');
        return;
    }
    
    initializeFilters();
    initializeSearch();
    initializeRefresh();
    loadSamples();
});

/**
 * Initialize filter pills
 */
function initializeFilters() {
    console.log('🔧 Initializing filters...');
    const filterPills = document.querySelectorAll('.filter-pill');
    
    if (filterPills.length === 0) {
        console.error('❌ Filter pills not found!');
        return;
    }
    
    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            // Remove active class from all pills
            filterPills.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked pill
            this.classList.add('active');
            
            // Update current filter
            currentFilter = this.getAttribute('data-status');
            console.log('🔍 Filter changed to:', currentFilter);
            
            // Load samples with new filter
            loadSamples();
        });
    });
    
    console.log('✅ Filters initialized');
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    console.log('🔧 Initializing search...');
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    
    if (!searchInput) {
        console.error('❌ Search input not found!');
        return;
    }
    
    // Search input with debounce
    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        // Show/hide clear button
        if (value.length > 0) {
            clearBtn.style.display = 'flex';
        } else {
            clearBtn.style.display = 'none';
        }
        
        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = value;
            console.log('🔎 Search query:', currentSearch);
            loadSamples();
        }, 500);
    });
    
    // Clear search button
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            currentSearch = '';
            this.style.display = 'none';
            console.log('🧹 Search cleared');
            loadSamples();
        });
    }
    
    console.log('✅ Search initialized');
}

/**
 * Initialize refresh button
 */
function initializeRefresh() {
    console.log('🔧 Initializing refresh button...');
    const refreshBtn = document.getElementById('refreshSamples');
    
    if (!refreshBtn) {
        console.error('❌ Refresh button not found!');
        return;
    }
    
    refreshBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (icon) {
            icon.classList.add('rotating');
        }
        
        console.log('🔄 Manual refresh triggered');
        loadSamples().finally(() => {
            if (icon) {
                icon.classList.remove('rotating');
            }
        });
    });
    
    console.log('✅ Refresh button initialized');
}

/**
 * Load samples from server
 */
function loadSamples() {
    console.log('📥 Loading samples...', { filter: currentFilter, search: currentSearch });
    const tbody = document.getElementById('samplesTableBody');
    
    if (!tbody) {
        console.error('❌ Table body not found!');
        return Promise.reject('Table body not found');
    }
    
    // Show loading state
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading samples...</p>
            </td>
        </tr>
    `;
    
    // Build request URL
    const requestBody = `action=fetchAll&statusFilter=${encodeURIComponent(currentFilter)}&searchTerm=${encodeURIComponent(currentSearch)}`;
    console.log('📤 Request:', requestBody);
    
    // AJAX request
    return fetch(CONTROLLER_PATH, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: requestBody
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('📦 Data received:', data);
        
        if (data.status === 'success') {
            renderTable(data.data);
            updateCounts(data.counts);
            updateLastUpdateTime();
            console.log('✅ Samples loaded successfully');
        } else {
            console.error('❌ Server error:', data.message);
            showToast(data.message || 'Failed to load samples', 'error');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <h5>Error Loading Data</h5>
                            <p class="text-danger">${escapeHtml(data.message || 'Please try again')}</p>
                            <button class="btn btn-primary mt-3" onclick="loadSamples()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Retry
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('💥 Network error:', error);
        showToast('Network error. Please check your connection.', 'error');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-wifi-off text-danger"></i>
                        <h5>Connection Error</h5>
                        <p class="text-muted">Unable to connect to server</p>
                        <small class="text-danger d-block mb-3">${escapeHtml(error.message)}</small>
                        <button class="btn btn-primary" onclick="loadSamples()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
}

/**
 * Render table rows
 */
function renderTable(samples) {
    console.log('🎨 Rendering table with', samples.length, 'samples');
    const tbody = document.getElementById('samplesTableBody');
    const totalCount = document.getElementById('totalSamplesCount');
    
    // Update total count
    if (totalCount) {
        totalCount.innerHTML = `<i class="bi bi-file-earmark-text me-1"></i>${samples.length} Sample${samples.length !== 1 ? 's' : ''}`;
    }
    
    // Check if empty
    if (samples.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>No Samples Found</h5>
                        <p>${currentSearch ? 'Try adjusting your search terms' : 'No samples match the selected filter'}</p>
                        ${currentSearch ? '<button class="btn btn-outline-primary mt-3" onclick="document.getElementById(\'searchInput\').value=\'\'; document.getElementById(\'clearSearch\').click();">Clear Search</button>' : ''}
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Render rows
    tbody.innerHTML = samples.map(sample => renderTableRow(sample)).join('');
    
    // Attach event listeners to dropdowns
    attachStatusDropdownListeners();
    
    console.log('✅ Table rendered successfully');
}

/**
 * Render single table row
 */
function renderTableRow(sample) {
    const statusClass = getStatusClass(sample.status);
    const formattedAmount = formatCurrency(sample.grand_total);
    const formattedDate = formatDate(sample.received_date);
    
    return `
        <tr data-sample-id="${sample.sample_id}">
            <td class="px-3 py-3">
                <span class="text-primary fw-semibold">${escapeHtml(sample.sample_code)}</span>
            </td>
            <td class="px-3 py-3">
                <span class="text-dark">${escapeHtml(sample.client_name)}</span>
            </td>
            <td class="px-3 py-3 text-end">
                <span class="currency">Rs. ${formattedAmount}</span>
            </td>
            <td class="px-3 py-3">
                <select class="status-dropdown ${statusClass}" 
                        data-sample-id="${sample.sample_id}"
                        data-current-status="${escapeHtml(sample.status)}"
                        title="Click to change status">
                    <option value="Pending" ${sample.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="In Progress" ${sample.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                    <option value="Completed" ${sample.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    <option value="Cancelled" ${sample.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
            <td class="px-3 py-3 text-muted">
                <i class="bi bi-calendar-event me-1"></i>${formattedDate}
            </td>
        </tr>
    `;
}

/**
 * Attach event listeners to status dropdowns
 */
function attachStatusDropdownListeners() {
    const dropdowns = document.querySelectorAll('.status-dropdown');
    console.log('🔗 Attaching listeners to', dropdowns.length, 'dropdowns');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            handleStatusChange(this);
        });
    });
}

/**
 * Handle status change
 */
function handleStatusChange(selectElement) {
    if (isUpdating) {
        console.warn('⚠️ Update already in progress');
        return;
    }
    
    const sampleId = selectElement.getAttribute('data-sample-id');
    const currentStatus = selectElement.getAttribute('data-current-status');
    const newStatus = selectElement.value;
    
    console.log('🔄 Status change requested:', { sampleId, currentStatus, newStatus });
    
    // Check if status actually changed
    if (currentStatus === newStatus) {
        console.log('ℹ️ Status unchanged, skipping update');
        return;
    }
    
    // Confirm change for certain status transitions
    if (newStatus === 'Cancelled') {
        if (!confirm('⚠️ Are you sure you want to cancel this sample? This action cannot be undone.')) {
            selectElement.value = currentStatus;
            console.log('❌ Status change cancelled by user');
            return;
        }
    }
    
    // Disable dropdown and show loading
    isUpdating = true;
    selectElement.disabled = true;
    selectElement.classList.add('updating-status');
    
    console.log('📤 Sending update request...');
    
    // AJAX update
    fetch(CONTROLLER_PATH, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=updateStatus&sample_id=${sampleId}&new_status=${encodeURIComponent(newStatus)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📥 Update response:', data);
        
        if (data.status === 'success') {
            // Update the data attribute
            selectElement.setAttribute('data-current-status', newStatus);
            
            // Update dropdown class
            selectElement.className = 'status-dropdown ' + getStatusClass(newStatus);
            
            // Show success message
            showToast(`✅ Status updated to "${newStatus}" successfully`, 'success');
            
            // Reload to update counts
            setTimeout(() => loadSamples(), 500);
            
            console.log('✅ Status updated successfully');
        } else {
            console.error('❌ Update failed:', data.message);
            // Revert to old status
            selectElement.value = currentStatus;
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('💥 Update error:', error);
        selectElement.value = currentStatus;
        showToast('Network error. Please try again.', 'error');
    })
    .finally(() => {
        isUpdating = false;
        selectElement.disabled = false;
        selectElement.classList.remove('updating-status');
        console.log('🏁 Update process completed');
    });
}

/**
 * Update status counts in filter pills
 */
function updateCounts(counts) {
    console.log('📊 Updating counts:', counts);
    
    const countElements = {
        'all': document.getElementById('count-all'),
        'Pending': document.getElementById('count-pending'),
        'In Progress': document.getElementById('count-inprogress'),
        'Completed': document.getElementById('count-completed'),
        'Cancelled': document.getElementById('count-cancelled')
    };
    
    if (countElements.all) countElements.all.textContent = counts.all || 0;
    if (countElements.Pending) countElements.Pending.textContent = counts.Pending || 0;
    if (countElements['In Progress']) countElements['In Progress'].textContent = counts['In Progress'] || 0;
    if (countElements.Completed) countElements.Completed.textContent = counts.Completed || 0;
    if (countElements.Cancelled) countElements.Cancelled.textContent = counts.Cancelled || 0;
}

/**
 * Update last update time
 */
function updateLastUpdateTime() {
    const lastUpdatedEl = document.getElementById('lastUpdated');
    if (lastUpdatedEl) {
        const now = new Date();
        lastUpdatedEl.textContent = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
    }
    lastUpdateTime = new Date();
}

/**
 * Get CSS class for status
 */
function getStatusClass(status) {
    const statusMap = {
        'Pending': 'status-pending',
        'In Progress': 'status-inprogress',
        'Completed': 'status-completed',
        'Cancelled': 'status-cancelled'
    };
    return statusMap[status] || '';
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    
    try {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    } catch (error) {
        console.error('Date format error:', error);
        return dateString;
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastEl = document.getElementById('statusToast');
    const toastBody = document.getElementById('toastMessage');
    
    if (!toastEl || !toastBody) {
        console.error('❌ Toast elements not found!');
        alert(message); // Fallback to alert
        return;
    }
    
    // Set message
    toastBody.textContent = message;
    
    // Set class based on type
    toastEl.className = 'toast align-items-center border-0 toast-' + type;
    
    // Show toast
    try {
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3500
        });
        toast.show();
        console.log('🍞 Toast shown:', type, message);
    } catch (error) {
        console.error('Toast error:', error);
        alert(message); // Fallback to alert
    }
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    const tbody = document.getElementById('samplesTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        <h5>System Error</h5>
                        <p class="text-danger">${escapeHtml(message)}</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Add rotation animation CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .rotating {
        animation: rotate 1s linear infinite;
    }
`;
document.head.appendChild(style);

console.log('✅ Sample Status Management script loaded successfully');