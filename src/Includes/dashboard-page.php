

    <!-- Dashboard content wrapped in container -->
    <div class="dashboard-container">
      <div class="dashboard-content-wrapper">
        <!-- Page Header -->
        <!-- <div class="page-header">
          <h1 class="page-title">Dashboard Overview</h1>
          <div class="page-subtitle">
            <span class="current-date">Year 2025 Performance</span>
            <span>•</span>
            <span>Last updated: Just now</span>
          </div>
        </div> -->

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

        <!-- Recent Samples Table 
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
        </div> -->
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