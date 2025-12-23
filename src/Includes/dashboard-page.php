<style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      :root {
        --primary: #2563eb;
        --primary-dark: #1e40af;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-900: #111827;
      }

      /* REMOVED body padding - this was causing the margin issue */
      body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
          sans-serif;
        background: var(--gray-50);
        /* padding: 20px; */ /* REMOVED */
      }

      /* Dashboard Container - matches client management structure */
      .dashboard-container {
        padding: 20px;
        max-width: 100%;
        overflow-x: hidden; /* Prevent horizontal scroll */
      }

      /* Ensure content stays within bounds when sidebar expands/collapses */
      @media (min-width: 992px) {
        .dashboard-container {
          transition: padding-left 0.3s ease;
        }
      }

      .page-header {
        margin-bottom: 32px;
        animation: fadeIn 0.5s;
      }

      .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 8px;
      }

      .page-subtitle {
        color: var(--gray-600);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .current-date {
        font-weight: 500;
        color: var(--primary);
      }

      /* Period Selector */
      .period-selector {
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        animation: slideDown 0.5s;
        flex-wrap: wrap;
      }

      .period-btn {
        padding: 8px 20px;
        border: 2px solid var(--gray-200);
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: var(--gray-700);
        transition: all 0.3s;
      }

      .period-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
      }

      .period-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
      }

      /* Comparison Cards */
      .comparison-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
      }

      .comparison-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
        animation: fadeInUp 0.6s;
        position: relative;
        overflow: hidden;
      }

      .comparison-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--info));
        transform: scaleX(0);
        transition: transform 0.3s;
      }

      .comparison-card:hover::before {
        transform: scaleX(1);
      }

      .comparison-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-4px);
      }

      .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
      }

      .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
      }

      .icon-blue {
        background: rgba(37, 99, 235, 0.1);
      }
      .icon-green {
        background: rgba(16, 185, 129, 0.1);
      }
      .icon-orange {
        background: rgba(245, 158, 11, 0.1);
      }
      .icon-red {
        background: rgba(239, 68, 68, 0.1);
      }
      .icon-purple {
        background: rgba(139, 92, 246, 0.1);
      }

      .card-title {
        font-size: 13px;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .card-value {
        font-size: 36px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 12px;
      }

      .comparison-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-top: 1px solid var(--gray-100);
      }

      .comparison-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
      }

      .comparison-label {
        font-size: 12px;
        color: var(--gray-600);
      }

      .comparison-value {
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-900);
      }

      .comparison-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 8px;
      }

      .badge-positive {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
      }

      .badge-negative {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
      }

      .badge-neutral {
        background: rgba(107, 114, 128, 0.1);
        color: var(--gray-600);
      }

      /* Charts Section */
      .charts-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 24px;
      }

      @media (min-width: 992px) {
        .charts-grid {
          grid-template-columns: 2fr 1fr;
        }
      }

      .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        animation: fadeInUp 0.7s;
      }

      .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
      }

      .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-900);
      }

      .chart-subtitle {
        font-size: 13px;
        color: var(--gray-600);
        margin-top: 4px;
      }

      .chart-toggle {
        display: flex;
        gap: 8px;
        background: var(--gray-100);
        padding: 4px;
        border-radius: 8px;
      }

      .toggle-btn {
        padding: 6px 12px;
        border: none;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-600);
        transition: all 0.3s;
      }

      .toggle-btn.active {
        background: white;
        color: var(--gray-900);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      }

      .chart-wrapper {
        position: relative;
        height: 320px;
      }

      /* Table Section */
      .table-section {
        margin-bottom: 24px;
      }

      .table-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        animation: fadeInUp 0.8s;
        overflow-x: auto;
      }

      .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
      }

      .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-900);
      }

      .view-all-btn {
        padding: 8px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
      }

      .view-all-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
      }

      .responsive-table {
        width: 100%;
        min-width: 600px;
      }

      .responsive-table th {
        background: var(--gray-50);
        padding: 12px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-200);
      }

      .responsive-table td {
        padding: 12px;
        font-size: 14px;
        color: var(--gray-900);
        border-bottom: 1px solid var(--gray-100);
      }

      .responsive-table tr:hover {
        background: var(--gray-50);
      }

      .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
      }

      .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
      }

      .status-progress {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
      }

      .status-completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
      }

      .status-delivered {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
      }

      .action-btn {
        padding: 6px 12px;
        background: transparent;
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        color: var(--gray-700);
      }

      .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
      }

      /* Animations */
      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .comparison-grid {
          grid-template-columns: 1fr;
        }

        .charts-grid {
          grid-template-columns: 1fr;
        }

        .period-selector {
          flex-direction: column;
        }

        .period-btn {
          width: 100%;
        }

        .card-value {
          font-size: 28px;
        }

        .chart-wrapper {
          height: 250px;
        }
      }

      /* Ensure proper spacing and layout */
      .dashboard-content-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
      }
    </style>

    <!-- Dashboard content wrapped in container -->
    <div class="dashboard-container">
      <div class="dashboard-content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
          <h1 class="page-title">Dashboard Overview</h1>
          <div class="page-subtitle">
            <span class="current-date">Year 2025 Performance</span>
            <span>•</span>
            <span>Last updated: Just now</span>
          </div>
        </div>

        <!-- Period Selector -->
        <div class="period-selector">
          <button class="period-btn" onclick="changePeriod('week')">This Week</button>
          <button class="period-btn" onclick="changePeriod('month')">This Month</button>
          <button class="period-btn active" onclick="changePeriod('year')">This Year</button>
          <button class="period-btn" onclick="changePeriod('custom')">Custom Range</button>
        </div>

        <!-- Comparison Cards -->
        <div class="comparison-grid">
          <!-- Samples Card -->
          <div class="comparison-card">
            <div class="card-header">
              <div>
                <div class="card-title">Total Samples</div>
                <div class="card-value">247</div>
              </div>
              <div class="card-icon icon-blue">🧪</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-item">
                <span class="comparison-label">2025 (YTD)</span>
                <span class="comparison-value">247</span>
              </div>
              <div class="comparison-item">
                <span class="comparison-label">2024 (Full Year)</span>
                <span class="comparison-value">228</span>
              </div>
              <div class="comparison-item">
                <span class="comparison-label">Monthly Avg</span>
                <span class="comparison-value">21</span>
              </div>
            </div>
            <div class="comparison-badge badge-positive">
              ↑ 8.3% vs 2024
            </div>
          </div>

          <!-- Revenue Card -->
          <div class="comparison-card">
            <div class="card-header">
              <div>
                <div class="card-title">Total Revenue</div>
                <div class="card-value">LKR 5.88M</div>
              </div>
              <div class="card-icon icon-green">💰</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-item">
                <span class="comparison-label">2025 (YTD)</span>
                <span class="comparison-value">LKR 5.88M</span>
              </div>
              <div class="comparison-item">
                <span class="comparison-label">2024 (Full Year)</span>
                <span class="comparison-value">LKR 5.43M</span>
              </div>
              <div class="comparison-item">
                <span class="comparison-label">Projected 2025</span>
                <span class="comparison-value">LKR 6.12M</span>
              </div>
            </div>
            <div class="comparison-badge badge-positive">
              ↑ 8.3% vs 2024
            </div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
          <!-- Main Trend Chart -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Samples & Revenue Trends</h3>
                <p class="chart-subtitle">12-month comparison</p>
              </div>
              <div class="chart-toggle">
                <button class="toggle-btn active" onclick="changeChartView('both')">Both</button>
                <button class="toggle-btn" onclick="changeChartView('samples')">Samples</button>
                <button class="toggle-btn" onclick="changeChartView('revenue')">Revenue</button>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="trendsChart"></canvas>
            </div>
          </div>

          <!-- Distribution Chart -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Test Categories</h3>
                <p class="chart-subtitle">Distribution by type</p>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="distributionChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Additional Charts -->
        <div class="charts-grid">
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Top 5 Clients Comparison</h3>
                <p class="chart-subtitle">2024 vs 2025 Revenue</p>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="clientYearlyChart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Test Type Distribution</h3>
                <p class="chart-subtitle">This year</p>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="testTypeChart"></canvas>
            </div>
          </div>
        </div>

        <!-- More Charts -->
        <div class="charts-grid">
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Client Activity</h3>
                <p class="chart-subtitle">New vs Returning Clients</p>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="clientActivityChart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Quarterly Revenue</h3>
                <p class="chart-subtitle">2025 Performance</p>
              </div>
            </div>
            <div class="chart-wrapper">
              <canvas id="quarterlyChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Recent Samples Table -->
        <div class="table-section">
          <div class="table-card">
            <div class="section-header">
              <h3 class="section-title">Recent Sample Submissions</h3>
              <button class="view-all-btn" onclick="viewAllSamples()">View All</button>
            </div>
            <div style="overflow-x: auto;">
              <table class="responsive-table">
                <thead>
                  <tr>
                    <th>Sample Code</th>
                    <th>Client</th>
                    <th>Test Type</th>
                    <th>Submission Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>NARA-2025-0247</strong></td>
                    <td>Ocean Fisheries Ltd</td>
                    <td>Water Quality + Microbiology</td>
                    <td>Dec 22, 2025</td>
                    <td><span class="status-badge status-pending">Pending Analysis</span></td>
                    <td><button class="action-btn" onclick="viewSample('NARA-2025-0247')">View</button></td>
                  </tr>
                  <tr>
                    <td><strong>NARA-2025-0246</strong></td>
                    <td>Marine Foods Corp</td>
                    <td>Food Safety Analysis</td>
                    <td>Dec 22, 2025</td>
                    <td><span class="status-badge status-progress">In Progress</span></td>
                    <td><button class="action-btn" onclick="viewSample('NARA-2025-0246')">View</button></td>
                  </tr>
                  <tr>
                    <td><strong>NARA-2025-0245</strong></td>
                    <td>Coastal Aquaculture</td>
                    <td>Swab Test Package</td>
                    <td>Dec 21, 2025</td>
                    <td><span class="status-badge status-progress">In Progress</span></td>
                    <td><button class="action-btn" onclick="viewSample('NARA-2025-0245')">View</button></td>
                  </tr>
                  <tr>
                    <td><strong>NARA-2025-0244</strong></td>
                    <td>Island Seafood Processing</td>
                    <td>Regular Water Test</td>
                    <td>Dec 21, 2025</td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                    <td><button class="action-btn" onclick="viewSample('NARA-2025-0244')">View</button></td>
                  </tr>
                  <tr>
                    <td><strong>NARA-2025-0243</strong></td>
                    <td>Bay Area Fisheries</td>
                    <td>Combo Package - Premium</td>
                    <td>Dec 20, 2025</td>
                    <td><span class="status-badge status-delivered">Report Delivered</span></td>
                    <td><button class="action-btn" onclick="viewSample('NARA-2025-0243')">View</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Sample chart data
      const topClients = [
        { name: "Ocean Fisheries", thisYear: 548000, lastYear: 512000 },
        { name: "Marine Foods", thisYear: 492000, lastYear: 468000 },
        { name: "Coastal Aqua", thisYear: 445000, lastYear: 425000 },
        { name: "Island Seafood", thisYear: 398000, lastYear: 385000 },
        { name: "Bay Fisheries", thisYear: 367000, lastYear: 352000 },
      ];

      // Samples & Revenue Trends Chart
      const trendsCtx = document.getElementById("trendsChart").getContext("2d");
      new Chart(trendsCtx, {
        type: "bar",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
          datasets: [
            {
              label: "Samples",
              data: [18, 21, 19, 17, 23, 24, 20, 22, 26, 23, 21, 24],
              backgroundColor: "rgba(37, 99, 235, 0.7)",
              yAxisID: "y",
            },
            {
              label: "Revenue (K)",
              data: [39, 43, 41, 37, 49, 52, 46, 48, 53, 51, 48, 52],
              type: "line",
              borderColor: "#10B981",
              backgroundColor: "rgba(16, 185, 129, 0.1)",
              tension: 0.4,
              fill: true,
              yAxisID: "y1",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: "index",
            intersect: false,
          },
          scales: {
            y: {
              type: "linear",
              position: "left",
              title: { display: true, text: "Samples" },
            },
            y1: {
              type: "linear",
              position: "right",
              title: { display: true, text: "Revenue (K)" },
              grid: { drawOnChartArea: false },
            },
          },
        },
      });

      // Test Distribution Chart
      const distCtx = document
        .getElementById("distributionChart")
        .getContext("2d");
      new Chart(distCtx, {
        type: "doughnut",
        data: {
          labels: [
            "Water Tests",
            "Food Tests",
            "Microbiology",
            "Combo Tests",
            "Other",
          ],
          datasets: [
            {
              data: [38, 31, 18, 10, 3],
              backgroundColor: [
                "#2563EB",
                "#10B981",
                "#F59E0B",
                "#8B5CF6",
                "#6B7280",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "bottom" },
          },
        },
      });

      // Client Yearly Comparison Chart
      const clientYearlyCtx = document
        .getElementById("clientYearlyChart")
        .getContext("2d");
      new Chart(clientYearlyCtx, {
        type: "bar",
        data: {
          labels: topClients.map((c) => c.name),
          datasets: [
            {
              label: "2024",
              data: topClients.map((c) => c.lastYear / 1000),
              backgroundColor: "rgba(107, 114, 128, 0.6)",
            },
            {
              label: "2025",
              data: topClients.map((c) => c.thisYear / 1000),
              backgroundColor: "#2563EB",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              title: { display: true, text: "Revenue (K)" },
              beginAtZero: true,
            },
          },
          plugins: {
            legend: { position: "top" },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return (
                    context.dataset.label +
                    ": LKR " +
                    context.parsed.y.toFixed(0) +
                    "K"
                  );
                },
              },
            },
          },
        },
      });

      // Test Type Distribution Chart
      const testTypeCtx = document
        .getElementById("testTypeChart")
        .getContext("2d");
      new Chart(testTypeCtx, {
        type: "pie",
        data: {
          labels: ["Regular Tests", "Combo Packages", "Swab Tests"],
          datasets: [
            {
              data: [142, 68, 37],
              backgroundColor: ["#2563EB", "#10B981", "#F59E0B"],
              borderWidth: 2,
              borderColor: "#ffffff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                padding: 15,
                font: { size: 12 },
              },
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed / total) * 100).toFixed(
                    1
                  );
                  return (
                    context.label +
                    ": " +
                    context.parsed +
                    " (" +
                    percentage +
                    "%)"
                  );
                },
              },
            },
          },
        },
      });

      // Client Activity Chart
      const clientActivityCtx = document
        .getElementById("clientActivityChart")
        .getContext("2d");
      new Chart(clientActivityCtx, {
        type: "line",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
          datasets: [
            {
              label: "New Clients",
              data: [3, 2, 4, 3, 5, 4, 3, 4, 5, 3, 4, 5],
              borderColor: "#2563EB",
              backgroundColor: "rgba(37, 99, 235, 0.1)",
              fill: true,
              tension: 0.4,
            },
            {
              label: "Returning Clients",
              data: [8, 10, 9, 8, 11, 12, 10, 11, 13, 11, 10, 12],
              borderColor: "#10B981",
              backgroundColor: "rgba(16, 185, 129, 0.1)",
              fill: true,
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "bottom" },
          },
        },
      });

      // Quarterly Revenue Chart
      const quarterlyCtx = document
        .getElementById("quarterlyChart")
        .getContext("2d");
      new Chart(quarterlyCtx, {
        type: "doughnut",
        data: {
          labels: [
            "Q1 (Jan-Mar)",
            "Q2 (Apr-Jun)",
            "Q3 (Jul-Sep)",
            "Q4 (Oct-Dec)",
          ],
          datasets: [
            {
              data: [123, 145, 152, 128],
              backgroundColor: ["#2563EB", "#10B981", "#F59E0B", "#8B5CF6"],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "bottom" },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed / total) * 100).toFixed(
                    1
                  );
                  return (
                    context.label +
                    ": LKR " +
                    context.parsed +
                    "K (" +
                    percentage +
                    "%)"
                  );
                },
              },
            },
          },
        },
      });

      // Interactive Functions
      function changePeriod(period) {
        document
          .querySelectorAll(".period-btn")
          .forEach((btn) => btn.classList.remove("active"));
        event.target.classList.add("active");
        console.log("Period changed to:", period);
      }

      function changeChartView(view) {
        document
          .querySelectorAll(".chart-toggle .toggle-btn")
          .forEach((btn) => btn.classList.remove("active"));
        event.target.classList.add("active");
        console.log("Chart view changed to:", view);
      }

      function viewSample(sampleCode) {
        console.log("View sample:", sampleCode);
        alert("Navigate to sample detail page for: " + sampleCode);
      }

      function viewAllSamples() {
        console.log("View all samples");
        alert("Navigate to all samples page");
      }
    </script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>