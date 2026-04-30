/**
 * ============================================================
 * RESULT ENTRY JAVASCRIPT MODULE
 * Laboratory Management System
 * Version 1.0 - Result Entry with Type-Aware Controls
 * ============================================================
 *
 * Features:
 * - Sample listing with real-time filtering
 * - Modal-based result entry form
 * - Type-aware controls (numeric/ND or present/absent)
 * - ESPC toggle support
 * - Auto-completion status on all results filled
 * - Toast notifications
 *
 * ============================================================
 */

const ResultEntry = (function () {
  "use strict";

  // ==================== CONFIGURATION ====================
  const CONFIG = {
    CONTROLLER_URL: "../../src/Controllers/ResultEntryController.php",
    DEBOUNCE_DELAY: 500,
    TOAST_DURATION: 4000,
  };

  // ==================== STATE ====================
  const STATE = {
    currentFilters: {
      search: "",
      status: "all",
      date_preset: "",
    },
    samples: [],
    isLoading: false,
    currentSampleId: null,
    formData: null,
  };

  // ==================== DOM ELEMENTS ====================
  const EL = {};

  // ==================== INITIALIZATION ====================
  function init() {
    cacheElements();
    attachEventListeners();
    initializeModal();
    loadSamples();
  }

  function cacheElements() {
    EL.searchInput = document.getElementById("reSearchInput");
    EL.statusFilter = document.getElementById("reStatusFilter");
    EL.datePreset = document.getElementById("reDatePreset");

    EL.table = document.getElementById("resultsTable");
    EL.tbody = EL.table.querySelector("tbody");
    EL.emptyState = document.getElementById("reEmptyState");

    EL.modal = document.getElementById("resultEntryModal");
    EL.modalSampleId = document.getElementById("modalResultSampleId");
    EL.modalSampleCode = document.getElementById("modalSampleCode");
    EL.modalClientName = document.getElementById("modalClientName");
    EL.modalBody = document.getElementById("resultEntryBody");
    EL.btnSave = document.getElementById("btnSaveResults");

    EL.toastContainer = document.getElementById("reToastContainer");

    // Verify critical elements
    [
      "searchInput",
      "statusFilter",
      "table",
      "tbody",
      "modal",
      "btnSave",
    ].forEach(function (k) {
      if (!EL[k]) throw new Error("Critical element missing: " + k);
    });
  }

  function attachEventListeners() {
    let debounce;
    EL.searchInput.addEventListener("input", function () {
      clearTimeout(debounce);
      debounce = setTimeout(function () {
        STATE.currentFilters.search = EL.searchInput.value.trim();
        loadSamples();
      }, CONFIG.DEBOUNCE_DELAY);
    });

    EL.statusFilter.addEventListener("change", function () {
      STATE.currentFilters.status = this.value;
      loadSamples();
    });

    EL.datePreset.addEventListener("change", function () {
      STATE.currentFilters.date_preset = this.value;
      loadSamples();
    });

    EL.btnSave.addEventListener("click", saveResults);

    EL.modal.addEventListener("hidden.bs.modal", function () {
      EL.modalBody.innerHTML =
        '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="text-muted mt-2 mb-0 small">Loading...</p></div>';
      EL.btnSave.disabled = true;
      STATE.currentSampleId = null;
      STATE.formData = null;
    });
  }

  function initializeModal() {
    if (typeof bootstrap !== "undefined" && EL.modal) {
      EL.modalInstance = new bootstrap.Modal(EL.modal);
    }
  }

  // ==================== DATA LOADING ====================
  function loadSamples() {
    if (STATE.isLoading) return;
    STATE.isLoading = true;
    showTableLoading();

    var fd = new FormData();
    fd.append("action", "fetchSamples");
    fd.append("search", STATE.currentFilters.search);
    fd.append("status", STATE.currentFilters.status);
    fd.append("date_preset", STATE.currentFilters.date_preset);

    fetch(CONFIG.CONTROLLER_URL, { method: "POST", body: fd })
      .then(function (r) {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then(function (data) {
        if (data.status === "success") {
          STATE.samples = data.data || [];
          renderTable(STATE.samples);
        } else {
          throw new Error(data.message || "Failed to load");
        }
      })
      .catch(function (err) {
        showToast("Failed to load samples: " + err.message, "danger");
        showEmptyState();
      })
      .finally(function () {
        STATE.isLoading = false;
      });
  }

  function showTableLoading() {
    EL.tbody.innerHTML =
      '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="text-muted mt-2 mb-0 small">Loading samples...</p></td></tr>';
    if (EL.emptyState) EL.emptyState.style.display = "none";
  }

  function showEmptyState() {
    EL.tbody.innerHTML = "";
    if (EL.emptyState) EL.emptyState.style.display = "block";
  }

  // ==================== TABLE RENDERING ====================
  function renderTable(samples) {
    if (!samples || samples.length === 0) {
      showEmptyState();
      return;
    }
    if (EL.emptyState) EL.emptyState.style.display = "none";
    EL.tbody.innerHTML = samples.map(createRow).join("");
    attachRowListeners();
  }

  function createRow(s) {
    var statusClass = getStatusClass(s.status);
    var progress = getProgressInfo(s);
    var date = formatDate(s.received_date);
    var isCompleted = s.status === "Completed";
    var isCancelled = s.status === "Cancelled";

    return (
      '<tr data-sample-id="' +
      s.sample_id +
      '">' +
      '<td class="px-3 py-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">' +
      esc(s.sample_code) +
      "</span></td>" +
      '<td class="px-3 py-3 client-name-column" data-label="Client:"><span class="fw-medium">' +
      esc(s.client_name) +
      "</span></td>" +
      '<td class="px-3 py-3 text-center" data-label="Status:"><span class="badge ' +
      statusClass +
      '">' +
      esc(s.status) +
      "</span></td>" +
      '<td class="px-3 py-3 text-center" data-label="Items:"><span class="fw-medium">' +
      s.item_count +
      "</span></td>" +
      '<td class="px-3 py-3 text-center" data-label="Progress:">' +
      progress.html +
      "</td>" +
      '<td class="px-3 py-3" data-label="Received:"><span class="text-muted">' +
      date +
      "</span></td>" +
      '<td class="px-3 py-3 text-center" data-label="">' +
      '<div class="d-flex gap-2 justify-content-center">' +
      (!isCompleted && !isCancelled
        ? '<button class="btn-re-action btn-assign" data-sample-id="' +
          s.sample_id +
          '" title="Enter Results"><i class="fa-solid fa-pen-to-square"></i></button>'
        : '<button class="btn-re-action btn-view-results" data-sample-id="' +
          s.sample_id +
          '" title="View Results"><i class="fa-solid fa-eye"></i></button>') +
      '<button class="btn-re-action btn-print-re" data-sample-id="' +
      s.sample_id +
      '" title="Print Results"><i class="fa-solid fa-print"></i></button>' +
      "</div></td>" +
      "</tr>"
    );
  }

  function getStatusClass(status) {
    var map = {
      Pending: "badge-pending",
      "In Progress": "badge-in-progress",
      Completed: "badge-completed",
      Cancelled: "badge-canceled",
    };
    return map[status] || "bg-secondary";
  }

  function getProgressInfo(s) {
    var total = parseInt(s.test_count) || 0;
    var done = parseInt(s.result_count) || 0;
    var pct = total > 0 ? Math.round((done / total) * 100) : 0;

    if (total === 0) {
      return { html: '<span class="text-muted small">No tests</span>' };
    }

    var color = pct === 100 ? "#16a34a" : pct > 0 ? "#3b82f6" : "#94a3b8";
    return {
      html:
        '<div class="re-progress-wrap">' +
        '<div class="re-progress-bar" style="width:' +
        pct +
        "%;background:" +
        color +
        '"></div>' +
        '</div><small class="text-muted">' +
        done +
        "/" +
        total +
        "</small>",
    };
  }

  function attachRowListeners() {
    document
      .querySelectorAll(".btn-assign, .btn-view-results")
      .forEach(function (btn) {
        btn.addEventListener("click", function () {
          openResultModal(parseInt(this.dataset.sampleId));
        });
      });
    document.querySelectorAll(".btn-print-re").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var sid = this.dataset.sampleId;
        window.location.href = "index.php?page=test-reports&sample_id=" + sid;
      });
    });
  }

  // ==================== RESULT MODAL ====================
  function openResultModal(sampleId) {
    STATE.currentSampleId = sampleId;
    EL.modalSampleId.value = sampleId;
    EL.btnSave.disabled = true;

    EL.modalBody.innerHTML =
      '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="text-muted mt-2 mb-0 small">Loading test form...</p></div>';

    if (EL.modalInstance) EL.modalInstance.show();

    var fd = new FormData();
    fd.append("action", "getForm");
    fd.append("sample_id", sampleId);

    fetch(CONFIG.CONTROLLER_URL, { method: "POST", body: fd })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.status === "success" && data.data) {
          STATE.formData = data.data;
          renderFormModal(data.data);
          EL.btnSave.disabled = false;
        } else {
          throw new Error(data.message || "Failed to load form");
        }
      })
      .catch(function (err) {
        EL.modalBody.innerHTML =
          '<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle me-2"></i>' +
          esc(err.message) +
          "</div>";
      });
  }

  function renderFormModal(data) {
    var sample = data.sample;
    var items = data.items;

    EL.modalSampleCode.textContent = sample.sample_code;
    EL.modalClientName.textContent = sample.client_name;

    var html =
      '<div class="re-sample-header mb-3">' +
      '<div class="row g-2">' +
      '<div class="col-md-3"><div class="re-info-chip"><small class="text-muted">Form No.</small><strong>' +
      esc(sample.form_number) +
      "</strong></div></div>" +
      '<div class="col-md-3"><div class="re-info-chip"><small class="text-muted">Received</small><strong>' +
      formatDate(sample.received_date) +
      "</strong></div></div>" +
      '<div class="col-md-3"><div class="re-info-chip"><small class="text-muted">Tentative</small><strong>' +
      formatDate(sample.tentative_date) +
      "</strong></div></div>" +
      '<div class="col-md-3"><div class="re-info-chip"><small class="text-muted">Status</small><strong>' +
      esc(sample.status) +
      "</strong></div></div>" +
      "</div>" +
      '<div class="row g-2 mt-2">' +
      '<div class="col-md-4">' +
      '<div class="re-info-chip re-date-chip" data-field="analysis_start_date">' +
      '<label for="reAnalysisStartDate" class="form-label mb-1 text-muted fw-bold" style="font-size:0.7rem;"><i class="fa-solid fa-calendar-play me-1"></i>ANALYSIS START DATE <span class="text-danger">*</span></label>' +
      '<input type="date" class="form-control form-control-sm" id="reAnalysisStartDate" value="' +
      (sample.analysis_start_date || sample.received_date || "") +
      '" max="' +
      new Date().toISOString().split("T")[0] +
      '">' +
      '<div class="re-error-label"></div>' +
      "</div></div>" +
      '<div class="col-md-4">' +
      '<div class="re-info-chip re-date-chip" data-field="analysis_end_date">' +
      '<label for="reAnalysisEndDate" class="form-label mb-1 text-muted fw-bold" style="font-size:0.7rem;"><i class="fa-solid fa-calendar-check me-1"></i>ANALYSIS END DATE <span class="text-danger">*</span></label>' +
      '<input type="date" class="form-control form-control-sm" id="reAnalysisEndDate" value="' +
      (sample.analysis_end_date || "") +
      '" max="' +
      new Date().toISOString().split("T")[0] +
      '">' +
      '<div class="re-error-label"></div>' +
      "</div></div>" +
      "</div>" +
      "</div>";

    if (!items || items.length === 0) {
      html +=
        '<div class="alert alert-warning">No sample items found for this submission.</div>';
      EL.modalBody.innerHTML = html;
      return;
    }

    html += '<div class="accordion" id="itemsAccordion">';

    items.forEach(function (item, idx) {
      var catBadge = getCategoryBadge(item.category_code, item.category_name);
      var isFirst = idx === 0;
      var collapseId = "collapse-item-" + item.sample_item_id;

      html +=
        '<div class="accordion-item re-accordion-item">' +
        '<h2 class="accordion-header">' +
        '<button class="accordion-button re-accordion-btn' +
        (isFirst ? "" : " collapsed") +
        '" type="button" data-bs-toggle="collapse" data-bs-target="#' +
        collapseId +
        '">' +
        '<span class="re-item-label">Item ' +
        (idx + 1) +
        ": <strong>" +
        esc(item.sample_name) +
        "</strong></span>" +
        '<span class="ms-2">' +
        catBadge +
        "</span>" +
        '<span class="ms-auto me-3 small text-muted">' +
        (item.tests ? item.tests.length : 0) +
        " test(s)</span>" +
        "</button></h2>" +
        '<div id="' +
        collapseId +
        '" class="accordion-collapse collapse' +
        (isFirst ? " show" : "") +
        '" data-bs-parent="#itemsAccordion">' +
        '<div class="accordion-body p-0">';

      if (!item.tests || item.tests.length === 0) {
        html +=
          '<div class="p-3 text-muted">No tests assigned to this item.</div>';
      } else {
        html += renderTestsTable(item);
      }

      html += "</div></div></div>";
    });

    html += "</div>";
    EL.modalBody.innerHTML = html;
    attachFormListeners();
  }

  function renderTestsTable(item) {
    var html =
      '<div class="table-responsive"><table class="table table-sm mb-0 re-tests-table">' +
      "<thead><tr>" +
      '<th class="px-3 py-2">Parameter</th>' +
      '<th class="px-3 py-2">Unit</th>' +
      '<th class="px-3 py-2">Method</th>' +
      '<th class="px-3 py-2" style="min-width:220px;">Result</th>' +
      '<th class="px-3 py-2 text-center" style="width:60px;">ESPC</th>' +
      "</tr></thead><tbody>";

    item.tests.forEach(function (test) {
      var paramLabel =
        esc(test.parameter_name) +
        (test.variant_name
          ? ' <small class="text-muted">(' +
            esc(test.variant_name) +
            ")</small>"
          : "");
      var existing = test.existing_result;

      html +=
        '<tr class="result-row" data-sample-test-id="' +
        test.sample_test_id +
        '" data-sample-item-id="' +
        test.sample_item_id +
        '" data-parameter-id="' +
        test.parameter_id +
        '" data-variant-id="' +
        (test.variant_id || "") +
        '" data-result-mode="' +
        test.result_mode +
        '" data-espc-applicable="' +
        test.espc_applicable +
        '">' +
        '<td class="px-3 py-2">' +
        paramLabel +
        "</td>" +
        '<td class="px-3 py-2"><span class="text-muted">' +
        formatUnitSup(test.unit_name) +
        "</span></td>" +
        '<td class="px-3 py-2"><span class="text-muted small">' +
        esc(test.method_name) +
        "</span></td>" +
        '<td class="px-3 py-2">' +
        renderResultControl(test, existing) +
        "</td>" +
        '<td class="px-3 py-2 text-center">' +
        renderEspcControl(test, existing) +
        "</td>" +
        "</tr>";
    });

    html += "</tbody></table></div>";
    return html;
  }

  function renderResultControl(test, existing) {
    var stid = test.sample_test_id;

    if (test.result_mode === "numeric_or_ND") {
      var selType = "";
      var numVal = "";

      if (existing && existing.result_value) {
        if (existing.result_value === "ND") {
          selType = "ND";
        } else {
          selType = "numeric";
          numVal = existing.result_value;
        }
      }

      var numDisabled = selType !== "numeric" ? "disabled" : "";

      return (
        '<div class="d-flex flex-column gap-1">' +
        '<div class="d-flex gap-2 align-items-center">' +
        '<select class="form-select form-select-sm re-result-type" data-test-id="' +
        stid +
        '" style="width:100px;">' +
        '<option value=""' +
        (selType === "" ? " selected" : "") +
        ">--</option>" +
        '<option value="numeric"' +
        (selType === "numeric" ? " selected" : "") +
        ">Numeric</option>" +
        '<option value="ND"' +
        (selType === "ND" ? " selected" : "") +
        ">ND</option>" +
        "</select>" +
        '<input type="text" class="form-control form-control-sm re-result-value" data-test-id="' +
        stid +
        '" value="' +
        esc(numVal) +
        '" placeholder="Value (e.g. <1, 10^5)" style="width:140px;" ' +
        numDisabled +
        ">" +
        "</div>" +
        '<div class="re-error-label"></div>' +
        "</div>"
      );
    } else if (test.result_mode === "present_or_absent") {
      var catVal =
        existing && existing.result_value ? existing.result_value : "";

      return (
        '<div class="d-flex flex-column gap-1">' +
        '<select class="form-select form-select-sm re-result-type" data-test-id="' +
        stid +
        '" style="width:140px;">' +
        '<option value=""' +
        (catVal === "" ? " selected" : "") +
        ">--</option>" +
        '<option value="Present"' +
        (catVal === "Present" ? " selected" : "") +
        ">Present</option>" +
        '<option value="Absent"' +
        (catVal === "Absent" ? " selected" : "") +
        ">Absent</option>" +
        "</select>" +
        '<div class="re-error-label"></div>' +
        "</div>"
      );
    }

    return '<span class="text-muted">N/A</span>';
  }

  function renderEspcControl(test, existing) {
    if (!test.espc_applicable) {
      return '<span class="text-muted">-</span>';
    }

    // Auto-check only if a prior result explicitly had ESPC checked
    var isChecked = existing ? existing.has_espc === 1 : false;
    var checkedAttr = isChecked ? " checked" : "";

    // Disable if result is ND or empty
    var resVal = existing && existing.result_value ? existing.result_value : "";
    var disabledAttr = resVal === "ND" || resVal === "" ? " disabled" : "";

    return (
      '<input type="checkbox" class="form-check-input re-espc-check" data-test-id="' +
      test.sample_test_id +
      '"' +
      checkedAttr +
      disabledAttr +
      ">"
    );
  }

  function getCategoryBadge(code, name) {
    var colors = {
      WAT: "background:rgba(59,130,246,0.15);color:#2563eb;border-color:rgba(59,130,246,0.3)",
      FSH: "background:rgba(245,158,11,0.15);color:#d97706;border-color:rgba(245,158,11,0.3)",
      SWB: "background:rgba(22,163,74,0.15);color:#15803d;border-color:rgba(22,163,74,0.3)",
      OTH: "background:rgba(100,116,139,0.15);color:#475569;border-color:rgba(100,116,139,0.3)",
    };
    var icons = { WAT: "💧", FSH: "🐟", SWB: "🧹", OTH: "📦" };
    var style = colors[code] || colors.OTH;
    var icon = icons[code] || icons.OTH;

    return (
      '<span class="badge re-cat-badge" style="' +
      style +
      '">' +
      icon +
      " " +
      esc(name) +
      "</span>"
    );
  }

  function attachFormListeners() {
    // Toggle numeric input when result type changes
    document.querySelectorAll(".re-result-type").forEach(function (sel) {
      sel.addEventListener("change", function () {
        var row = this.closest(".result-row");
        var mode = row.dataset.resultMode;
        var numInput = row.querySelector(".re-result-value");

        if (mode === "numeric_or_ND" && numInput) {
          if (this.value === "numeric") {
            numInput.disabled = false;
            numInput.focus();
          } else {
            numInput.disabled = true;
            numInput.value = "";
          }
        }
        validateResultRow(row);

        // Toggle ESPC based on selection
        var espcCheck = row.querySelector(".re-espc-check");
        if (espcCheck) {
          if (this.value === "ND" || this.value === "") {
            espcCheck.disabled = true;
            espcCheck.checked = false;
          } else {
            espcCheck.disabled = false;
          }
        }
      });
    });

    // Real-time validation for numeric values
    document.querySelectorAll(".re-result-value").forEach(function (input) {
      input.addEventListener("input", function () {
        validateResultRow(this.closest(".result-row"));
      });
    });

    // Real-time validation for dates
    ["reAnalysisStartDate", "reAnalysisEndDate"].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener("change", function () {
          validateDateField(this);
          // Also validate the other date to check cross-date logic (start <= end)
          var otherId =
            id === "reAnalysisStartDate"
              ? "reAnalysisEndDate"
              : "reAnalysisStartDate";
          var otherEl = document.getElementById(otherId);
          if (otherEl) validateDateField(otherEl);
        });
      }
    });
  }

  // ==================== VALIDATION LOGIC ====================

  function validateResultRow(row) {
    var mode = row.dataset.resultMode;
    var typeSel = row.querySelector(".re-result-type");
    var valInput = row.querySelector(".re-result-value");
    var type = typeSel ? typeSel.value : "";

    clearError(typeSel);
    if (valInput) clearError(valInput);

    if (mode === "numeric_or_ND") {
      if (type === "numeric") {
        var val = valInput.value.trim();
        if (!val) {
          showError(valInput, "Result value is required");
          return false;
        }
      }
    } else if (mode === "present_or_absent") {
      if (!type) {
        showError(typeSel, "Selection is required");
        return false;
      }
    }
    return true;
  }

  function validateDateField(el) {
    clearError(el);
    var val = el.value;
    if (!val) return true;

    // Use current local date in YYYY-MM-DD format for direct comparison
    var todayStr = new Date().toLocaleDateString("en-CA"); // 'en-CA' gives YYYY-MM-DD

    if (val > todayStr) {
      showError(el, "Future dates are not allowed");
      return false;
    }

    // Logic: Start Date <= End Date
    var startEl = document.getElementById("reAnalysisStartDate");
    var endEl = document.getElementById("reAnalysisEndDate");
    if (startEl && endEl && startEl.value && endEl.value) {
      var start = new Date(startEl.value);
      var end = new Date(endEl.value);
      if (el.id === "reAnalysisEndDate" && end < start) {
        showError(el, "End date cannot be earlier than start date");
        return false;
      }
    }

    return true;
  }

  function showError(el, message) {
    el.classList.add("is-invalid");
    var container =
      el.closest(".flex-column") ||
      el.closest(".re-date-chip") ||
      el.parentElement;
    var label = container.querySelector(".re-error-label");
    if (label) {
      label.textContent = message;
      label.style.display = "block";
    }
  }

  function clearError(el) {
    el.classList.remove("is-invalid");
    var container =
      el.closest(".flex-column") ||
      el.closest(".re-date-chip") ||
      el.parentElement;
    var label = container.querySelector(".re-error-label");
    if (label) {
      label.textContent = "";
      label.style.display = "none";
    }
  }

  // ==================== SAVE RESULTS ====================
  function saveResults() {
    var sampleId = STATE.currentSampleId;
    if (!sampleId) return;

    var rows = document.querySelectorAll(".result-row");
    var results = [];
    var hasError = false;

    // Validate Dates first (MANDATORY per user request)
    var startDateEl = document.getElementById("reAnalysisStartDate");
    var endDateEl = document.getElementById("reAnalysisEndDate");

    if (startDateEl) {
      if (!startDateEl.value) {
        showError(startDateEl, "Start date is mandatory");
        hasError = true;
      } else if (!validateDateField(startDateEl)) {
        hasError = true;
      }
    }

    if (endDateEl) {
      if (!endDateEl.value) {
        showError(endDateEl, "End date is mandatory");
        hasError = true;
      } else if (!validateDateField(endDateEl)) {
        hasError = true;
      }
    }

    rows.forEach(function (row) {
      var mode = row.dataset.resultMode;
      var typeSel = row.querySelector(".re-result-type");
      var valInput = row.querySelector(".re-result-value");
      var espcCheck = row.querySelector(".re-espc-check");

      var resultType = typeSel ? typeSel.value : "";

      if (mode === "present_or_absent") {
        // Must select Present or Absent — never leave as --
        if (!resultType) {
          showError(typeSel, "Please select Present or Absent.");
          hasError = true;
          return;
        }
      }

      if (mode === "numeric_or_ND") {
        // Must choose either Numeric or ND
        if (!resultType) {
          showError(typeSel, "Please select Numeric or ND.");
          hasError = true;
          return;
        }
        // If Numeric selected, value must not be empty
        if (resultType === "numeric") {
          var val = valInput ? valInput.value.trim() : "";
          if (!val) {
            showError(valInput, "A numeric value is required.");
            hasError = true;
            return;
          }
        }
      }

      // Live row validation check
      if (!validateResultRow(row)) hasError = true;

      if (!resultType) return;

      var entry = {
        sample_test_id: row.dataset.sampleTestId,
        sample_item_id: row.dataset.sampleItemId,
        parameter_id: row.dataset.parameterId,
        variant_id: row.dataset.variantId || null,
        result_type: resultType,
        result_value: valInput ? valInput.value : null,
        has_espc: espcCheck ? (espcCheck.checked ? 1 : 0) : 0,
      };

      results.push(entry);
    });

    if (hasError) {
      showToast(
        "Please check again and fill all results correctly.",
        "warning",
      );
      // Find first error and scroll to it
      var firstError = document.querySelector(".is-invalid");
      if (firstError)
        firstError.scrollIntoView({ behavior: "smooth", block: "center" });
      return;
    }

    if (results.length === 0) {
      showToast("Please enter at least one result before saving.", "warning");
      return;
    }

    // Disable button
    EL.btnSave.disabled = true;
    EL.btnSave.innerHTML =
      '<i class="bi bi-hourglass-split me-1"></i>Saving...';

    var fd = new FormData();
    fd.append("action", "saveResults");
    fd.append("sample_id", sampleId);
    fd.append("results", JSON.stringify(results));

    // Append analysis dates from date pickers
    var startDateEl = document.getElementById("reAnalysisStartDate");
    var endDateEl = document.getElementById("reAnalysisEndDate");
    if (startDateEl && startDateEl.value) {
      fd.append("analysis_start_date", startDateEl.value);
    }
    if (endDateEl && endDateEl.value) {
      fd.append("analysis_end_date", endDateEl.value);
    }

    // Get CSRF Token from meta tag or window object
    var csrfToken =
      window.CSRF_TOKEN ||
      document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") ||
      "";
    fd.append("csrf_token", csrfToken);

    fetch(CONFIG.CONTROLLER_URL, { method: "POST", body: fd })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.status === "success") {
          var msg = data.message || "Results saved!";
          if (data.status_changed) {
            msg += " Sample marked as Completed ✅";
          }
          showToast(msg, "success");

          if (EL.modalInstance) EL.modalInstance.hide();
          loadSamples();
        } else {
          throw new Error(data.message || "Failed to save");
        }
      })
      .catch(function (err) {
        showToast("Save failed: " + err.message, "danger");
      })
      .finally(function () {
        EL.btnSave.disabled = false;
        EL.btnSave.innerHTML =
          '<i class="bi bi-check-circle me-1"></i>Save All Results';
      });
  }

  // ==================== TOAST NOTIFICATIONS ====================
  function showToast(message, type) {
    type = type || "info";
    var colors = {
      success: "text-bg-success",
      danger: "text-bg-danger",
      warning: "text-bg-warning",
      info: "text-bg-info",
    };
    var icons = {
      success: "bi-check-circle-fill",
      danger: "bi-exclamation-triangle-fill",
      warning: "bi-exclamation-circle-fill",
      info: "bi-info-circle-fill",
    };

    var toastEl = document.createElement("div");
    toastEl.className =
      "toast align-items-center " + (colors[type] || colors.info) + " border-0";
    toastEl.setAttribute("role", "alert");
    toastEl.innerHTML =
      '<div class="d-flex">' +
      '<div class="toast-body">' +
      '<i class="bi ' +
      (icons[type] || icons.info) +
      ' flex-shrink-0 me-2"></i>' +
      "<span>" +
      esc(message) +
      "</span>" +
      "</div>" +
      '<button type="button" class="btn-close ' +
      (type === "warning" ? "btn-close-black" : "btn-close-white") +
      ' me-2 m-auto" data-bs-dismiss="toast"></button>' +
      "</div>";

    if (EL.toastContainer) {
      EL.toastContainer.appendChild(toastEl);
      if (typeof bootstrap !== "undefined") {
        var bsToast = new bootstrap.Toast(toastEl, {
          delay: CONFIG.TOAST_DURATION || 4000,
        });
        bsToast.show();
        toastEl.addEventListener("hidden.bs.toast", function () {
          toastEl.remove();
        });
      }
    }
  }

  // ==================== UTILITIES ====================
  function formatDate(d) {
    if (!d) return "-";
    var parts = d.split("-");
    if (parts.length === 3) return parts[2] + "/" + parts[1] + "/" + parts[0];
    return d;
  }

  function esc(str) {
    if (!str) return "";
    var el = document.createElement("span");
    el.textContent = str;
    return el.innerHTML;
  }

  // Converts "cm^2" → "cm<sup>2</sup>" after escaping
  function formatUnitSup(str) {
    return esc(str).replace(/\^(\d+)/g, "<sup>$1</sup>");
  }

  // ==================== PUBLIC API ====================
  return { init: init };
})();
