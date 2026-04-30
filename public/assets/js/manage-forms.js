/**
 * Manage Forms JavaScript
 * Handles unified table loading and single-form view actions for four different form pages.
 */
document.addEventListener("DOMContentLoaded", function () {
  const table = document.getElementById("formsTable");
  if (!table) return;

  const formType = table.getAttribute("data-form-type");
  const tableBody = document.getElementById("tableBody");
  const emptyState = document.getElementById("emptyState");
  const searchInput = document.getElementById("searchInput");

  let allLoadedSamples = [];

  loadSamples();

  const paymentStatusFilter = document.getElementById("paymentStatusFilter");
  const statusFilter = document.getElementById("statusFilter");
  const dateFilter = document.getElementById("dateFilter");
  const customDateForm = document.getElementById("customDateForm");
  const customDateFrom = document.getElementById("customDateFrom");
  const customDateTo = document.getElementById("customDateTo");
  const btnResetFilters = document.getElementById("btnResetFilters");

  // Set max date for custom date pickers to today
  if (customDateFrom && customDateTo) {
    const todayStr = new Date().toISOString().split("T")[0];
    customDateFrom.setAttribute("max", todayStr);
    customDateTo.setAttribute("max", todayStr);
  }
  function applyFilters() {
    const term = (searchInput ? searchInput.value : "").toLowerCase().trim();
    const paymentFilterVal = paymentStatusFilter
      ? paymentStatusFilter.value
      : "";
    const statusFilterVal = statusFilter ? statusFilter.value : "";

    const filtered = allLoadedSamples.filter((sample) => {
      // 1. Text Search
      let textMatch = true;
      if (term) {
        const code = (sample.sample_code || "").toLowerCase();
        const client = (sample.client_name || "").toLowerCase();
        textMatch = code.includes(term) || client.includes(term);
      }

      // 2. Dropdown Filters
      let dropdownMatch = true;
      if (formType === "sacf" && paymentFilterVal) {
        const pStatus = sample.payment_status || "Pending";
        dropdownMatch = pStatus === paymentFilterVal;
      } else if (formType === "aif" && statusFilterVal) {
        const sStatus = sample.status || "Pending";
        dropdownMatch = sStatus === statusFilterVal;
      }

      // 3. Date Filter
      let dateMatch = true;
      const dateFilterVal = dateFilter ? dateFilter.value : "all";
      if (dateFilterVal !== "all" && sample.received_date) {
        const sampleDate = new Date(sample.received_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (dateFilterVal === "today") {
          dateMatch = sampleDate >= today;
        } else if (dateFilterVal === "7days") {
          const d = new Date(today);
          d.setDate(d.getDate() - 7);
          dateMatch = sampleDate >= d;
        } else if (dateFilterVal === "30days") {
          const d = new Date(today);
          d.setDate(d.getDate() - 30);
          dateMatch = sampleDate >= d;
        } else if (dateFilterVal === "this_month") {
          dateMatch =
            sampleDate.getMonth() === today.getMonth() &&
            sampleDate.getFullYear() === today.getFullYear();
        } else if (dateFilterVal === "custom") {
          let fromDate = customDateFrom ? new Date(customDateFrom.value) : null;
          let toDate = customDateTo ? new Date(customDateTo.value) : null;

          if (fromDate && !isNaN(fromDate.getTime())) {
            fromDate.setHours(0, 0, 0, 0);
            if (sampleDate < fromDate) dateMatch = false;
          }
          if (toDate && !isNaN(toDate.getTime())) {
            toDate.setHours(23, 59, 59, 999);
            if (sampleDate > toDate) dateMatch = false;
          }
        }
      }

      return textMatch && dropdownMatch && dateMatch;
    });

    renderTable(filtered, formType);
  }

  if (searchInput) {
    searchInput.addEventListener("keyup", applyFilters);
  }
  if (paymentStatusFilter) {
    paymentStatusFilter.addEventListener("change", applyFilters);
  }
  if (statusFilter) {
    statusFilter.addEventListener("change", applyFilters);
  }
  if (dateFilter) {
    dateFilter.addEventListener("change", function () {
      if (this.value === "custom") {
        if (customDateForm) {
          customDateForm.classList.remove("d-none");
          customDateForm.classList.add("d-flex");
        }
      } else {
        if (customDateForm) {
          customDateForm.classList.add("d-none");
          customDateForm.classList.remove("d-flex");
          if (customDateFrom) customDateFrom.value = "";
          if (customDateTo) customDateTo.value = "";
        }
      }
      applyFilters();
    });
  }
  if (customDateFrom) {
    customDateFrom.addEventListener("change", applyFilters);
  }
  if (customDateTo) {
    customDateTo.addEventListener("change", applyFilters);
  }
  if (btnResetFilters) {
    btnResetFilters.addEventListener("click", function () {
      if (searchInput) searchInput.value = "";
      if (paymentStatusFilter) paymentStatusFilter.value = "";
      if (statusFilter) statusFilter.value = "";
      if (dateFilter) dateFilter.value = "all";

      if (customDateForm) {
        customDateForm.classList.add("d-none");
        customDateForm.classList.remove("d-flex");
      }
      if (customDateFrom) customDateFrom.value = "";
      if (customDateTo) customDateTo.value = "";

      applyFilters();
    });
  }

  function loadSamples() {
    // Fetch all samples using sample-records-controller
    const formData = new FormData();
    formData.append("action", "fetchAll");

    fetch("../../src/Controllers/SampleRecordsController.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success" && data.data && data.data.length > 0) {
          allLoadedSamples = data.data;
          renderTable(allLoadedSamples, formType);
        } else {
          tableBody.innerHTML = "";
          table.style.display = "none";
          emptyState.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Error loading samples:", error);
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Failed to load samples.</td></tr>`;
      });
  }

  function renderTable(samples, type) {
    let rows = "";

    samples.forEach((sample) => {
      const sampleCode = escapeHtml(sample.sample_code || "N/A");
      const clientName = escapeHtml(sample.client_name || "Unknown");
      const receivedDate = escapeHtml(
        formatDate(sample.received_date) || "N/A",
      );

      if (type === "info") {
        rows += `
                <tr>
                    <td class="px-3 py-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">${sampleCode}</span></td>
                    <td class="px-3 py-3" data-label="Client:">${clientName}</td>
                    <td class="px-3 py-3 text-muted" data-label="Received Date:">${receivedDate}</td>
                    <td class="px-3 py-3 text-center" data-label="">
                        <button class="btn btn-action btn-action-primary view-form-btn" data-id="${sample.sample_id}" data-type="carousel">
                            <i class="fas fa-layer-group me-1"></i> View All Forms
                        </button>
                    </td>
                </tr>`;
      } else if (type === "saf") {
        rows += `
                <tr>
                    <td class="px-3 py-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">${sampleCode}</span></td>
                    <td class="px-3 py-3" data-label="Client:">${clientName}</td>
                    <td class="px-3 py-3 text-muted" data-label="Received Date:">${receivedDate}</td>
                    <td class="px-3 py-3 text-center" data-label="">
                        <button class="btn btn-action btn-action-success view-form-btn" data-id="${sample.sample_id}" data-type="saf">
                            <i class="fas fa-check-circle me-1"></i> View SAF
                        </button>
                    </td>
                </tr>`;
      } else if (type === "sacf") {
        const paymentStatus = escapeHtml(sample.payment_status || "Pending");
        let badgeClass = "payment-badge-pending";
        if (paymentStatus === "Paid") badgeClass = "payment-badge-paid";
        else if (paymentStatus === "Not Paid")
          badgeClass = "payment-badge-not-paid";

        rows += `
                <tr>
                    <td class="px-3 py-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">${sampleCode}</span></td>
                    <td class="px-3 py-3" data-label="Client:">${clientName}</td>
                    <td class="px-3 py-3" data-label="Payment Status:"><span class="payment-badge ${badgeClass}" style="min-width: 90px; text-align: center; justify-content: center;">${paymentStatus}</span></td>
                    <td class="px-3 py-3 text-muted" data-label="Received Date:">${receivedDate}</td>
                    <td class="px-3 py-3 text-center" data-label="">
                        <button class="btn btn-action btn-action-warning view-form-btn" data-id="${sample.sample_id}" data-type="sacf">
                            <i class="fas fa-file-invoice-dollar me-1"></i> View SAcF
                        </button>
                    </td>
                </tr>`;
      } else if (type === "aif") {
        const status = escapeHtml(sample.status || "Pending");
        let badgeClass = "badge-pending";
        if (status === "Completed") badgeClass = "badge-completed";
        else if (status === "In Progress") badgeClass = "badge-in-progress";
        else if (status === "Cancelled") badgeClass = "badge-cancelled";

        rows += `
                <tr>
                    <td class="px-3 py-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">${sampleCode}</span></td>
                    <td class="px-3 py-3" data-label="Client:">${clientName}</td>
                    <td class="px-3 py-3" data-label="Status:"><span class="status-badge ${badgeClass}">${status}</span></td>
                    <td class="px-3 py-3 text-muted" data-label="Received Date:">${receivedDate}</td>
                    <td class="px-3 py-3 text-center" data-label="">
                        <button class="btn btn-action btn-action-info view-form-btn" data-id="${sample.sample_id}" data-type="aif">
                            <i class="fas fa-microscope me-1"></i> View AIF
                        </button>
                    </td>
                </tr>`;
      }
    });

    tableBody.innerHTML = rows;

    // Attach event listeners to buttons
    document.querySelectorAll(".view-form-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const sampleId = this.getAttribute("data-id");
        const btnType = this.getAttribute("data-type");

        let url = "";
        if (btnType === "carousel") {
          // Info page: full carousel with all 3 forms
          url = `src/Controllers/FormsController.php?action=view&sample_id=${sampleId}`;
        } else {
          // SAF/SAcF/AIF pages: same carousel modal, but only active slide shown
          url = `src/Controllers/FormsController.php?action=view&type=${btnType}&sample_id=${sampleId}`;
        }

        // Open in new tab with popup features
        const w = window.screen.availWidth;
        const h = window.screen.availHeight;
        window.open(
          url,
          "_blank",
          `width=${w},height=${h},left=0,top=0,scrollbars=yes,resizable=yes`,
        );
      });
    });
  }

  function escapeHtml(unsafe) {
    if (!unsafe) return "";
    return unsafe
      .toString()
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatDate(dateString) {
    if (!dateString) return "";
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${year}-${month}-${day}`;
  }
});
