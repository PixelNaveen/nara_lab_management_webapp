/**
 * Sample Submission JavaScript - FULLY CONNECTED TO PHP API
 * Works with your sample-controller.php and sample-model.php
 * @version 3.4 (Updated Toast Function)
 */

const API_BASE = "/src/Controllers/sample-controller.php"; // Use absolute path from root

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString("en-GB");
  } catch {
    return dateStr || "N/A";
  }
}

function formatCurrency(amount) {
  return "Rs. " + parseFloat(amount || 0).toFixed(2);
}

/**
 * Shows a Bootstrap 5.3.3 toast notification.
 * @param {string} message - The message to display.
 * @param {string} type - The type of toast (success, error, warning, info).
 */
function showToast(message, type = 'info') {
  // Color classes (converted to your new style)
  const colors = {
    success: 'bg-success text-white',
    error: 'bg-danger text-white',
    warning: 'bg-warning text-dark',
    info: 'bg-info text-dark'
  };

  // Create or get toast container (same as your old function)
  let toastContainer = document.getElementById('notificationToastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'notificationToastContainer';
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '1080';
    document.body.appendChild(toastContainer);
  }

  // Create toast element using your new structure
  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center ${colors[type] || colors.info} border-0 mb-2`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  // Add to container
  toastContainer.appendChild(toastEl);

  // Show toast (error = 5s, others = 3s)
  const toast = new bootstrap.Toast(toastEl, {
    delay: type === 'error' ? 5000 : 3000
  });
  toast.show();

  // Remove after hidden
  toastEl.addEventListener('hidden.bs.toast', () => {
    toastEl.remove();
  });
}

// Debounce for search inputs
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

// ==========================================
// GLOBAL STATE
// ==========================================

let currentStep = 1;
let sampleCount = 0;
let submissionType = "";
let allParameters = []; // Will hold real parameters from server

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  initializeDateRestrictions();
  initializeEventListeners();
  showStep(1);
});

function initializeDateRestrictions() {
  // Fixed DateTime issue - using native JavaScript Date
  const today = new Date().toISOString().split("T")[0];
  const fiveDaysAgo = new Date();
  fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
  const minDate = fiveDaysAgo.toISOString().split("T")[0];

  const tentativeDate = new Date();
  tentativeDate.setDate(tentativeDate.getDate() + 7);
  const defaultTentativeDate = tentativeDate.toISOString().split("T")[0];

  document.getElementById("receivedDate").max = today;
  document.getElementById("receivedDate").min = minDate;
  document.getElementById("tentativeDate").min = today;

  // Set default dates
  document.getElementById("receivedDate").value = today;
  document.getElementById("tentativeDate").value = defaultTentativeDate;
}

function initializeEventListeners() {
  // Client Search - Live from DB
  document
    .getElementById("clientSearch")
    .addEventListener("input", debounce(handleClientSearch, 400));

  // Submission Type
  document
    .querySelectorAll(".type-card")
    .forEach((card) => card.addEventListener("click", selectSubmissionType));

  // Samples
  document.getElementById("addSampleBtn").addEventListener("click", addSample);

  // Navigation
  document.getElementById("nextBtn").addEventListener("click", handleNext);
  document.getElementById("prevBtn").addEventListener("click", handlePrev);
  document.getElementById("siForm").addEventListener("submit", handleSubmit);

  // Payment options
  document.querySelectorAll(".payment-option").forEach((option) => {
    option.addEventListener("click", selectPaymentStatus);
  });

  // Additional charges change
  document
    .getElementById("additionalCharges")
    .addEventListener("input", updateGrandTotal);

  // Click outside to close dropdowns
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".position-relative")) {
      document
        .querySelectorAll(".sample-name-autocomplete")
        .forEach((dropdown) => {
          dropdown.classList.remove("show");
        });
    }
    if (!e.target.closest(".client-search-container")) {
      document.getElementById("clientResults").classList.remove("show");
    }
  });
}

// ==========================================
// STEP NAVIGATION
// ==========================================

function showStep(step) {
  // Update step visibility
  document
    .querySelectorAll(".step")
    .forEach((s) => s.classList.remove("active"));
  const targetStep = document.querySelector(`.step[data-step="${step}"]`);
  if (targetStep) {
    targetStep.classList.add("active");
  }

  // Update tab navigation
  document.querySelectorAll(".nav-tabs .nav-link").forEach((link, idx) => {
    if (idx + 1 === step) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });

  // Update buttons
  document.getElementById("prevBtn").style.display =
    step === 1 ? "none" : "inline-block";
  document.getElementById("nextBtn").style.display =
    step === 6 ? "none" : "inline-block";
  document.getElementById("submitBtn").style.display =
    step === 6 ? "inline-block" : "none";

  // Load step-specific content
  if (step === 4) loadSamples();
  if (step === 5) loadTests();
  if (step === 6) generateReview();

  currentStep = step;
}

function handleNext() {
  if (!validateStep(currentStep)) return;
  showStep(currentStep + 1);
}

function handlePrev() {
  showStep(currentStep - 1);
}

// ==========================================
// CLIENT SEARCH - LIVE FROM DATABASE
// ==========================================
async function handleClientSearch() {
  const query = this.value.trim();
  const resultsDiv = document.getElementById("clientResults");

  if (query.length < 2) {
    resultsDiv.classList.remove("show");
    return;
  }

  try {
    const res = await fetch(
      `${API_BASE}?action=searchClients&query=${encodeURIComponent(query)}`
    );
    if (!res.ok) {
      throw new Error(`Server responded with status: ${res.status}`);
    }
    const data = await res.json();

    if (data.success && data.clients.length > 0) {
      let html = "";
      data.clients.forEach((c) => {
        html += `
          <div class="client-item" 
               data-id="${c.client_id}"
               data-name="${c.client_name}"
               data-phone="${c.phone_primary}"
               data-contact="${c.contact_person || ""}"
               data-address="${c.address_line1 || ""}"
               data-city="${c.city || ""}">
            <strong>${c.client_name}</strong><br>
            <small>${c.phone_primary} - ${
          c.contact_person || "No contact"
        }</small>
          </div>`;
      });
      resultsDiv.innerHTML = html;
      resultsDiv.classList.add("show");

      // Add event listener to each new item
      resultsDiv.querySelectorAll(".client-item").forEach((item) => {
        item.addEventListener("click", selectClientFromSearch);
      });
    } else {
      resultsDiv.innerHTML = `<div class="client-item text-muted">No clients found</div>`;
      resultsDiv.classList.add("show");
    }
  } catch (err) {
    console.error("Client Search Error:", err);
    showToast(
      "Search error. Check connection and browser console for details.",
      "error"
    );
  }
}

function selectClientFromSearch() {
  const item = this;
  document.getElementById("selectedClientId").value = item.dataset.id;
  document.getElementById("clientName").value = item.dataset.name;
  document.getElementById("phonePrimary").value = item.dataset.phone;
  document.getElementById("contactPerson").value = item.dataset.contact;
  document.getElementById("addressLine1").value = item.dataset.address;
  document.getElementById("city").value = item.dataset.city;

  document.getElementById("clientSearch").value = item.dataset.name;
  document.getElementById("clientResults").classList.remove("show");
}

// ==========================================
// SUBMISSION TYPE
// ==========================================
function selectSubmissionType() {
  document
    .querySelectorAll(".type-card")
    .forEach((c) => c.classList.remove("selected"));
  this.classList.add("selected");
  submissionType = this.dataset.type;

  // Reload tests when type changes (swab vs regular)
  if (currentStep === 5) loadTests();
}

// ==========================================
// SAMPLES STEP
// ==========================================
function loadSamples() {
  const container = document.getElementById("samplesContainer");
  container.innerHTML = "";
  sampleCount = 0;
  addSample(); // Add first sample by default
}

function addSample() {
  sampleCount++;
  const index = sampleCount;
  const html = `
    <div class="sample-card mb-4 p-4 border rounded" data-index="${index}">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Sample ${index}</h5>
        <button type="button" class="btn btn-sm btn-outline-danger remove-sample">Remove</button>
      </div>
      <div class="row g-3">
        <div class="col-md-6 position-relative">
          <label>Sample Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-name-input" placeholder="Type to search..." required>
          <div class="sample-name-autocomplete autocomplete-dropdown"></div>
        </div>
        <div class="col-md-3">
          <label>Value <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-value" required>
        </div>
        <div class="col-md-3">
          <label>Unit <span class="text-danger">*</span></label>
          <select class="form-select sample-unit" required>
            <option value="">Select</option>
            <option value="ml">ml</option>
            <option value="g">g</option>
            <option value="cm²">cm²</option>
            <option value="pcs">pcs</option>
          </select>
        </div>
        <div class="col-md-6">
          <label>Client Sample Code</label>
          <input type="text" class="form-control sample-client-code">
        </div>
        <div class="col-md-6">
          <label>Sampling Location</label>
          <input type="text" class="form-control sample-location">
        </div>
        <div class="col-12">
          <label>Reason for Analysis</label>
          <textarea class="form-control sample-reason" rows="2"></textarea>
        </div>
        <!-- Other fields (damage, temp, validity) -->
        <div class="col-md-4">
          <label>Container Damage</label>
          <select class="form-select sample-damage"><option>No</option><option>Yes</option></select>
        </div>
        <div class="col-md-4">
          <label>Temperature</label>
          <select class="form-select sample-temp">
            <option>Ambient</option><option>Chilled</option><option>Frozen</option>
          </select>
        </div>
        <div class="col-md-4">
          <label>Validity</label>
          <select class="form-select sample-validity"><option>OK</option><option>Not OK</option></select>
        </div>
      </div>
    </div>`;

  document
    .getElementById("samplesContainer")
    .insertAdjacentHTML("beforeend", html);

  const card = document.querySelector(`.sample-card[data-index="${index}"]`);
  card.querySelector(".remove-sample").onclick = () => {
    if (sampleCount > 1) {
      card.remove();
      sampleCount--;
      renumberSamples();
    } else {
      showToast("At least one sample required", "warning");
    }
  };

  // Add event listener for sample name autocomplete
  const sampleNameInput = card.querySelector(".sample-name-input");
  sampleNameInput.addEventListener(
    "input",
    debounce(function () {
      handleSampleNameSearch(this);
    }, 400)
  );
}

function renumberSamples() {
  document.querySelectorAll(".sample-card").forEach((card, i) => {
    card.dataset.index = i + 1;
    card.querySelector("h5").textContent = `Sample ${i + 1}`;
  });
}

// Sample name autocomplete - Live from DB
async function handleSampleNameSearch(input) {
  const query = input.value.trim();
  const dropdown = input
    .closest(".position-relative")
    .querySelector(".sample-name-autocomplete");

  if (query.length < 2) {
    dropdown.classList.remove("show");
    return;
  }

  try {
    const res = await fetch(
      `${API_BASE}?action=searchSampleNames&query=${encodeURIComponent(query)}`
    );
    const data = await res.json();

    if (data.success && data.names.length > 0) {
      let html = "";
      data.names.forEach((n) => {
        html += `<div class="autocomplete-item" data-name="${n.sample_name}">${n.sample_name} <small>(${n.usage_count} uses)</small></div>`;
      });
      dropdown.innerHTML = html;
      dropdown.classList.add("show");

      dropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
        item.onclick = () => {
          input.value = item.dataset.name;
          dropdown.classList.remove("show");
        };
      });
    }
  } catch (err) {}
}

// ==========================================
// TESTS STEP - Load from DB
// ==========================================
async function loadTests() {
  const container = document.getElementById("testsContainer");
  container.innerHTML =
    "<div class='text-center p-4'><div class='spinner-border'></div> Loading tests...</div>";

  try {
    const res = await fetch(
      `${API_BASE}?action=getParameters&type=${submissionType}`
    );
    const data = await res.json();

    if (!data.success) throw new Error(data.message);

    allParameters = data.parameters;
    renderTests();
  } catch (err) {
    container.innerHTML = "<div class='text-danger'>Failed to load tests</div>";
  }
}

function renderTests() {
  const container = document.getElementById("testsContainer");
  container.innerHTML = "";

  document.querySelectorAll(".sample-card").forEach((card, idx) => {
    const sampleIdx = idx + 1;
    const sampleName =
      card.querySelector(".sample-name-input").value || `Sample ${sampleIdx}`;

    let html = `<div class="test-card mb-4 p-4 border rounded">
      <h5>${sampleName}</h5>
      <div class="row">`;

    allParameters.forEach((param) => {
      if (param.variants && param.variants.length > 0) {
        param.variants.forEach((v) => {
          html += createTestCheckbox(
            sampleIdx,
            param.id,
            v.id,
            `${param.name} - ${v.name}`,
            v.price
          );
        });
      } else {
        html += createTestCheckbox(
          sampleIdx,
          param.id,
          null,
          param.name,
          param.price
        );
      }
    });

    html += `</div></div>`;
    container.insertAdjacentHTML("beforeend", html);
  });

  // Live total calculation
  document.querySelectorAll(".test-checkbox").forEach((cb) => {
    cb.addEventListener("change", calculateTestTotals);
  });
}

function createTestCheckbox(sampleIdx, paramId, variantId, label, price) {
  const id = variantId
    ? `test_${sampleIdx}_v${variantId}`
    : `test_${sampleIdx}_p${paramId}`;
  return `
    <div class="col-md-6 mb-2">
      <div class="form-check">
        <input class="form-check-input test-checkbox" type="checkbox"
               id="${id}"
               data-sample="${sampleIdx}"
               data-param="${paramId}"
               data-variant="${variantId || ""}"
               data-price="${price}">
        <label class="form-check-label" for="${id}">
          ${label}
          <strong class="float-end">${formatCurrency(price)}</strong>
        </label>
      </div>
    </div>`;
}

function calculateTestTotals() {
  let total = 0;
  document.querySelectorAll(".test-checkbox:checked").forEach((cb) => {
    total += parseFloat(cb.dataset.price);
  });

  // Create or update the test charges total display if it exists
  let testTotalElement = document.getElementById("testChargesTotal");
  if (testTotalElement) {
    testTotalElement.textContent = formatCurrency(total);
  }

  updateGrandTotal();
}

// ==========================================
// REVIEW & SUBMIT
// ==========================================
function generateReview() {
  const container = document.getElementById("reviewSummary");
  let html = "";

  // Client Information
  html += `
    <div class="review-section">
      <h6><i class="fas fa-user"></i> Client Information</h6>
      <p><strong>Name:</strong> ${
        document.getElementById("clientName").value
      }</p>
      <p><strong>Phone:</strong> ${
        document.getElementById("phonePrimary").value
      }</p>
      <p><strong>Contact Person:</strong> ${
        document.getElementById("contactPerson").value || "N/A"
      }</p>
    </div>
  `;

  // Submission Details
  html += `
    <div class="review-section">
      <h6><i class="fas fa-calendar-alt"></i> Submission Details</h6>
      <p><strong>Type:</strong> ${
        submissionType.charAt(0).toUpperCase() + submissionType.slice(1)
      }</p>
      <p><strong>Received:</strong> ${formatDate(
        document.getElementById("receivedDate").value
      )}</p>
      <p><strong>Tentative:</strong> ${formatDate(
        document.getElementById("tentativeDate").value
      )}</p>
      <p><strong>Notes:</strong> ${
        document.getElementById("additionalNotes").value || "None"
      }</p>
    </div>
  `;

  // Samples and Tests
  let testTotal = 0;
  html +=
    '<div class="review-section"><h6><i class="fas fa-flask"></i> Samples & Tests</h6>';

  document.querySelectorAll(".sample-card").forEach((sample, idx) => {
    const sampleIndex = idx + 1;
    const sampleName = sample.querySelector(".sample-name-input").value;

    html += `<p><strong>Sample ${sampleIndex}:</strong> ${sampleName}</p><ul>`;

    const selectedTests = document.querySelectorAll(
      `input[data-sample="${sampleIndex}"]:checked`
    );
    selectedTests.forEach((test) => {
      const label = test.nextElementSibling.textContent.split("Rs.")[0].trim();
      const price = parseFloat(test.dataset.price);
      testTotal += price;
      html += `<li>${label} - ${formatCurrency(price)}</li>`;
    });

    html += "</ul>";
  });

  html += "</div>";

  // Totals
  const addCharges = Math.max(
    0,
    parseFloat(document.getElementById("additionalCharges").value) || 0
  );
  const grandTotal = testTotal + addCharges;

  html += `
    <div class="review-section">
      <h6><i class="fas fa-calculator"></i> Totals</h6>
      <p><strong>Test Charges:</strong> <span id="testChargesTotal">${formatCurrency(
        testTotal
      )}</span></p>
      <p><strong>Additional Charges:</strong> ${formatCurrency(addCharges)}</p>
      <h5 class="mt-3"><strong>Grand Total: <span id="grandTotalDisplay">${formatCurrency(
        grandTotal
      )}</span></strong></h5>
    </div>
  `;

  container.innerHTML = html;
}

function updateGrandTotal() {
  const testTotal = parseFloat(
    document
      .getElementById("testChargesTotal")
      ?.textContent.replace(/[^0-9.]/g, "") || 0
  );
  const addCharges = parseFloat(
    document.getElementById("additionalCharges").value || 0
  );
  const grand = testTotal + addCharges;

  const grandTotalElement = document.getElementById("grandTotalDisplay");
  if (grandTotalElement) {
    grandTotalElement.textContent = formatCurrency(grand);
  }
}

function selectPaymentStatus() {
  document
    .querySelectorAll(".payment-option")
    .forEach((o) => o.classList.remove("selected"));
  this.classList.add("selected");
  const status = this.dataset.status;

  const paymentRefSection = document.getElementById("paymentReferenceSection");
  if (paymentRefSection) {
    paymentRefSection.classList.toggle("d-none", status !== "paid");
  }
}

async function handleSubmit(e) {
  e.preventDefault();

  if (!validateForm()) return;

  const formData = new FormData();
  formData.append("action", "saveSample");
  formData.append(
    "client_id",
    document.getElementById("selectedClientId").value
  );
  formData.append("submission_type", submissionType);
  formData.append(
    "received_date",
    document.getElementById("receivedDate").value
  );
  formData.append(
    "tentative_date",
    document.getElementById("tentativeDate").value
  );
  formData.append(
    "additional_notes",
    document.getElementById("additionalNotes").value || ""
  );
  formData.append(
    "additional_charges",
    document.getElementById("additionalCharges").value || 0
  );

  // Samples array
  const samples = [];
  document.querySelectorAll(".sample-card").forEach((card) => {
    samples.push({
      sample_name: card.querySelector(".sample-name-input").value,
      value: card.querySelector(".sample-value").value,
      unit: card.querySelector(".sample-unit").value,
      client_sample_code: card.querySelector(".sample-client-code").value || "",
      sampling_location: card.querySelector(".sample-location").value || "",
      reason_for_analysis: card.querySelector(".sample-reason").value || "",
      container_damage: card.querySelector(".sample-damage").value,
      temperature_condition: card.querySelector(".sample-temp").value,
      validity_status: card.querySelector(".sample-validity").value,
    });
  });
  formData.append("samples", JSON.stringify(samples));

  // Tests array
  const tests = [];
  document.querySelectorAll(".test-checkbox:checked").forEach((cb) => {
    tests.push({
      sample: cb.dataset.sample,
      parameter_id: cb.dataset.param,
      variant_id: cb.dataset.variant || null,
      charge: cb.dataset.price,
    });
  });
  formData.append("tests", JSON.stringify(tests));

  // Payment
  const selectedPayment = document.querySelector(".payment-option.selected");
  if (selectedPayment) {
    const isPaid = selectedPayment.dataset.status === "paid";
    if (isPaid) {
      formData.append("payment_status", "paid");
      formData.append(
        "payment_reference",
        document.getElementById("paymentReference").value.trim()
      );
    } else {
      formData.append("payment_status", "not_paid");
      formData.append("payment_reference", "");
    }
  }

  try {
    const res = await fetch(API_BASE, { method: "POST", body: formData });
    const data = await res.json();

    if (data.success) {
      showToast(`Sample submitted! Form No: ${data.form_number}`, "success");
      setTimeout(() => location.reload(), 2000);
    } else {
      showToast(data.message || "Submission failed", "error");
    }
  } catch (err) {
    showToast("Network error", "error");
  }
}

function validateForm() {
  if (!document.getElementById("selectedClientId").value) {
    showToast("Please select or create a client", "error");
    showStep(1);
    return false;
  }
  if (!submissionType) {
    showToast("Please select submission type", "error");
    showStep(2);
    return false;
  }

  // Validate each sample has at least one test selected
  const sampleCards = document.querySelectorAll(".sample-card");
  for (let i = 0; i < sampleCards.length; i++) {
    const sampleIdx = i + 1;
    const selectedTests = document.querySelectorAll(
      `input[data-sample="${sampleIdx}"]:checked`
    );
    if (selectedTests.length === 0) {
      showToast(
        `Sample ${sampleIdx} must have at least one test selected`,
        "error"
      );
      showStep(5);
      return false;
    }
  }

  // Validate payment status
  const selectedPayment = document.querySelector(".payment-option.selected");
  if (!selectedPayment) {
    showToast("Please select payment status", "error");
    showStep(6);
    return false;
  }

  // Validate payment reference if paid
  if (selectedPayment.dataset.status === "paid") {
    const paymentRef = document.getElementById("paymentReference").value.trim();
    if (!paymentRef) {
      showToast("Payment reference is required for paid status", "error");
      showStep(6);
      return false;
    }
  }

  return true;
}

function validateStep(step) {
  // Basic validation for each step
  if (step === 1) {
    if (!document.getElementById("selectedClientId").value) {
      showToast("Please select or create a client", "error");
      return false;
    }
  } else if (step === 2) {
    if (!submissionType) {
      showToast("Please select submission type", "error");
      return false;
    }
  } else if (step === 3) {
    if (
      !document.getElementById("receivedDate").value ||
      !document.getElementById("tentativeDate").value
    ) {
      showToast("Please provide both received and tentative dates", "error");
      return false;
    }
  } else if (step === 4) {
    if (sampleCount === 0) {
      showToast("Please add at least one sample", "error");
      return false;
    }

    // Validate each sample has required fields
    let valid = true;
    document.querySelectorAll(".sample-card").forEach((card) => {
      if (
        !card.querySelector(".sample-name-input").value.trim() ||
        !card.querySelector(".sample-value").value.trim() ||
        !card.querySelector(".sample-unit").value
      ) {
        valid = false;
      }
    });

    if (!valid) {
      showToast("All samples must have a name, value, and unit", "error");
      return false;
    }
  } else if (step === 5) {
    // Validate each sample has at least one test selected
    const sampleCards = document.querySelectorAll(".sample-card");
    for (let i = 0; i < sampleCards.length; i++) {
      const sampleIdx = i + 1;
      const selectedTests = document.querySelectorAll(
        `input[data-sample="${sampleIdx}"]:checked`
      );
      if (selectedTests.length === 0) {
        showToast(
          `Sample ${sampleIdx} must have at least one test selected`,
          "error"
        );
        return false;
      }
    }
  }

  return true;
}
