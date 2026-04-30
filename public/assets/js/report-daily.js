/**
 * Daily Summary Dashboard Logic
 * Fetches data on load and populates KPI, Charts, and latest records table.
 */

const DailySummary = (function() {
    
    // Config
    const endpoint = 'src/Controllers/DailySummaryController.php';
    const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';

    // Elements
    const elKpiIntakeTotal = document.getElementById('dsKpiIntakeTotal');
    const elKpiIntakeSub = document.getElementById('dsKpiIntakeSub');
    const elKpiCompleted = document.getElementById('dsKpiCompleted');
    const elKpiReports = document.getElementById('dsKpiReports');
    const elKpiRevenue = document.getElementById('dsKpiRevenue');
    const elLiveClock = document.getElementById('dsLiveClock');
    const recentIntakeTbody = document.querySelector('#dsRecentIntakeTable tbody');

    // Chart instances
    let donutChartInstance = null;
    let trendChartInstance = null;

    /**
     * Clock update
     */
    function updateClock() {
        if (!elLiveClock) return;
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        elLiveClock.textContent = `${hours}:${minutes}`;
    }

    /**
     * Formatting utils
     */
    function formatCurrency(val) {
        return new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR' }).format(val);
    }

    function getStatusBadge(status) {
        let cls = 'ds-badge-pending';
        if (status === 'In Progress') cls = 'ds-badge-progress';
        if (status === 'Completed') cls = 'ds-badge-completed';
        if (status === 'Cancelled') cls = 'ds-badge-cancelled';
        return `<span class="ds-badge ${cls}">${status}</span>`;
    }

    function getPaymentBadge(status) {
        let cls = status === 'Paid' ? 'ds-badge-paid' : 'ds-badge-notpaid';
        return `<span class="ds-badge ${cls}">${status}</span>`;
    }

    function formatTime(timeStr) {
        if (!timeStr) return '-';
        return timeStr.substring(0, 5); // Assuming HH:MM:SS, format to HH:MM
    }

    /**
     * Render KPI Cards
     */
    function renderKPIs(kpis) {
        elKpiIntakeTotal.textContent = kpis.intakes.total;
        
        const intakeParts = [];
        if (kpis.intakes.water > 0) intakeParts.push(`${kpis.intakes.water} Water`);
        if (kpis.intakes.food > 0) intakeParts.push(`${kpis.intakes.food} Food`);
        if (kpis.intakes.swab > 0) intakeParts.push(`${kpis.intakes.swab} Swabs`);
        if (kpis.intakes.other > 0) intakeParts.push(`${kpis.intakes.other} Other`);
        
        elKpiIntakeSub.textContent = intakeParts.length > 0 ? intakeParts.join(' / ') : 'No intakes today';
        
        elKpiCompleted.textContent = kpis.completed;
        elKpiReports.textContent = kpis.reports_generated;
        elKpiRevenue.textContent = formatCurrency(kpis.revenue);
    }

    /**
     * Render Recent Intakes Table
     */
    function renderRecentIntakes(intakes) {
        if (!intakes || intakes.length === 0) {
            recentIntakeTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No samples received today.</td></tr>';
            return;
        }

        let html = '';
        intakes.forEach(item => {
            html += `
                <tr>
                    <td><strong>${item.sample_code}</strong></td>
                    <td>${item.client_name}</td>
                    <td>${formatTime(item.received_time)}</td>
                    <td>${getStatusBadge(item.status)}</td>
                    <td>${getPaymentBadge(item.payment_status)}</td>
                </tr>
            `;
        });
        recentIntakeTbody.innerHTML = html;
    }

    /**
     * Render Donut Chart (Intake Breakdown)
     */
    function renderDonutChart(intakes) {
        const ctx = document.getElementById('dsDonutChart').getContext('2d');
        if (donutChartInstance) donutChartInstance.destroy();

        const water = intakes.water || 0;
        const food  = intakes.food  || 0;
        const swab  = intakes.swab  || 0;
        const hasData = (water + food + swab) > 0;

        if (!hasData) {
            // Empty state – show a ghost grey ring with a centered label
            donutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['No Data'],
                    datasets: [{ data: [1], backgroundColor: ['#e2e8f0'], borderWidth: 0 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '78%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
            return;
        }

        // 3 fixed categories: Water (blue), Food (amber), Swab (emerald)
        donutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Water', 'Food', 'Swab'],
                datasets: [{
                    data: [water, food, swab],
                    backgroundColor: ['#3b82f6', '#f59e0b', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 16,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = water + food + swab;
                                const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Render Trend Bar Chart
     */
    function renderTrendChart(trendData) {
        const ctx = document.getElementById('dsTrendChart').getContext('2d');
        if (trendChartInstance) trendChartInstance.destroy();

        trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: 'Samples Received',
                    data: trendData.data,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#e2e8f0' } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    /**
     * Load Dashboard Data
     */
    function loadData() {
        const loader = document.getElementById('dsMainLoader');
        if (loader) loader.style.display = 'flex';

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'getDashboardData',
                csrf_token: csrfToken
            })
        })
        .then(res => res.json())
        .then(res => {
            if (loader) loader.style.display = 'none';

            if (res.status === 'success') {
                const data = res.data;
                renderKPIs(data.kpis);
                renderRecentIntakes(data.recentIntakes);
                renderDonutChart(data.kpis.intakes);
                renderTrendChart(data.trend);
            } else {
                console.error('Failed to load dashboard data', res.message);
                alert('Error loading dashboard: ' + res.message);
            }
        })
        .catch(err => {
            if (loader) loader.style.display = 'none';
            console.error('Network Error', err);
        });
    }

    return {
        init: function() {
            // Ensure Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error("Chart.js is not loaded.");
                return;
            }
            // Start clock
            updateClock();
            setInterval(updateClock, 1000); // Check every second to be responsive to minute changes
            
            loadData();
        },
        refresh: loadData
    };

})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    DailySummary.init();
});
