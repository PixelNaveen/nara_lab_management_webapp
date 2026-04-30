/**
 * Turnaround Time (TAT) Report - JavaScript Controller
 * Laboratory Management System
 *
 * Handles:
 * - Loading KPI summary data
 * - Rendering Chart.js charts (Status Pie, TAT Distribution Bar)
 * - Loading and rendering the detailed samples table
 * - Filter controls and reset
 *
 * @version 1.0
 */

const TATReport = (() => {
    'use strict';

    const CONTROLLER_URL = '../../src/Controllers/TurnaroundController.php';

    // Chart instances
    let statusChart = null;
    let distributionChart = null;

    // ================================================================
    // INITIALIZATION
    // ================================================================

    function init() {
        // Disable future dates on date pickers
        const today = new Date().toISOString().split('T')[0];
        const dateFrom = document.getElementById('tatDateFrom');
        const dateTo = document.getElementById('tatDateTo');
        if (dateFrom) dateFrom.max = today;
        if (dateTo) dateTo.max = today;

        // Clear custom date pickers when a preset is selected
        const presetEl = document.getElementById('tatDatePreset');
        if (presetEl) {
            presetEl.addEventListener('change', () => {
                if (presetEl.value) {
                    if (dateFrom) dateFrom.value = '';
                    if (dateTo) dateTo.value = '';
                }
            });
        }

        // Clear preset when custom dates are entered
        if (dateFrom) {
            dateFrom.addEventListener('change', () => {
                if (dateFrom.value && presetEl) presetEl.value = '';
            });
        }
        if (dateTo) {
            dateTo.addEventListener('change', () => {
                if (dateTo.value && presetEl) presetEl.value = '';
            });
        }

        // Initial load
        load();
    }

    // ================================================================
    // COLLECT FILTERS
    // ================================================================

    function getFilters() {
        return {
            search: (document.getElementById('tatSearch') || {}).value || '',
            status: (document.getElementById('tatStatusFilter') || {}).value || 'all',
            date_preset: (document.getElementById('tatDatePreset') || {}).value || '',
            date_from: (document.getElementById('tatDateFrom') || {}).value || '',
            date_to: (document.getElementById('tatDateTo') || {}).value || ''
        };
    }

    // ================================================================
    // LOAD ALL DATA
    // ================================================================

    function load() {
        loadSummary();
        loadDetails();
        loadStatusDistribution();
    }

    // ================================================================
    // RESET
    // ================================================================

    function reset() {
        const search = document.getElementById('tatSearch');
        const status = document.getElementById('tatStatusFilter');
        const preset = document.getElementById('tatDatePreset');
        const dateFrom = document.getElementById('tatDateFrom');
        const dateTo = document.getElementById('tatDateTo');

        if (search) search.value = '';
        if (status) status.value = 'all';
        if (preset) preset.value = 'last30';
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';

        load();
    }

    // ================================================================
    // LOAD KPI SUMMARY
    // ================================================================

    function loadSummary() {
        const filters = getFilters();
        const formData = new FormData();
        formData.append('action', 'getSummary');

        Object.keys(filters).forEach(k => formData.append(k, filters[k]));

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(resp => {
                if (resp.status === 'success') {
                    renderKPIs(resp.data);
                }
            })
            .catch(err => {
                console.error('Summary load error:', err);
            });
    }

    function renderKPIs(data) {
        const avgEl = document.getElementById('kpiAvgTat');
        const compEl = document.getElementById('kpiCompleted');
        const breachEl = document.getElementById('kpiBreached');
        const onTimeEl = document.getElementById('kpiOnTime');
        const pendingEl = document.getElementById('kpiPending');

        if (avgEl) avgEl.textContent = data.avg_tat !== null ? data.avg_tat : '0';
        if (compEl) compEl.textContent = data.completed_count || '0';
        if (breachEl) breachEl.textContent = data.breached_count || '0';
        if (onTimeEl) onTimeEl.textContent = data.on_time_count || '0';
        if (pendingEl) pendingEl.textContent = data.pending_count || '0';
    }

    // ================================================================
    // LOAD STATUS DISTRIBUTION (PIE CHART)
    // ================================================================

    function loadStatusDistribution() {
        const filters = getFilters();
        const formData = new FormData();
        formData.append('action', 'getStatusDistribution');
        Object.keys(filters).forEach(k => formData.append(k, filters[k]));

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(resp => {
                if (resp.status === 'success') {
                    renderStatusChart(resp.data);
                }
            })
            .catch(err => console.error('Status distribution error:', err));
    }

    function renderStatusChart(data) {
        const ctx = document.getElementById('tatStatusChart');
        if (!ctx) return;

        if (statusChart) statusChart.destroy();

        const colorMap = {
            'Pending': '#f59e0b',
            'In Progress': '#0ea5e9',
            'Completed': '#16a34a'
        };

        const labels = data.map(d => d.status);
        const values = data.map(d => parseInt(d.count));
        const colors = labels.map(l => colorMap[l] || '#94a3b8');

        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 14,
                            usePointStyle: true,
                            pointStyleWidth: 10,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.raw} samples`
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // ================================================================
    // LOAD DETAILED TABLE
    // ================================================================

    function loadDetails() {
        const filters = getFilters();
        const formData = new FormData();
        formData.append('action', 'getDetails');
        Object.keys(filters).forEach(k => formData.append(k, filters[k]));

        const tbody = document.getElementById('tatTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="9" class="tat-loading"><i class="bi bi-arrow-repeat me-1"></i> Loading data...</td></tr>';
        }

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(resp => {
                if (resp.status === 'success') {
                    renderTable(resp.data);
                    renderDistributionChart(resp.data);
                    const countEl = document.getElementById('tatRowCount');
                    if (countEl) countEl.textContent = resp.count || 0;
                }
            })
            .catch(err => {
                console.error('Details load error:', err);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="9" class="tat-empty"><i class="bi bi-exclamation-circle"></i><p>Failed to load data</p></td></tr>';
                }
            });
    }

    function renderTable(rows) {
        const tbody = document.getElementById('tatTableBody');
        if (!tbody) return;

        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="tat-empty"><i class="bi bi-inbox"></i><p>No samples found for the selected filters</p></td></tr>';
            return;
        }

        let html = '';
        rows.forEach((row, idx) => {
            const tatDisplay = getTatDisplay(row);
            const delayDisplay = getDelayDisplay(row);
            const statusBadge = getStatusBadge(row);

            html += `<tr>
                <td>${idx + 1}</td>
                <td><strong>${escHtml(row.sample_code || '')}</strong></td>
                <td>${escHtml(row.client_name || '')}</td>
                <td>${formatDate(row.received_date)}</td>
                <td>${formatDate(row.tentative_date)}</td>
                <td>${formatDate(row.analysis_end_date)}</td>
                <td>${tatDisplay}</td>
                <td>${delayDisplay}</td>
                <td>${statusBadge}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }

    // ================================================================
    // TAT DISTRIBUTION BAR CHART
    // ================================================================

    function renderDistributionChart(rows) {
        const ctx = document.getElementById('tatDistributionChart');
        if (!ctx) return;

        if (distributionChart) distributionChart.destroy();

        // Only completed rows with tat_days
        const completedRows = rows.filter(r => r.tat_days !== null && r.tat_days !== undefined);

        // Bucket: 0-3, 4-7, 8-10, 11-14, 15+
        const buckets = { '0-3': 0, '4-7': 0, '8-10': 0, '11-14': 0, '15+': 0 };
        completedRows.forEach(r => {
            const d = parseInt(r.tat_days);
            if (d <= 3) buckets['0-3']++;
            else if (d <= 7) buckets['4-7']++;
            else if (d <= 10) buckets['8-10']++;
            else if (d <= 14) buckets['11-14']++;
            else buckets['15+']++;
        });

        const barColors = ['#16a34a', '#22c55e', '#f59e0b', '#f97316', '#dc2626'];

        distributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(buckets),
                datasets: [{
                    label: 'Samples',
                    data: Object.values(buckets),
                    backgroundColor: barColors,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.raw} sample(s)`
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Days', font: { size: 11, weight: '600' } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    }

    // ================================================================
    // HELPERS
    // ================================================================

    function getTatDisplay(row) {
        if (row.status === 'Completed' && row.tat_days !== null) {
            const d = parseInt(row.tat_days);
            let cls = 'tat-good';
            if (d > 10) cls = 'tat-bad';
            else if (d > 7) cls = 'tat-warn';
            return `<span class="tat-tat-value ${cls}">${d}</span>`;
        }
        if (row.elapsed_days !== null) {
            return `<span class="tat-tat-value tat-warn">${row.elapsed_days}+</span>`;
        }
        return '<span style="color:#94a3b8;">—</span>';
    }

    function getDelayDisplay(row) {
        if (row.delay_days === null || row.delay_days === undefined) {
            if (row.status === 'Completed') return '<span class="tat-badge tat-badge-ontime"><i class="bi bi-check-lg"></i> On Time</span>';
            return '<span style="color:#94a3b8;">—</span>';
        }
        const d = parseInt(row.delay_days);
        if (d <= 0) {
            return '<span class="tat-badge tat-badge-ontime"><i class="bi bi-check-lg"></i> On Time</span>';
        }
        return `<span class="tat-badge tat-badge-breach"><i class="bi bi-exclamation-triangle"></i> +${d} day${d > 1 ? 's' : ''}</span>`;
    }

    function getStatusBadge(row) {
        const s = row.status;
        if (s === 'Completed') return '<span class="tat-badge tat-badge-completed"><i class="bi bi-check-circle-fill"></i> Completed</span>';
        if (s === 'In Progress') return '<span class="tat-badge tat-badge-progress"><i class="bi bi-gear-fill"></i> In Progress</span>';
        if (s === 'Pending') return '<span class="tat-badge tat-badge-pending"><i class="bi bi-clock-fill"></i> Pending</span>';
        return `<span class="tat-badge">${escHtml(s)}</span>`;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '<span style="color:#94a3b8;">—</span>';
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ================================================================
    // PUBLIC API
    // ================================================================

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return { load, reset };

})();
