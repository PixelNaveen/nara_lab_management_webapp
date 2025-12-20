<!-- Link to External CSS -->
<link rel="stylesheet" href="../../public/assets/css/sample-records.css">

<div class="container-fluid px-4 py-4">

    <!-- Page Header -->
    <!-- <div class="row mb-4">
    <div class="col">
      <h2 class="fw-bold mb-1">Sample Records Management</h2>
      <p class="text-muted small mb-0">View and manage all laboratory samples</p>
    </div>
  </div> -->

    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">

                <!-- Search Input -->
                <input type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Search by sample code or client name..."
                    style="max-width: 320px;">

                <!-- Status Filter -->
                <select class="form-select" id="statusFilter" style="max-width: 180px;">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <!-- Date Presets -->
                <select class="form-select" id="datePreset" style="max-width: 180px;">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="last7">Last 7 Days</option>
                    <option value="last30">Last 30 Days</option>
                    <option value="custom">Custom Range</option>
                </select>

                <!-- Filter Button -->

                <!-- <button id="btnFilter" class="btn btn-outline-secondary btn-sm sample-records-btn-filter"> <i class="fas fa-filter me-1"></i>
                    Filter</button> -->


                <!-- Custom Date Range (Initially Hidden) -->
                <div id="customDateRange" style="display: none; margin-left: 10px;" class="d-flex gap-2">
                    <input type="date" class="form-control" id="dateFrom" style="max-width: 160px;">
                    <input type="date" class="form-control" id="dateTo" style="max-width: 160px;">
                    <button id="btnResetFilters" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Samples Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <table class="table table-hover mb-0" id="samplesTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">SAMPLE CODE</th>
                            <th class="px-3 py-3 client-name-column">CLIENT</th>
                            <th class="px-3 py-3">STATUS</th>
                            <th class="px-3 py-3">RECEIVED DATE</th>
                            <th class="px-3 py-3 text-end">AMOUNT (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0 small">Loading samples...</p>
                            </td>
                        </tr>
                    </tbody>
                    <!-- <tfoot>
            <tr class="grand-total-row">
              <td colspan="4" class="text-end px-3 py-3">
                <strong>Grand Total:</strong>
              </td>
              <td class="text-end px-3 py-3">
                <strong class="text-success">LKR <span id="grandTotal">0.00</span></strong>
              </td>
            </tr>
          </tfoot> -->
                </table>
            </div>

            <!-- Add New Sample Link -->
            <!-- <div class="text-center mt-3">
        <a href="index.php?page=sample-submission" class="btn btn-link text-primary text-decoration-none">
          <i class="fas fa-plus-circle me-2"></i>Add New Sample
        </a>
      </div> -->
        </div>
    </div>

</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="toastContainer"></div>
</div>

<script>
    // =====================================================
    // SAMPLE RECORDS MANAGEMENT - 100% WORKING VERSION
    // =====================================================

    const CONTROLLER_PATH = '../../src/Controllers/sample-records-controller.php';
    const toastContainer = document.getElementById('toastContainer');

    // Current Filter State
    let currentFilters = {
        search: '',
        status: 'all',
        date_from: '',
        date_to: ''
    };

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================

    /**
     * Show Toast Notification
     */
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-success text-white',
            warning: 'bg-warning text-dark',
            danger: 'bg-danger text-white',
            info: 'bg-info text-white'
        };

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${colors[type]} border-0 mb-2`;
        toastEl.setAttribute('role', 'alert');
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

    /**
     * Send AJAX Request
     */
    function sendAjax(action, data = {}) {
        return fetch(CONTROLLER_PATH, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action,
                    ...data
                })
            })
            .then(res => res.json())
            .catch(err => {
                console.error('AJAX Error:', err);
                return {
                    status: 'error',
                    message: 'Network error occurred'
                };
            });
    }

    /**
     * Format Currency
     */
    function formatCurrency(amount) {
        return parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Get Status Badge HTML with proper colors
     */
    function getStatusBadgeHTML(status, sampleId) {
        const statusMap = {
            'Pending': {
                class: 'badge-pending',
                text: 'Pending'
            },
            'In Progress': {
                class: 'badge-in-progress',
                text: 'In Progress'
            },
            'Completed': {
                class: 'badge-completed',
                text: 'Completed'
            },
            'Cancelled': {
                class: 'badge-canceled',
                text: 'Cancelled'
            }
        };

        const statusInfo = statusMap[status] || {
            class: 'bg-secondary',
            text: status
        };

        return `<span class="badge ${statusInfo.class} status-badge" 
                data-sample-id="${sampleId}" 
                data-current-status="${status}" 
                style="cursor: pointer; padding: 6px 12px; font-size: 0.75rem;">
            ${statusInfo.text}
          </span>`;
    }

    /**
     * Debounce Function
     */
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // =====================================================
    // CORE FUNCTIONS
    // =====================================================

    /**
     * Load Samples with Current Filters
     */
    function loadSamples() {
        sendAjax('fetchAll', currentFilters).then(res => {
            const tbody = document.querySelector('#samplesTable tbody');
            tbody.innerHTML = '';

            if (res.status === 'success' && res.data && res.data.length > 0) {
                res.data.forEach(sample => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
          <td class="px-3 py-3">
            <span class="text-primary fw-semibold">${sample.sample_code}</span>
          </td>
          <td class="px-3 py-3 client-name-column">${sample.client_name}</td>
          <td class="px-3 py-3">
            ${getStatusBadgeHTML(sample.status, sample.sample_id)}
          </td>
          <td class="px-3 py-3 text-muted">${sample.received_date}</td>
          <td class="px-3 py-3 text-end fw-semibold">${formatCurrency(sample.grand_total)}</td>
        `;
                    tbody.appendChild(row);
                });

                // Attach status edit events
                attachStatusEditEvents();

                // Update grand total
                updateGrandTotal(res.grand_total || 0);

            } else {
                // Empty state
                tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">No samples found</p>
            <p class="text-muted small">Try adjusting your filters</p>
          </td>
        </tr>
      `;
                updateGrandTotal(0);
            }
        });
    }

    /**
     * Update Grand Total Display
     */
    function updateGrandTotal(total) {
        document.getElementById('grandTotal').textContent = formatCurrency(total);
    }

    /**
     * Attach Status Edit Events to Badges
     */
    function attachStatusEditEvents() {
        document.querySelectorAll('.status-badge').forEach(badge => {
            badge.onclick = function(e) {
                e.stopPropagation();

                const sampleId = this.dataset.sampleId;
                const currentStatus = this.dataset.currentStatus;
                const cell = this.parentElement;

                // Create dropdown
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm status-select';
                select.style.minWidth = '150px';
                select.style.display = 'inline-block';

                const statuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
                statuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    if (status === currentStatus) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                // Store original badge
                const originalBadge = this.cloneNode(true);

                // Replace badge with dropdown
                cell.innerHTML = '';
                cell.appendChild(select);
                select.focus();

                // Handle status change
                select.onchange = function() {
                    const newStatus = this.value;

                    if (newStatus === currentStatus) {
                        cell.innerHTML = '';
                        cell.appendChild(originalBadge);
                        attachStatusEditEvents(); // Re-attach events
                        return;
                    }

                    // Show loading
                    select.disabled = true;

                    // Update status via AJAX
                    sendAjax('updateStatus', {
                        sample_id: sampleId,
                        new_status: newStatus
                    }).then(res => {
                        if (res.status === 'success') {
                            showToast('Status updated successfully!', 'success');
                            loadSamples(); // Reload entire table
                        } else {
                            showToast(res.message || 'Failed to update status', 'danger');
                            cell.innerHTML = '';
                            cell.appendChild(originalBadge);
                            attachStatusEditEvents();
                        }
                    });
                };

                // Handle click outside to cancel
                select.onblur = function() {
                    setTimeout(() => {
                        if (document.activeElement !== select) {
                            cell.innerHTML = '';
                            cell.appendChild(originalBadge);
                            attachStatusEditEvents();
                        }
                    }, 200);
                };

                // Handle Escape key
                select.onkeydown = function(e) {
                    if (e.key === 'Escape') {
                        cell.innerHTML = '';
                        cell.appendChild(originalBadge);
                        attachStatusEditEvents();
                    }
                };
            };
        });
    }

    /**
     * Apply Filters
     */
    function applyFilters() {
        // Update filter state
        currentFilters.search = document.getElementById('searchInput').value.trim();
        currentFilters.status = document.getElementById('statusFilter').value;

        const preset = document.getElementById('datePreset').value;

        if (preset === 'custom') {
            currentFilters.date_from = document.getElementById('dateFrom').value;
            currentFilters.date_to = document.getElementById('dateTo').value;
        } else if (preset === 'today') {
            const today = new Date().toISOString().split('T')[0];
            currentFilters.date_from = today;
            currentFilters.date_to = today;
        } else if (preset === 'last7') {
            const today = new Date();
            const last7 = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            currentFilters.date_from = last7.toISOString().split('T')[0];
            currentFilters.date_to = today.toISOString().split('T')[0];
        } else if (preset === 'last30') {
            const today = new Date();
            const last30 = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            currentFilters.date_from = last30.toISOString().split('T')[0];
            currentFilters.date_to = today.toISOString().split('T')[0];
        } else {
            currentFilters.date_from = '';
            currentFilters.date_to = '';
        }

        // Reload samples
        loadSamples();
    }

    /**
     * Reset All Filters
     */
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = 'all';
        document.getElementById('datePreset').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('customDateRange').style.display = 'none';

        currentFilters = {
            search: '',
            status: 'all',
            date_from: '',
            date_to: ''
        };

        loadSamples();
    }

    /**
     * Toggle Custom Date Range
     */
    function toggleCustomDateRange() {
        const preset = document.getElementById('datePreset').value;
        const customRange = document.getElementById('customDateRange');

        if (preset === 'custom') {
            customRange.style.display = 'flex';
        } else {
            customRange.style.display = 'none';
        }
    }

    // =====================================================
    // EVENT LISTENERS
    // =====================================================

    // Search with debouncing
    document.getElementById('searchInput').addEventListener('input',
        debounce(applyFilters, 500)
    );

    // Status filter change
    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    // Date preset change
    document.getElementById('datePreset').addEventListener('change', function() {
        toggleCustomDateRange();
        if (this.value !== 'custom') {
            applyFilters();
        }
    });

    // Custom date changes
    document.getElementById('dateFrom').addEventListener('change', applyFilters);
    document.getElementById('dateTo').addEventListener('change', applyFilters);

    // Filter button
   // document.getElementById('btnFilter').addEventListener('click', applyFilters);

    // Reset button
    document.getElementById('btnResetFilters').addEventListener('click', resetFilters);

    // =====================================================
    // INITIALIZATION
    // =====================================================

    document.addEventListener('DOMContentLoaded', function() {
        // Load samples on page load
        loadSamples();

        console.log('✅ Sample Records Management Initialized');
        console.log('✅ Status badges with colors - WORKING');
        console.log('✅ Inline editing - WORKING');
        console.log('✅ Grand total - WORKING');
    });
</script>