/**
 * Revenue Analysis JS Controller
 * Laboratory Management System
 * AAA-Grade Implementation using IIFE
 */

const RevenueAnalytics = (function() {
    
    // Core Elements
    const elements = {
        startDate: document.getElementById('revStartDate'),
        endDate: document.getElementById('revEndDate'),
        btnYTD: document.getElementById('btnYTD'),
        btnMTD: document.getElementById('btnMTD'),
        btnToday: document.getElementById('btnToday'),
        btnFetch: document.getElementById('btnFetchRevenue'),
        loader: document.getElementById('revenueLoader'),
        csrfToken: document.getElementById('csrfToken').value,
        
        // KPIs
        kpiBilled: document.getElementById('kpiBilled'),
        kpiCollected: document.getElementById('kpiCollected'),
        kpiOutstanding: document.getElementById('kpiOutstanding'),
        kpiInvoices: document.getElementById('kpiInvoices')
    };

    // Chart Instances
    let trendChartInstance = null;
    let categoryChartInstance = null;

    // Formatters
    const formatCurrency = (amount) => {
        return 'LKR ' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /**
     * Set Date range based on presets
     */
    const setDateRange = (preset) => {
        const today = new Date();
        elements.endDate.value = today.toISOString().split('T')[0];
        
        if (preset === 'ytd') {
            elements.startDate.value = `${today.getFullYear()}-01-01`;
        } else if (preset === 'mtd') {
            elements.startDate.value = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-01`;
        } else if (preset === 'today') {
            elements.startDate.value = elements.endDate.value;
        }

        // Update active class
        document.querySelectorAll('.rev-btn-preset').forEach(btn => btn.classList.remove('active'));
        if (preset === 'ytd') elements.btnYTD.classList.add('active');
        if (preset === 'mtd') elements.btnMTD.classList.add('active');
        if (preset === 'today') elements.btnToday.classList.add('active');
    };

    /**
     * Fetch all data from the secured endpoint
     */
    const fetchRevenueData = async () => {
        elements.loader.style.display = 'flex';

        const formData = new FormData();
        formData.append('action', 'getRevenueData');
        formData.append('csrf_token', elements.csrfToken);
        formData.append('start_date', elements.startDate.value);
        formData.append('end_date', elements.endDate.value);

        try {
            const response = await fetch('src/Controllers/RevenueController.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                updateDashboard(result.data);
            } else {
                console.error("API Error:", result.message);
                alert("Failed to fetch revenue data: " + result.message);
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            alert("Network error occurred while fetching revenue data.");
        } finally {
            elements.loader.style.display = 'none';
        }
    };

    /**
     * Update entire dashboard visually
     */
    const updateDashboard = (data) => {
        // 1. Update KPIs
        elements.kpiBilled.innerText = formatCurrency(data.summary.total_billed);
        elements.kpiCollected.innerText = formatCurrency(data.summary.total_paid);
        elements.kpiOutstanding.innerText = formatCurrency(data.summary.total_outstanding);
        elements.kpiInvoices.innerText = data.summary.total_invoices;

        // 2. Render Charts
        renderTrendChart(data.trend);
        renderCategoryChart(data.categories);

        // 3. Render Table
        renderDebtorsTable(data.debtors);
    };

    /**
     * Render Trend Bar Chart (Billed vs Paid)
     */
    const renderTrendChart = (trendData) => {
        const ctx = document.getElementById('revenueTrendChart').getContext('2d');
        
        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        const labels = trendData.map(item => item.date);
        const billedData = trendData.map(item => item.billed);
        const paidData = trendData.map(item => item.paid);

        trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Billed (LKR)',
                        data: billedData,
                        backgroundColor: 'rgba(13, 92, 117, 0.8)', // Nara Blue
                        borderRadius: 4
                    },
                    {
                        label: 'Total Collected (LKR)',
                        data: paidData,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)', // Emerald
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4] }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    };

    /**
     * Render Category Doughnut Chart
     */
    const renderCategoryChart = (categoriesData) => {
        const ctx = document.getElementById('revenueCategoryChart').getContext('2d');
        
        if (categoryChartInstance) {
            categoryChartInstance.destroy();
        }

        const labels = categoriesData.map(item => item.category_name);
        // Display Billed Revenue by category
        const data = categoriesData.map(item => item.billed_revenue);
        
        // Define colors for Water, Food, Swabs
        const backgroundColors = [
            'rgba(14, 165, 233, 0.8)', // Sky
            'rgba(245, 158, 11, 0.8)', // Amber
            'rgba(16, 185, 129, 0.8)', // Emerald
            'rgba(100, 116, 139, 0.8)' // Slate for others
        ];

        categoryChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
    };

    /**
     * Render Debtors Table (Vanilla JS)
     */
    const renderDebtorsTable = (debtors) => {
        const tbody = document.getElementById('debtorsTbody');
        tbody.innerHTML = '';

        if (!debtors || debtors.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="rev-empty-state"><i class="bi bi-check-circle"></i>No outstanding debtors for this period.</td></tr>`;
            return;
        }

        debtors.forEach(debtor => {
            const tr = document.createElement('tr');
            const badgeClass = debtor.days_outstanding > 30 ? 'rev-badge overdue' : 'rev-badge normal';

            tr.innerHTML = `
                <td class="sample-code">${debtor.sample_code}</td>
                <td>${debtor.received_date}</td>
                <td>${debtor.client_name}</td>
                <td>${debtor.phone_primary}</td>
                <td><span class="${badgeClass}">${debtor.days_outstanding} Days</span></td>
                <td>${formatCurrency(debtor.outstanding_amount)}</td>
            `;
            tbody.appendChild(tr);
        });
    };

    /**
     * Boot up event listeners and initial fetch
     */
    const init = () => {
        elements.btnYTD.addEventListener('click', () => { setDateRange('ytd'); fetchRevenueData(); });
        elements.btnMTD.addEventListener('click', () => { setDateRange('mtd'); fetchRevenueData(); });
        elements.btnToday.addEventListener('click', () => { setDateRange('today'); fetchRevenueData(); });
        
        elements.btnFetch.addEventListener('click', fetchRevenueData);
        
        // Remove active class from presets if manual date is picked
        [elements.startDate, elements.endDate].forEach(input => {
            input.addEventListener('change', () => {
                document.querySelectorAll('.rev-btn-preset').forEach(btn => btn.classList.remove('active'));
            });
        });

        // Initial Data Fetch
        fetchRevenueData();
    };

    return { init };
})();

// Start on DOM Content Loaded
document.addEventListener('DOMContentLoaded', RevenueAnalytics.init);
