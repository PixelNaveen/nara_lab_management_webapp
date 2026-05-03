/**
 * Manage Invoices JavaScript
 * Laboratory Management System
 */

const ManageInvoices = (function () {
  "use strict";

  const CONFIG = {
    CONTROLLER_URL: "../../src/Controllers/SampleRecordsController.php",
    INVOICE_URL: "../../src/Controllers/InvoiceController.php",
    DEBOUNCE_DELAY: 500,
    TOAST_DURATION: 3000,
  };

  const STATE = {
    currentFilters: {
      search: "",
      payment_status: "all",
    },
    samples: [],
    isLoading: false,
  };

  const ELEMENTS = {
    searchInput: null,
    btnResetFilters: null,
    invoicesTable: null,
    emptyState: null,
    paymentStatusFilter: null,
    toastContainer: null,

    // Invoice Modal
    invoiceModal: null,
    invoiceModalInstance: null,
    invoiceSignatory: null,
    invoiceRequestDate: null,
    invoiceSampleId: null,
    btnPreviewInvoice: null,
    btnGenerateInvoice: null,
    invoiceErrorAlert: null,

    // Payment Modal
    paymentModal: null,
    paymentModalInstance: null,
    modalSampleId: null,
    modalSampleCode: null,
    modalClientName: null,
    modalAmount: null,
    modalPaymentStatus: null,
    modalReferenceNumber: null,
    referenceNumberGroup: null,
    modalPaymentDate: null,
    paymentDateGroup: null,
    paymentErrorAlert: null,
    btnSavePayment: null,
  };

  function init() {
    cacheElements();
    attachEventListeners();
    initializeModals();
    loadInvoices();
  }

  function cacheElements() {
    ELEMENTS.searchInput = document.getElementById("invoiceSearch");
    ELEMENTS.btnResetFilters = document.getElementById("btnResetFilters");
    ELEMENTS.invoicesTable = document.getElementById("invoicesTable");
    ELEMENTS.tableBody = ELEMENTS.invoicesTable.querySelector("tbody");
    ELEMENTS.emptyState = document.getElementById("emptyState");
    ELEMENTS.paymentStatusFilter = document.getElementById("paymentStatusFilter");
    ELEMENTS.toastContainer = document.getElementById("toastContainer");

    // Invoice Modal
    ELEMENTS.invoiceModal = document.getElementById("invoiceModal");
    ELEMENTS.invoiceSignatory = document.getElementById("invoiceSignatory");
    ELEMENTS.invoiceRequestDate = document.getElementById("invoiceRequestDate");
    ELEMENTS.invoiceSampleId = document.getElementById("invoiceSampleId");
    ELEMENTS.btnPreviewInvoice = document.getElementById("btnPreviewInvoice");
    ELEMENTS.btnGenerateInvoice = document.getElementById("btnGenerateInvoice");
    ELEMENTS.invoiceErrorAlert = document.getElementById("invoiceErrorAlert");

    // Payment Modal
    ELEMENTS.paymentModal = document.getElementById("paymentModal");
    ELEMENTS.modalSampleId = document.getElementById("modalSampleId");
    ELEMENTS.modalSampleCode = document.getElementById("modalSampleCode");
    ELEMENTS.modalClientName = document.getElementById("modalClientName");
    ELEMENTS.modalAmount = document.getElementById("modalAmount");
    ELEMENTS.modalPaymentStatus = document.getElementById("modalPaymentStatus");
    ELEMENTS.modalReferenceNumber = document.getElementById("modalReferenceNumber");
    ELEMENTS.referenceNumberGroup = document.getElementById("referenceNumberGroup");
    ELEMENTS.modalPaymentDate = document.getElementById("modalPaymentDate");
    ELEMENTS.paymentDateGroup = document.getElementById("paymentDateGroup");
    ELEMENTS.paymentErrorAlert = document.getElementById("paymentErrorAlert");
    ELEMENTS.btnSavePayment = document.getElementById("btnSavePayment");
  }

  function attachEventListeners() {
    let searchDebounceTimer;
    ELEMENTS.searchInput.addEventListener("input", function () {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        STATE.currentFilters.search = this.value.trim();
        loadInvoices();
      }, CONFIG.DEBOUNCE_DELAY);
    });

    ELEMENTS.paymentStatusFilter.addEventListener("change", function() {
        STATE.currentFilters.payment_status = this.value;
        loadInvoices();
    });

    ELEMENTS.btnResetFilters.addEventListener("click", function() {
        ELEMENTS.searchInput.value = "";
        ELEMENTS.paymentStatusFilter.value = "all";
        STATE.currentFilters.search = "";
        STATE.currentFilters.payment_status = "all";
        loadInvoices();
    });

    // Modal listeners
    ELEMENTS.modalPaymentStatus.addEventListener("change", handlePaymentStatusChange);
    ELEMENTS.btnSavePayment.addEventListener("click", savePaymentStatus);
    ELEMENTS.btnPreviewInvoice.addEventListener("click", previewInvoice);
    ELEMENTS.btnGenerateInvoice.addEventListener("click", generateInvoice);
  }

  function initializeModals() {
    if (typeof bootstrap !== "undefined") {
      ELEMENTS.invoiceModalInstance = new bootstrap.Modal(ELEMENTS.invoiceModal);
      ELEMENTS.paymentModalInstance = new bootstrap.Modal(ELEMENTS.paymentModal);
    }
  }

  function loadInvoices() {
    if (STATE.isLoading) return;
    STATE.isLoading = true;
    showLoading();

    const formData = new FormData();
    formData.append("action", "fetchAll");
    formData.append("search", STATE.currentFilters.search);
    formData.append("payment_status", STATE.currentFilters.payment_status);
    formData.append("status", "all"); // Overall sample status (optional)

    fetch(CONFIG.CONTROLLER_URL, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          STATE.samples = data.data || [];
          renderTable(STATE.samples);
        }
      })
      .catch((err) => console.error(err))
      .finally(() => {
        STATE.isLoading = false;
      });
  }

  function showLoading() {
    ELEMENTS.tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>`;
  }

  function renderTable(samples) {
    if (samples.length === 0) {
      ELEMENTS.tableBody.innerHTML = "";
      ELEMENTS.emptyState.style.display = "block";
      return;
    }
    ELEMENTS.emptyState.style.display = "none";
    ELEMENTS.tableBody.innerHTML = samples.map(s => createRow(s)).join("");
    attachRowListeners();
  }

  function createRow(s) {
    const paymentStatus = s.payment_status || "Pending";
    const paymentClass = getPaymentBadgeClass(paymentStatus);
    const paidDate = s.payment_date && s.payment_date !== "0000-00-00" ? formatDate(s.payment_date) : "-";
    const invoiceNum = s.invoice_number ? `<span class="badge badge-invoice">${s.invoice_number}</span>` : `<span class="text-muted small">Not Generated</span>`;

    return `
      <tr data-sample-id="${s.sample_id}">
        <td class="px-3 py-3 fw-bold text-primary">${escapeHtml(s.sample_code)}</td>
        <td class="px-3 py-3">${escapeHtml(s.client_name)}</td>
        <td class="px-3 py-3 fw-bold text-success">${formatCurrency(s.grand_total)}</td>
        <td class="px-3 py-3 text-center">
            <span class="badge ${paymentClass} payment-trigger" style="cursor:pointer" data-id="${s.sample_id}">
                ${paymentStatus === 'Paid' ? '<i class="fas fa-square-check"></i>' : (paymentStatus === 'Not Paid' ? '<i class="fas fa-square-xmark"></i>' : '<i class="fas fa-clock"></i>')}
                ${paymentStatus}
            </span>
        </td>
        <td class="px-3 py-3 text-center text-muted">${paidDate}</td>
        <td class="px-3 py-3 text-center">${invoiceNum}</td>
        <td class="px-3 py-3 text-end">
            <button class="btn btn-sm invoice-icon-btn btn-generate-invoice" data-id="${s.sample_id}" title="Generate Invoice">
                <i class="fas fa-file-invoice"></i>
            </button>
        </td>
      </tr>
    `;
  }

  function attachRowListeners() {
    document.querySelectorAll(".payment-trigger").forEach(el => {
        el.addEventListener("click", () => openPaymentModal(el.dataset.id));
    });
    document.querySelectorAll(".btn-generate-invoice").forEach(el => {
        el.addEventListener("click", () => openInvoiceModal(el.dataset.id));
    });
  }

  // --- Modal Logic (Extracted & Cleaned) ---

  function openPaymentModal(sampleId) {
    const sample = STATE.samples.find(s => s.sample_id == sampleId);
    if (!sample) return;

    ELEMENTS.modalSampleId.value = sample.sample_id;
    ELEMENTS.modalSampleCode.textContent = sample.sample_code;
    ELEMENTS.modalClientName.textContent = sample.client_name;
    ELEMENTS.modalAmount.textContent = formatCurrency(sample.grand_total);
    ELEMENTS.modalPaymentStatus.value = sample.payment_status || "";
    ELEMENTS.modalReferenceNumber.value = sample.payment_reference || "";
    ELEMENTS.modalPaymentDate.value = (sample.payment_date) ? sample.payment_date.substring(0, 10) : "";

    handlePaymentStatusChange();
    ELEMENTS.paymentModalInstance.show();
  }

  function handlePaymentStatusChange() {
    const isPaid = ELEMENTS.modalPaymentStatus.value === "Paid";
    ELEMENTS.referenceNumberGroup.style.display = isPaid ? "block" : "none";
    ELEMENTS.paymentDateGroup.style.display = isPaid ? "block" : "none";
  }

  function savePaymentStatus() {
    const formData = new FormData();
    formData.append("action", "updatePayment");
    formData.append("sample_id", ELEMENTS.modalSampleId.value);
    formData.append("payment_status", ELEMENTS.modalPaymentStatus.value);
    formData.append("reference_number", ELEMENTS.modalReferenceNumber.value);
    formData.append("payment_date", ELEMENTS.modalPaymentDate.value);

    fetch(CONFIG.CONTROLLER_URL, { method: "POST", body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          showSuccess("Payment status updated successfully!");
          ELEMENTS.paymentModalInstance.hide();
          loadInvoices();
        } else {
          showError(data.message || "Failed to update payment status");
          ELEMENTS.paymentErrorAlert.textContent = data.message;
          ELEMENTS.paymentErrorAlert.style.display = "block";
        }
      })
      .catch(err => showError("An error occurred while updating payment."));
  }

  function openInvoiceModal(sampleId) {
    const sample = STATE.samples.find(s => s.sample_id == sampleId);
    if (!sample) return;

    ELEMENTS.invoiceSampleId.value = sampleId;
    ELEMENTS.invoiceErrorAlert.style.display = "none";
    ELEMENTS.invoiceRequestDate.value = sample.received_date || "";
    
    // Load Signatories
    const formData = new FormData();
    formData.append("action", "getSignatories");

    fetch(CONFIG.INVOICE_URL, { method: "POST", body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          let opts = '<option value="">Select Signatory</option>';
          data.signatories.forEach(sig => {
            opts += `<option value="${sig.signatory_id}">${sig.full_name} - ${sig.title}</option>`;
          });
          ELEMENTS.invoiceSignatory.innerHTML = opts;
          ELEMENTS.invoiceModalInstance.show();
        }
      });
  }

  function previewInvoice() {
    const sid = ELEMENTS.invoiceSampleId.value;
    const date = ELEMENTS.invoiceRequestDate.value;
    window.open(`../../src/Views/invoice-print-template.php?sample_id=${sid}&request_date=${date}`, "_blank");
  }

  function generateInvoice() {
    const signatoryId = ELEMENTS.invoiceSignatory.value;
    if (!signatoryId) {
        showError("Please select a signatory first.");
        ELEMENTS.invoiceSignatory.classList.add("is-invalid");
        return;
    }
    ELEMENTS.invoiceSignatory.classList.remove("is-invalid");

    // Pre-open a blank window to bypass popup blockers
    const printWindow = window.open("", "_blank");
    printWindow.document.write("<html><head><title>Generating Invoice...</title></head><body><div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h3>Generating Invoice...</h3><p>Please wait while we prepare your document.</p></div></body></html>");

    const formData = new FormData();
    formData.append("action", "generate");
    formData.append("sample_id", ELEMENTS.invoiceSampleId.value);
    formData.append("signatory_id", signatoryId);
    formData.append("request_date", ELEMENTS.invoiceRequestDate.value);

    fetch(CONFIG.INVOICE_URL, { method: "POST", body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          showSuccess("Invoice generated successfully!");
          
          // Update the pre-opened window with the actual print template
          printWindow.location.href = `../../src/Views/invoice-print-template.php?invoice_id=${data.invoice_id}`;
          
          ELEMENTS.invoiceModalInstance.hide();
          loadInvoices();
        } else {
            printWindow.close(); // Close the blank window if failed
            showError(data.message || "Failed to generate invoice");
            ELEMENTS.invoiceErrorAlert.textContent = data.message;
            ELEMENTS.invoiceErrorAlert.style.display = "block";
        }
      })
      .catch(err => {
          printWindow.close();
          showError("An error occurred while generating invoice.");
      });
  }

  // --- Helpers ---
  function formatCurrency(val) { return "LKR " + parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2 }); }
  function formatDate(str) { if (!str) return "-"; const d = new Date(str); return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" }); }
  function escapeHtml(text) { const div = document.createElement("div"); div.textContent = text; return div.innerHTML; }
  function getPaymentBadgeClass(status) {
    if (status === "Paid") return "badge-paid";
    if (status === "Not Paid") return "badge-not-paid";
    return "badge-pending";
  }

  function showSuccess(msg) { showToast(msg, "success"); }
  function showError(msg) { showToast(msg, "danger"); }

  function showToast(message, type = "info") {
    if (!ELEMENTS.toastContainer) return;
    const id = "toast_" + Date.now();
    const icons = { success: "fa-circle-check", danger: "fa-circle-exclamation", info: "fa-circle-info" };
    const html = `
        <div id="${id}" class="toast align-items-center text-white bg-${type} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body p-3">
                    <i class="fas ${icons[type] || icons.info} me-2"></i> ${escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
    ELEMENTS.toastContainer.insertAdjacentHTML("beforeend", html);
    const el = document.getElementById(id);
    const toast = new bootstrap.Toast(el, { autohide: true, delay: CONFIG.TOAST_DURATION });
    toast.show();
    el.addEventListener("hidden.bs.toast", () => el.remove());
  }

  return { init };
})();

document.addEventListener("DOMContentLoaded", ManageInvoices.init);
