/**
 * Dashboard JavaScript
 * Handles data fetching, UI updates, and Chart.js rendering for the enterprise dashboard.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we are on the dashboard
    if (!document.getElementById('dashboard-container')) return;

    // Initialize state
    let chartInstances = {};
    let currentPeriod = 'this_week';
    
    // Set up event listeners
    setupEventListeners();

    // Initial load
    loadDashboardData();

    // Auto-refresh every 5 minutes (300000 ms)
    setInterval(loadDashboardData, 300000);

    function setupEventListeners() {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.dataset.period === 'custom') {
                    document.getElementById('custom-date-container').classList.remove('d-none');
                    return; // Don't fetch yet, wait for Apply button
                }
                
                document.getElementById('custom-date-container').classList.add('d-none');
                
                // Update active state
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                currentPeriod = this.dataset.period;
                loadDashboardData();
            });
        });

        const btnApplyCustom = document.getElementById('btnApplyCustomDate');
        if (btnApplyCustom) {
            btnApplyCustom.addEventListener('click', function() {
                const from = document.getElementById('customDateFrom').value;
                const to = document.getElementById('customDateTo').value;
                
                if (!from || !to) {
                    alert('Please select both start and end dates.');
                    return;
                }
                
                if (new Date(from) > new Date(to)) {
                    alert('Start date cannot be after end date.');
                    return;
                }

                // Update active state
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('.period-btn[data-period="custom"]').classList.add('active');
                
                currentPeriod = 'custom';
                loadDashboardData(from, to);
            });
        }

        const btnResetCustom = document.getElementById('btnResetCustomDate');
        if (btnResetCustom) {
            btnResetCustom.addEventListener('click', function() {
                // Clear inputs
                document.getElementById('customDateFrom').value = '';
                document.getElementById('customDateTo').value = '';
                
                // Hide custom container
                document.getElementById('custom-date-container').classList.add('d-none');
                
                // Revert to This Week
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('.period-btn[data-period="this_week"]').classList.add('active');
                
                currentPeriod = 'this_week';
                loadDashboardData();
            });
        }
    }

    function getDateRange(period) {
        const today = new Date();
        let fromDate, toDate;

        toDate = today.toISOString().split('T')[0];

        switch (period) {
            case 'this_week':
                const d = new Date();
                d.setDate(d.getDate() - 7);
                fromDate = d.toISOString().split('T')[0];
                break;
            case 'this_month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                break;
            case 'last_90':
                const d90 = new Date();
                d90.setDate(d90.getDate() - 90);
                fromDate = d90.toISOString().split('T')[0];
                break;
            case 'this_year':
                fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                break;
            default:
                const dDefault = new Date();
                dDefault.setDate(dDefault.getDate() - 7);
                fromDate = dDefault.toISOString().split('T')[0];
        }

        return { from: fromDate, to: toDate };
    }

    function loadDashboardData(customFrom = null, customTo = null) {
        let dateFrom, dateTo;
        
        if (currentPeriod === 'custom' && customFrom && customTo) {
            dateFrom = customFrom;
            dateTo = customTo;
        } else {
            const range = getDateRange(currentPeriod);
            dateFrom = range.from;
            dateTo = range.to;
        }

        // Show loading state (could add spinner classes to cards here)
        
        const formData = new FormData();
        formData.append('action', 'getDashboardData');
        formData.append('date_from', dateFrom);
        formData.append('date_to', dateTo);

        fetch('src/Controllers/DashboardController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                updateKPIs(res.data.kpis);
                renderCharts(res.data.charts);
                updateTables(res.data.tables);
            } else {
                console.error('Error fetching dashboard data:', res.message);
                // alert('Failed to load dashboard data. Please try again.');
            }
        })
        .catch(err => {
            console.error('AJAX Error:', err);
        });
    }

    function formatCurrency(amount) {
        if (amount >= 1000000) {
            return 'Rs. ' + (amount / 1000000).toFixed(2) + 'M';
        } else if (amount >= 1000) {
            return 'Rs. ' + (amount / 1000).toFixed(1) + 'K';
        }
        return 'Rs. ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function animateValue(obj, start, end, duration, formatter = null) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            let current = Math.floor(progress * (end - start) + start);
            
            // Allow decimals for specific fields
            if (obj.id === 'kpi-completion' || obj.id === 'kpi-tat') {
                 current = (progress * (end - start) + start).toFixed(1);
            }

            if (formatter) {
                obj.innerHTML = formatter(current);
            } else {
                obj.innerHTML = current;
            }
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                // Ensure exact final value
                if (formatter) {
                    obj.innerHTML = formatter(end);
                } else {
                    obj.innerHTML = obj.id === 'kpi-completion' || obj.id === 'kpi-tat' ? end.toFixed(1) : end;
                }
            }
        };
        window.requestAnimationFrame(step);
    }

    function updateKPIs(kpis) {
        // Main KPIs
        const elTotalSamples = document.getElementById('kpi-total-samples');
        const elTotalRevenue = document.getElementById('kpi-total-revenue');
        const elOutstanding = document.getElementById('kpi-outstanding');
        const elCompletion = document.getElementById('kpi-completion');
        const elAvgTat = document.getElementById('kpi-tat');
        const elTodayIntake = document.getElementById('kpi-today-intake');

        if (elTotalSamples) animateValue(elTotalSamples, 0, kpis.total_samples || 0, 1000);
        if (elTotalRevenue) animateValue(elTotalRevenue, 0, kpis.total_revenue || 0, 1000, formatCurrency);
        if (elOutstanding) animateValue(elOutstanding, 0, kpis.outstanding_balance || 0, 1000, formatCurrency);
        if (elCompletion) animateValue(elCompletion, 0, parseFloat(kpis.completion_rate) || 0, 1000, (val) => val + '%');
        if (elAvgTat) animateValue(elAvgTat, 0, parseFloat(kpis.avg_tat) || 0, 1000, (val) => val + ' days');
        if (elTodayIntake) animateValue(elTodayIntake, 0, kpis.today_intake || 0, 1000);

        // Sub KPIs (Today)
        document.getElementById('kpi-today-water').innerText = kpis.today_water || 0;
        document.getElementById('kpi-today-food').innerText = kpis.today_food || 0;
        document.getElementById('kpi-today-swab').innerText = kpis.today_swab || 0;
        document.getElementById('kpi-today-revenue').innerText = formatCurrency(kpis.today_revenue || 0);
    }

    function renderCharts(chartsData) {
        // Destroy existing charts to prevent duplication
        Object.keys(chartInstances).forEach(key => {
            if (chartInstances[key]) {
                chartInstances[key].destroy();
            }
        });

        // Get aggregation mode from backend
        const aggMode = chartsData.aggregation || 'daily';
        const aggLabel = aggMode === 'daily' ? 'Day by Day' : aggMode === 'weekly' ? 'Week by Week' : 'Month by Month';

        // Update chart subtitles dynamically
        const trendSubEl = document.getElementById('trendChartSubtitle');
        if (trendSubEl) trendSubEl.textContent = 'Billed vs Paid Revenue — ' + aggLabel;
        const intakeSubEl = document.getElementById('intakeChartSubtitle');
        if (intakeSubEl) intakeSubEl.textContent = 'Samples received — ' + aggLabel;

        // 1. Revenue & Sample Trend (Combo Chart)
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx && chartsData.revenue_trend) {
            const labels = chartsData.revenue_trend.map(item => item.date);
            const billedData = chartsData.revenue_trend.map(item => item.billed);
            const paidData = chartsData.revenue_trend.map(item => item.paid);

            chartInstances['trendChart'] = new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Billed (Rs)',
                            data: billedData,
                            backgroundColor: 'rgba(37, 99, 235, 0.8)',
                            borderRadius: 4,
                            maxBarThickness: 40
                        },
                        {
                            label: 'Total Paid (Rs)',
                            data: paidData,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 4,
                            maxBarThickness: 40
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'LKR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // 2. Status Distribution (Doughnut)
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && chartsData.status_distribution) {
            const labels = chartsData.status_distribution.map(item => item.status);
            const data = chartsData.status_distribution.map(item => item.count);
            
            // Match our enterprise colors
            const bgColors = labels.map(status => {
                switch(status) {
                    case 'Pending': return '#f59e0b'; // warning
                    case 'In Progress': return '#2563eb'; // primary
                    case 'Completed': return '#10b981'; // success
                    case 'Cancelled': return '#64748b'; // slate
                    default: return '#94a3b8';
                }
            });

            chartInstances['statusChart'] = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 2b. Category Distribution (Pie)
        const categoryPieCtx = document.getElementById('categoryPieChart');
        if (categoryPieCtx && chartsData.category_distribution) {
            const labels = chartsData.category_distribution.map(item => item.category);
            const data = chartsData.category_distribution.map(item => item.count);
            
            const bgColors = labels.map(status => {
                switch(status) {
                    case 'Water': return '#2563eb'; // primary
                    case 'Food': return '#10b981'; // success
                    case 'Swab': return '#f59e0b'; // warning
                    default: return '#94a3b8';
                }
            });

            chartInstances['categoryPieChart'] = new Chart(categoryPieCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 3. Revenue By Category (Bar)
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx && chartsData.revenue_by_category) {
            const labels = chartsData.revenue_by_category.map(item => item.category_name);
            const billedData = chartsData.revenue_by_category.map(item => item.billed_revenue);
            const paidData = chartsData.revenue_by_category.map(item => item.paid_revenue);

            chartInstances['categoryChart'] = new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Billed',
                            data: billedData,
                            backgroundColor: 'rgba(37, 99, 235, 0.8)',
                            borderRadius: 4,
                            maxBarThickness: 28
                        },
                        {
                            label: 'Paid',
                            data: paidData,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 4,
                            maxBarThickness: 28
                        }
                    ]
                },
                options: {
                    indexAxis: 'y', // Horizontal bar
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                         tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('en-US', { style: 'currency', currency: 'LKR' }).format(context.parsed.x);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        }

        // 4. Intake Trend (Area) — aggregation-aware
        const intakeCtx = document.getElementById('intakeTrendChart');
        if (intakeCtx && chartsData.intake_trend) {
            chartInstances['intakeTrendChart'] = new Chart(intakeCtx, {
                type: 'line',
                data: {
                    labels: chartsData.intake_trend.labels,
                    datasets: [{
                        label: 'Samples Received',
                        data: chartsData.intake_trend.data,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#8b5cf6',
                        pointRadius: aggMode === 'daily' && chartsData.intake_trend.labels.length > 30 ? 0 : 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { 
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 15
                            }
                        }
                    }
                }
            });
        }
    }

    function updateTables(tablesData) {
        // Update Recent Samples Table
        const recentTbody = document.getElementById('recentSamplesBody');
        if (recentTbody) {
            recentTbody.innerHTML = '';
            if (tablesData.recent_samples && tablesData.recent_samples.length > 0) {
                tablesData.recent_samples.forEach(sample => {
                    
                    let statusClass = 'status-pending';
                    if (sample.status === 'Completed') statusClass = 'status-completed';
                    else if (sample.status === 'In Progress') statusClass = 'status-progress';
                    else if (sample.status === 'Cancelled') statusClass = 'status-delivered';

                    let paymentClass = 'payment-badge-pending';
                    if (sample.payment_status === 'Paid') paymentClass = 'payment-badge-paid';
                    else if (sample.payment_status === 'Not Paid') paymentClass = 'payment-badge-not-paid';

                    const row = `
                        <tr>
                            <td class="ps-3 fw-bold text-primary">${escapeHtml(sample.sample_code)}</td>
                            <td>${escapeHtml(sample.client_name)}</td>
                            <td><span class="status-badge ${statusClass}">${escapeHtml(sample.status)}</span></td>
                            <td><a href="index.php?page=sample-records-view&search=${escapeHtml(sample.sample_code)}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">View</a></td>
                        </tr>
                    `;
                    recentTbody.innerHTML += row;
                });
            } else {
                recentTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No samples received today.</td></tr>';
            }
        }

        // Update Top Debtors Table
        const debtorsTbody = document.getElementById('topDebtorsBody');
        if (debtorsTbody) {
            debtorsTbody.innerHTML = '';
            if (tablesData.top_debtors && tablesData.top_debtors.length > 0) {
                tablesData.top_debtors.forEach(debtor => {
                    const amountStr = Number(debtor.outstanding_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                    // Highlight very old debts
                    let daysClass = debtor.days_outstanding > 30 ? 'text-danger fw-bold' : 'text-warning fw-bold';
                    
                    const row = `
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">${escapeHtml(debtor.client_name)}</div>
                            </td>
                            <td>${escapeHtml(debtor.sample_code)}</td>
                            <td class="${daysClass}">${debtor.days_outstanding}d</td>
                            <td class="fw-bold text-danger text-end pe-3">Rs. ${amountStr}</td>
                        </tr>
                    `;
                    debtorsTbody.innerHTML += row;
                });
            } else {
                debtorsTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No outstanding debtors found.</td></tr>';
            }
        }

        // Update Popular Tests
        const popularTestsBody = document.getElementById('popularTestsBody');
        if (popularTestsBody) {
            popularTestsBody.innerHTML = '';
            if (tablesData.popular_tests && tablesData.popular_tests.length > 0) {
                tablesData.popular_tests.forEach((test, index) => {
                    const row = `
                        <tr>
                            <td class="ps-3 text-muted">${index + 1}</td>
                            <td>
                                <div class="fw-bold" title="${escapeHtml(test.parameter_name)}">${escapeHtml(test.parameter_name)}</div>
                            </td>
                            <td class="text-end fw-bold text-primary pe-3">${test.request_count}</td>
                        </tr>
                    `;
                    popularTestsBody.innerHTML += row;
                });
            } else {
                popularTestsBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No tests requested.</td></tr>';
            }
        }
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
