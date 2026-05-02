/**
 * Test Report Module
 * Laboratory Management System
 *
 * Handles the test report generation workflow:
 * - Loading completed samples into the table
 * - 2-step wizard (Layout → Signatories)
 * - Report generation via AJAX
 * - Opening print template in new tab
 *
 * @version 1.0
 */

const TestReport = (() => {
  "use strict";

  // ==================== CONFIGURATION ====================
  const API_URL = "src/Controllers/TestReportController.php";
  let currentStep = 1;
  let reportData = null;
  let allSignatories = [];
  let debounceTimer = null;

  // ==================== INITIALIZATION ====================

  function init() {
    bindEvents();
    loadSamples();
  }

  function bindEvents() {
    // Search & filters
    const searchInput = document.getElementById("trSearchInput");
    const datePreset = document.getElementById("trDatePreset");

    if (searchInput) {
      searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadSamples, 400);
      });
    }
    if (datePreset) {
      datePreset.addEventListener("change", loadSamples);
    }

    // Wizard navigation
    document
      .getElementById("btnWizardNext")
      ?.addEventListener("click", nextStep);
    document
      .getElementById("btnWizardBack")
      ?.addEventListener("click", prevStep);
    document
      .getElementById("btnGenerateReport")
      ?.addEventListener("click", generateReport);

    // Signatory dropdowns
    document
      .getElementById("signatoryLeft")
      ?.addEventListener("change", function () {
        updateSignatoryPreview("Left", this);
      });
    document
      .getElementById("signatoryRight")
      ?.addEventListener("change", function () {
        updateSignatoryPreview("Right", this);
      });

    // Reset wizard when modal closes
    document
      .getElementById("reportWizardModal")
      ?.addEventListener("hidden.bs.modal", resetWizard);
  }

  // ==================== LOAD SAMPLES TABLE ====================

  function loadSamples() {
    const search = document.getElementById("trSearchInput")?.value || "";
    const datePreset = document.getElementById("trDatePreset")?.value || "";
    const tbody = document.querySelector("#trSamplesTable tbody");

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2 mb-0 small">Loading completed samples...</p>
        </td></tr>`;

    fetch(
      `${API_URL}?action=fetchCompletedSamples&search=${encodeURIComponent(search)}&date_preset=${encodeURIComponent(datePreset)}`,
    )
      .then((r) => r.json())
      .then((res) => {
        if (res.status !== "success") throw new Error(res.message);
        renderSamplesTable(res.data);
      })
      .catch((err) => {
        console.error("Load samples error:", err);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle"></i> Failed to load samples
                </td></tr>`;
      });
  }

  function renderSamplesTable(samples) {
    const tbody = document.querySelector("#trSamplesTable tbody");
    const emptyState = document.getElementById("trEmptyState");

    if (!samples || samples.length === 0) {
      tbody.innerHTML = "";
      emptyState.style.display = "block";
      return;
    }

    emptyState.style.display = "none";

    tbody.innerHTML = samples
      .map((s) => {
        const hasReport = s.existing_report_ids != null;
        const reportBadge = hasReport
          ? `<span class="badge-status bg-success"><i class="fa-solid fa-circle-check me-1"></i>Generated</span>`
          : `<span class="badge-status bg-warning"><i class="fa-solid fa-clock me-1"></i>Pending</span>`;

        const displayDate = s.analysis_end_date || s.received_date;
        const testEndedDate = displayDate
          ? new Date(displayDate).toLocaleDateString("en-GB", {
              day: "2-digit",
              month: "short",
              year: "numeric",
            })
          : "—";

        // Pass the full comma-separated IDs string to printSaved
        const reportIdsParam = hasReport
          ? `'${s.existing_report_ids}'`
          : "null";

        return `<tr>
                <td class="px-3" data-label="Sample Code:"><span class="fw-bold text-primary" style="font-size: 11.5pt;">${escHtml(s.sample_code)}</span></td>
                <td class="px-3" data-label="Client:">${escHtml(s.client_name)}</td>
                <td class="px-3 text-center" data-label="Items:">${s.item_count}</td>
                <td class="px-3 text-center" data-label="Tests:">${s.result_count}/${s.test_count}</td>
                <td class="px-3" data-label="Test Ended:"><small>${testEndedDate}</small></td>
                <td class="px-3 text-center" data-label="Report:">${reportBadge}</td>
                <td class="px-3 text-center" data-label="">
                    <button class="btn-tr-action" 
                            onclick="TestReport.openWizard(${s.sample_id})" 
                            title="Generate / Print Report">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </td>
            </tr>`;
      })
      .join("");
  }

  // ==================== WIZARD ====================

  function openWizard(sampleId) {
    document.getElementById("wizardSampleId").value = sampleId;
    currentStep = 1;
    showStep(1);

    // Load preview data
    const modal = new bootstrap.Modal(
      document.getElementById("reportWizardModal"),
    );
    modal.show();

    loadPreviewData(sampleId);
  }

  function loadPreviewData(sampleId) {
    fetch(`${API_URL}?action=preview&sample_id=${sampleId}`)
      .then((r) => r.json())
      .then((res) => {
        if (res.status !== "success") throw new Error(res.message);
        reportData = res.data;
        renderStep1();
        loadSignatories(res.data.signatories);
        document.getElementById("wizardSampleCode").textContent =
          res.data.sample.sample_code;
      })
      .catch((err) => {
        console.error("Preview error:", err);
        showToast("Failed to load report data: " + err.message, "danger");
      });
  }

  function renderStep1() {
    if (!reportData) return;

    const items = reportData.items || [];

    // Detect mixed accreditation
    const hasAcc = items.some((i) => parseInt(i.is_slab_accredited || 0));
    const hasNonAcc = items.some((i) => !parseInt(i.is_slab_accredited || 0));
    const isMixed = hasAcc && hasNonAcc;

    // Show layout options if multiple items
    const layoutOptions = document.getElementById("layoutOptions");
    if (items.length > 1) {
      layoutOptions.style.display = "block";
      const note = document.getElementById("layoutNote");
      if (isMixed) {
        note.textContent = `${items.length} items detected (mixed accreditation). Combined will create 2 reports (accredited + non-accredited). Separate will create ${items.length} individual reports.`;
      } else {
        note.textContent = `${items.length} items can be combined into a single report with ${items.length} result columns, or separated into ${items.length} individual reports.`;
      }
    } else {
      layoutOptions.style.display = "none";
    }

    // Report type info
    const typeInfo = document.getElementById("reportTypeText");
    if (isMixed) {
      typeInfo.innerHTML =
        "<strong>Mixed Report</strong> — Contains both accredited and non-accredited items. The system will automatically split them into separate reports.";
    } else if (reportData.report_type === "accredited") {
      typeInfo.innerHTML =
        "<strong>Accredited Report</strong> — Will include 3 logos (Govt Seal, NARA, SLAB) and asterisks (*) on accredited parameters.";
    } else {
      typeInfo.innerHTML =
        "<strong>Non-Accredited Report</strong> — Will include NARA logo only. No accreditation marks.";
    }
  }

  function loadSignatories(sigData) {
    allSignatories = sigData.all || [];
    const defaults = sigData.defaults || {};

    const leftSelect = document.getElementById("signatoryLeft");
    const rightSelect = document.getElementById("signatoryRight");

    // Populate scientists (left)
    const scientists = allSignatories.filter(
      (s) => s.role_type === "scientist",
    );
    leftSelect.innerHTML =
      '<option value="">Select scientist...</option>' +
      scientists
        .map(
          (s) => `<option value="${s.signatory_id}" 
                data-title="${escHtml(s.title)}" 
                data-division="${escHtml(s.division)}"
                ${defaults.scientist && defaults.scientist.signatory_id == s.signatory_id ? "selected" : ""}>
                ${escHtml(s.full_name)}
            </option>`,
        )
        .join("");

    // Populate heads (right)
    const heads = allSignatories.filter((s) => s.role_type === "head");
    rightSelect.innerHTML =
      '<option value="">Select head...</option>' +
      heads
        .map(
          (s) => `<option value="${s.signatory_id}" 
                data-title="${escHtml(s.title)}" 
                data-division="${escHtml(s.division)}"
                ${defaults.head && defaults.head.signatory_id == s.signatory_id ? "selected" : ""}>
                ${escHtml(s.full_name)}
            </option>`,
        )
        .join("");

    // Trigger preview for defaults
    if (leftSelect.value) updateSignatoryPreview("Left", leftSelect);
    if (rightSelect.value) updateSignatoryPreview("Right", rightSelect);
  }

  function updateSignatoryPreview(side, selectEl) {
    const preview = document.getElementById(`sig${side}Preview`);
    const titleEl = document.getElementById(`sig${side}Title`);
    const divEl = document.getElementById(`sig${side}Division`);
    const opt = selectEl.options[selectEl.selectedIndex];

    if (selectEl.value) {
      titleEl.textContent = opt.dataset.title || "";
      divEl.textContent = opt.dataset.division || "";
      preview.style.display = "block";
    } else {
      preview.style.display = "none";
    }
  }

  // ==================== STEP NAVIGATION ====================

  function showStep(step) {
    currentStep = step;

    // Hide all panels
    document
      .querySelectorAll(".wizard-panel")
      .forEach((p) => (p.style.display = "none"));
    document.getElementById(`wizardStep${step}`).style.display = "block";

    // Update step indicators
    document.querySelectorAll(".wizard-step").forEach((s) => {
      const stepNum = parseInt(s.dataset.step);
      s.classList.toggle("active", stepNum === step);
      s.classList.toggle("completed", stepNum < step);
    });

    // Update buttons
    document.getElementById("btnWizardBack").style.display =
      step > 1 ? "inline-block" : "none";
    document.getElementById("btnWizardNext").style.display =
      step < 2 ? "inline-block" : "none";
    document.getElementById("btnGenerateReport").style.display =
      step === 2 ? "inline-block" : "none";
  }

  function nextStep() {
    if (currentStep < 2) showStep(currentStep + 1);
  }

  function prevStep() {
    if (currentStep > 1) showStep(currentStep - 1);
  }

  function resetWizard() {
    currentStep = 1;
    showStep(1);
    reportData = null;
    const sampleIdInput = document.getElementById("wizardSampleId");
    if (sampleIdInput) sampleIdInput.value = "";
    const leftSel = document.getElementById("signatoryLeft");
    const rightSel = document.getElementById("signatoryRight");
    if (leftSel) leftSel.value = "";
    if (rightSel) rightSel.value = "";
    const leftPrev = document.getElementById("sigLeftPreview");
    const rightPrev = document.getElementById("sigRightPreview");
    if (leftPrev) leftPrev.style.display = "none";
    if (rightPrev) rightPrev.style.display = "none";
    const warn = document.getElementById("signatoryWarning");
    if (warn) warn.style.display = "none";
  }

  // ==================== GENERATE REPORT ====================

  function generateReport() {
    const sampleId = document.getElementById("wizardSampleId").value;
    const leftId = document.getElementById("signatoryLeft").value;
    const rightId = document.getElementById("signatoryRight").value;

    if (!leftId || !rightId) {
      document.getElementById("signatoryWarning").style.display = "block";
      return;
    }
    document.getElementById("signatoryWarning").style.display = "none";

    const layoutType =
      document.querySelector(
        '#reportWizardModal input[name="layoutType"]:checked',
      )?.value || "combined";

    // Build item positions
    const itemPositions = reportData.items.map((item, idx) => ({
      sample_item_id: item.sample_item_id,
      page_number: Math.floor(idx / 5) + 1,
      column_position: (idx % 5) + 1,
    }));

    const btn = document.getElementById("btnGenerateReport");
    btn.disabled = true;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';

    const formData = new FormData();
    formData.append("action", "generate");
    formData.append("sample_id", sampleId);
    formData.append("signatory_left_id", leftId);
    formData.append("signatory_right_id", rightId);
    formData.append("layout_type", layoutType);
    formData.append("item_positions", JSON.stringify(itemPositions));

    fetch(API_URL, { method: "POST", body: formData })
      .then((r) => r.json())
      .then((res) => {
        if (res.status !== "success") throw new Error(res.message);

        showToast("Report generated successfully!", "success");

        // Close modal
        bootstrap.Modal.getInstance(
          document.getElementById("reportWizardModal"),
        )?.hide();

        // Open print template in new tab with ALL report IDs
        // Uses single tab to avoid browser popup blocking
        const ids = res.report_ids || [res.report_id];
        openPrintTemplate(ids.join(","));

        // Refresh table
        loadSamples();
      })
      .catch((err) => {
        console.error("Generate error:", err);
        showToast("Failed: " + err.message, "danger");
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML =
          '<i class="bi bi-file-earmark-check me-1"></i>Generate & Print Report';
      });
  }

  // ==================== PRINT ====================

  function openPrintTemplate(reportIds) {
    window.open(
      `src/Views/report-print-template.php?report_ids=${reportIds}`,
      "_blank",
    );
  }

  function printSaved(reportIdsStr) {
    if (!reportIdsStr || reportIdsStr === "null") {
      showToast("Report not generated yet for this sample.", "warning");
      return;
    }
    openPrintTemplate(reportIdsStr);
  }

  // ==================== UTILITIES ====================

  function escHtml(str) {
    if (!str) return "";
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  function showToast(message, type = "success") {
    const container = document.getElementById("trToastContainer");
    const id = "toast-" + Date.now();
    const bgClass =
      type === "success"
        ? "bg-success"
        : type === "danger"
          ? "bg-danger"
          : "bg-primary";

    container.innerHTML += `
            <div id="${id}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

    const toastEl = document.getElementById(id);
    new bootstrap.Toast(toastEl).show();
    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
  }

  // ==================== PUBLIC API ====================

  return {
    init,
    openWizard,
    printSaved,
  };
})();
