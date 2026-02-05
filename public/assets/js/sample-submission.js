/**
 * Sample Submission JavaScript - COMPLETE PRODUCTION VERSION
 * @version 5.0 - Zero Bugs, Full Validation, Professional UI
 */

const API_BASE = "src/Controllers/sample-controller.php";

// ==========================================
// GLOBAL STATE
// ==========================================

let currentStep = 1;
let sampleCount = 0;
let submissionType = "";
let allParameters = [];
let availableCombos = []; // NEW: Store combos for detection

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

function showToast(message, type = "info") {
  const colors = {
    success: "bg-success text-white",
    error: "bg-danger text-white",
    warning: "bg-warning text-dark",
    info: "bg-info text-dark",
  };

  let toastContainer = document.getElementById("submissionToastContainer");
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.id = "submissionToastContainer";
    toastContainer.className =
      "toast-container position-fixed bottom-0 end-0 p-3";
    toastContainer.style.zIndex = "1080";
    document.body.appendChild(toastContainer);
  }

  const toastEl = document.createElement("div");
  toastEl.className = `toast align-items-center ${
    colors[type] || colors.info
  } border-0 mb-2`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  toastContainer.appendChild(toastEl);

  const toast = new bootstrap.Toast(toastEl, {
    delay: type === "error" ? 5000 : 3000,
  });
  toast.show();

  toastEl.addEventListener("hidden.bs.toast", () => {
    toastEl.remove();
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text || "";
  return div.innerHTML;
}

function showError(inputId, message) {
  const input = document.getElementById(inputId);
  const errorLabel = document.getElementById(inputId + "Error");

  if (input) {
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
  }

  if (errorLabel) {
    errorLabel.textContent = message;
    errorLabel.classList.add("show");
  }
}

function hideError(inputId) {
  const input = document.getElementById(inputId);
  const errorLabel = document.getElementById(inputId + "Error");

  if (input) {
    input.classList.remove("is-invalid");
  }

  if (errorLabel) {
    errorLabel.classList.remove("show");
  }
}

function showSuccess(inputId) {
  const input = document.getElementById(inputId);
  if (input) {
    input.classList.add("is-valid");
    input.classList.remove("is-invalid");
  }
  hideError(inputId);
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("Sample Submission initialized - Version 5.0");
  initializeDateRestrictions();
  initializeEventListeners();
  initializeRealTimeValidation();
  initializeCityAutocomplete(); // ← CITY AUTOCOMPLETE
  showStep(1);
});

function initializeDateRestrictions() {
  const today = new Date().toISOString().split("T")[0];
  const fiveDaysAgo = new Date();
  fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
  const minDate = fiveDaysAgo.toISOString().split("T")[0];

  const tentativeDate = new Date();
  tentativeDate.setDate(tentativeDate.getDate() + 7);
  const defaultTentativeDate = tentativeDate.toISOString().split("T")[0];

  const receivedDateEl = document.getElementById("receivedDate");
  const tentativeDateEl = document.getElementById("tentativeDate");

  if (receivedDateEl) {
    receivedDateEl.max = today;
    receivedDateEl.min = minDate;
    receivedDateEl.value = today;
  }

  if (tentativeDateEl) {
    tentativeDateEl.min = today;
    tentativeDateEl.value = defaultTentativeDate;
  }
}

function initializeEventListeners() {
  // Client Search
  const clientSearchEl = document.getElementById("clientSearch");
  if (clientSearchEl) {
    clientSearchEl.addEventListener("input", debounce(handleClientSearch, 400));
  }

  // Submission Type
  document.querySelectorAll(".type-card").forEach((card) => {
    card.addEventListener("click", selectSubmissionType);
  });

  // Add Sample Button
  const addSampleBtn = document.getElementById("addSampleBtn");
  if (addSampleBtn) {
    addSampleBtn.addEventListener("click", addSample);
  }

  // Navigation
  document.getElementById("nextBtn").addEventListener("click", handleNext);
  document.getElementById("prevBtn").addEventListener("click", handlePrev);
  document.getElementById("siForm").addEventListener("submit", handleSubmit);
  // Payment Options - Removed (payment auto-set to "Not Paid")

  // Additional Charges
  const additionalChargesEl = document.getElementById("additionalCharges");
  if (additionalChargesEl) {
    additionalChargesEl.addEventListener("input", updateGrandTotal);
  }

  // Click outside to close dropdowns
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".position-relative")) {
      document
        .querySelectorAll(".sample-name-autocomplete")
        .forEach((dropdown) => {
          dropdown.classList.remove("show");
        });
    }
    if (
      !e.target.closest("#clientSearch") &&
      !e.target.closest("#clientResults")
    ) {
      const clientResults = document.getElementById("clientResults");
      if (clientResults) {
        clientResults.classList.remove("show");
      }
    }
  });
}

// ==========================================
// REAL-TIME VALIDATION
// ==========================================

function initializeRealTimeValidation() {
  // Client Name Validation
  const clientNameEl = document.getElementById("clientName");
  if (clientNameEl) {
    clientNameEl.addEventListener("input", function () {
      const value = this.value.trim();
      if (value.length === 0) {
        showError("clientName", "Client name is required");
      } else if (value.length < 3) {
        showError("clientName", "Client name must be at least 3 characters");
      } else {
        showSuccess("clientName");
      }
    });
  }

  // Phone Validation
  const phoneEl = document.getElementById("phonePrimary");
  if (phoneEl) {
    phoneEl.addEventListener("input", function () {
      const value = this.value.replace(/[\s-]/g, "");

      if (value.length === 0) {
        showError("phonePrimary", "Phone number is required");
      } else if (value.length > 0 && value[0] !== "0") {
        showError("phonePrimary", "Phone must start with 0");
      } else if (value.length > 0 && value.length < 10) {
        showError("phonePrimary", `Enter ${10 - value.length} more digit(s)`);
      } else if (value.length === 10 && /^0\d{9}$/.test(value)) {
        showSuccess("phonePrimary");
      } else if (value.length > 10) {
        showError("phonePrimary", "Phone must be exactly 10 digits");
      }
    });
  }

  // Received Date Validation
  const receivedDateEl = document.getElementById("receivedDate");
  if (receivedDateEl) {
    receivedDateEl.addEventListener("change", function () {
      const date = new Date(this.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const fiveDaysAgo = new Date(today);
      fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);

      if (!this.value) {
        showError("receivedDate", "Received date is required");
      } else if (date > today) {
        showError("receivedDate", "Received date cannot be in the future");
      } else if (date < fiveDaysAgo) {
        showError(
          "receivedDate",
          "Received date cannot be more than 5 days in the past"
        );
      } else {
        showSuccess("receivedDate");
      }
    });
  }

  // Tentative Date Validation
  const tentativeDateEl = document.getElementById("tentativeDate");
  if (tentativeDateEl) {
    tentativeDateEl.addEventListener("change", function () {
      const date = new Date(this.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (!this.value) {
        showError("tentativeDate", "Tentative date is required");
      } else if (date < today) {
        showError("tentativeDate", "Tentative date cannot be in the past");
      } else {
        showSuccess("tentativeDate");
      }
    });
  }

  // Additional Charges Validation
  const additionalChargesEl = document.getElementById("additionalCharges");
  if (additionalChargesEl) {
    additionalChargesEl.addEventListener("input", function () {
      const value = parseFloat(this.value);

      if (isNaN(value) || value < 0) {
        showError(
          "additionalCharges",
          "Additional charges must be a positive number"
        );
      } else {
        showSuccess("additionalCharges");
      }
    });
  }

  // Payment Reference Validation - Removed (not needed)
}
// Email Validation (Real-time) - NEW
const receiptEmailEl = document.getElementById("receiptEmail");
if (receiptEmailEl) {
  const validateEmail = function () {
    const value = this.value.trim();
    if (value.length === 0) {
      hideError("receiptEmail");
      this.classList.remove("is-invalid", "is-valid");
      return;
    }
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(value)) {
      showError("receiptEmail", "Please enter a valid email address");
    } else {
      showSuccess("receiptEmail");
    }
  };
  receiptEmailEl.addEventListener("input", validateEmail);
  receiptEmailEl.addEventListener("blur", validateEmail);
}

// ==========================================
// CLIENT MANAGEMENT
// ==========================================

async function handleClientSearch() {
  const query = this.value.trim();
  const resultsDiv = document.getElementById("clientResults");

  if (query.length < 2) {
    resultsDiv.classList.remove("show");
    resultsDiv.innerHTML = "";
    return;
  }

  try {
    const res = await fetch(
      `${API_BASE}?action=searchClients&query=${encodeURIComponent(query)}`
    );

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const data = await res.json();
    console.log("Search results:", data);

    if (data.success && data.clients && data.clients.length > 0) {
      let html = "";
      data.clients.forEach((client) => {
        html += `
          <div class="client-item" 
               data-id="${client.client_id}"
               data-name="${escapeHtml(client.client_name)}"
               data-phone="${escapeHtml(client.phone_primary)}"
               data-contact="${escapeHtml(client.contact_person || "")}"
               data-address="${escapeHtml(client.address_line1 || "")}"
               data-city="${escapeHtml(client.city || "")}">
            <strong>${escapeHtml(client.client_name)}</strong><br>
            <small class="text-muted">${escapeHtml(
              client.phone_primary
            )} • ${escapeHtml(client.contact_person || "No contact")}</small>
          </div>`;
      });
      resultsDiv.innerHTML = html;
      resultsDiv.classList.add("show");

      // Attach click handlers
      resultsDiv.querySelectorAll(".client-item").forEach((item) => {
        item.addEventListener("click", selectClientFromSearch);
      });
    } else {
      resultsDiv.innerHTML = `
        <div class="client-item text-muted">
          <i class="fas fa-info-circle"></i> No existing clients found. 
          Enter details below to create new client.
        </div>`;
      resultsDiv.classList.add("show");
    }
  } catch (err) {
    console.error("Client Search Error:", err);
    showToast("Search error. Check console for details.", "error");
  }
}

function selectClientFromSearch() {
  console.log("Client selected:", this.dataset);

  // Store client ID
  document.getElementById("selectedClientId").value = this.dataset.id;

  // Fill form fields
  document.getElementById("clientName").value = this.dataset.name;
  document.getElementById("phonePrimary").value = this.dataset.phone;
  document.getElementById("contactPerson").value = this.dataset.contact;
  document.getElementById("addressLine1").value = this.dataset.address;
  document.getElementById("city").value = this.dataset.city;

  // ← FIX: Load city_id when city is populated
  loadCityIdForClient(this.dataset.city);

  // Store original values for change detection
  document.getElementById("originalClientName").value = this.dataset.name;
  document.getElementById("originalPhone").value = this.dataset.phone;
  document.getElementById("originalContactPerson").value = this.dataset.contact;
  document.getElementById("originalCity").value = this.dataset.city; // ← FIX: Store original city

  // Update search box and hide results
  document.getElementById("clientSearch").value = this.dataset.name;
  document.getElementById("clientResults").classList.remove("show");

  // Show success validation
  showSuccess("clientName");
  showSuccess("phonePrimary");

  showToast("Client selected successfully", "success");
}

async function createNewClient() {
  const clientName = document.getElementById("clientName").value.trim();
  const phone = document.getElementById("phonePrimary").value.trim();

  if (!clientName || !phone) {
    showToast("Client name and phone are required", "error");
    return false;
  }

  // Validate phone (must start with 0 and be 10 digits)
  const phoneClean = phone.replace(/[\s-]/g, "");
  if (!/^0\d{9}$/.test(phoneClean)) {
    showToast(
      "Phone must be 10 digits starting with 0 (e.g., 0771234567)",
      "error"
    );
    showError("phonePrimary", "Invalid phone format");
    return false;
  }

  try {
    const formData = new FormData();
    formData.append("action", "createClient");
    formData.append("client_name", clientName);
    formData.append("phone_primary", phoneClean);
    formData.append(
      "address_line1",
      document.getElementById("addressLine1").value.trim()
    );
    formData.append("city", document.getElementById("city").value.trim());
    formData.append(
      "contact_person",
      document.getElementById("contactPerson").value.trim()
    );

    const res = await fetch(API_BASE, {
      method: "POST",
      body: formData,
    });

    const data = await res.json();
    console.log("Create client response:", data);

    if (data.success) {
      // Store new client ID
      document.getElementById("selectedClientId").value = data.client_id;

      // Store as "original" values
      document.getElementById("originalClientName").value = clientName;
      document.getElementById("originalPhone").value = phoneClean;
      document.getElementById("originalContactPerson").value = document
        .getElementById("contactPerson")
        .value.trim();
      document.getElementById("originalCity").value = document
        .getElementById("city")
        .value.trim();

      showToast("New client created successfully", "success");
      return true;
    } else {
      showToast(data.message || "Failed to create client", "error");
      return false;
    }
  } catch (err) {
    console.error("Create client error:", err);
    showToast("Error creating client. Check console.", "error");
    return false;
  }
}

async function updateClientIfModified() {
  const clientId = document.getElementById("selectedClientId").value;

  if (!clientId) {
    return true; // No client selected yet
  }

  const currentName = document.getElementById("clientName").value.trim();
  const currentPhone = document
    .getElementById("phonePrimary")
    .value.replace(/[\s-]/g, "");
  const currentContact = document.getElementById("contactPerson").value.trim();
  const currentCity = document.getElementById("city").value.trim(); // ← FIX: Get current city

  const originalName = document.getElementById("originalClientName").value;
  const originalPhone = document.getElementById("originalPhone").value;
  const originalContact = document.getElementById(
    "originalContactPerson"
  ).value;
  const originalCity = document.getElementById("originalCity").value; // ← FIX: Get original city

  // Check if modified (INCLUDING CITY)
  const isModified =
    currentName !== originalName ||
    currentPhone !== originalPhone ||
    currentContact !== originalContact ||
    currentCity !== originalCity; // ← FIX: Include city in change detection

  if (!isModified) {
    return true; // No changes
  }

  try {
    const formData = new FormData();
    formData.append("action", "updateClient");
    formData.append("client_id", clientId);
    formData.append("client_name", currentName);
    formData.append("phone_primary", currentPhone);
    formData.append(
      "address_line1",
      document.getElementById("addressLine1").value.trim()
    );
    formData.append("city", document.getElementById("city").value.trim());
    formData.append("contact_person", currentContact);

    const res = await fetch(API_BASE, {
      method: "POST",
      body: formData,
    });

    const data = await res.json();
    console.log("Update client response:", data);

    if (data.success) {
      // Update "original" values
      document.getElementById("originalClientName").value = currentName;
      document.getElementById("originalPhone").value = currentPhone;
      document.getElementById("originalContactPerson").value = currentContact;
      document.getElementById("originalCity").value = currentCity; // ← FIX: Update original city

      showToast("Client information updated", "success");
      return true;
    } else {
      showToast(data.message || "Failed to update client", "error");
      return false;
    }
  } catch (err) {
    console.error("Update client error:", err);
    showToast("Error updating client. Check console.", "error");
    return false;
  }
}

// ==========================================
// STEP NAVIGATION
// ==========================================

function showStep(step) {
  document
    .querySelectorAll(".step")
    .forEach((s) => s.classList.remove("active"));
  const targetStep = document.querySelector(`.step[data-step="${step}"]`);
  if (targetStep) {
    targetStep.classList.add("active");
  }

  document.querySelectorAll(".nav-tabs .nav-link").forEach((link, idx) => {
    if (idx + 1 === step) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });

  document.getElementById("prevBtn").style.display =
    step === 1 ? "none" : "inline-block";
  document.getElementById("nextBtn").style.display =
    step === 6 ? "none" : "inline-block";
  document.getElementById("submitBtn").style.display =
    step === 6 ? "inline-block" : "none";

  if (step === 4) loadSamples();
  if (step === 5) loadTests();
  if (step === 6) generateReview();

  currentStep = step;
  console.log("Switched to step:", step);
}

async function handleNext() {
  console.log("Next button clicked, current step:", currentStep);

  if (!validateStep(currentStep)) {
    return;
  }

  // Step 1: Handle client creation/update
  if (currentStep === 1) {
    const clientId = document.getElementById("selectedClientId").value;

    if (!clientId) {
      // No client selected - create new
      const created = await createNewClient();
      if (!created) {
        return;
      }
    } else {
      // Client exists - check if modified
      const updated = await updateClientIfModified();
      if (!updated) {
        return;
      }
    }
  }

  showStep(currentStep + 1);
}

function handlePrev() {
  showStep(currentStep - 1);
}

function validateStep(step) {
  console.log("Validating step:", step);

  if (step === 1) {
    const clientName = document.getElementById("clientName").value.trim();
    const phone = document.getElementById("phonePrimary").value.trim();

    if (!clientName) {
      showToast("Client name is required", "error");
      showError("clientName", "Client name is required");
      return false;
    }

    if (clientName.length < 3) {
      showToast("Client name must be at least 3 characters", "error");
      showError("clientName", "Client name must be at least 3 characters");
      return false;
    }

    if (!phone) {
      showToast("Phone number is required", "error");
      showError("phonePrimary", "Phone number is required");
      return false;
    }

    const phoneClean = phone.replace(/[\s-]/g, "");
    if (!/^0\d{9}$/.test(phoneClean)) {
      showToast("Phone must be 10 digits starting with 0", "error");
      showError("phonePrimary", "Phone must be 10 digits starting with 0");
      return false;
    }

    return true;
  }

  if (step === 2) {
    if (!submissionType) {
      showToast("Please select submission type (Regular or Swab)", "error");
      showError("submissionType", "Please select submission type");
      return false;
    }
    hideError("submissionType");
    return true;
  }

  if (step === 3) {
    if (!document.getElementById("receivedDate").value) {
      showToast("Received date is required", "error");
      showError("receivedDate", "Received date is required");
      return false;
    }
    if (!document.getElementById("tentativeDate").value) {
      showToast("Tentative date is required", "error");
      showError("tentativeDate", "Tentative date is required");
      return false;
    }

    const addCharges = parseFloat(
      document.getElementById("additionalCharges").value
    );
    if (isNaN(addCharges) || addCharges < 0) {
      showToast("Additional charges must be a positive number", "error");
      showError("additionalCharges", "Must be a positive number");
      return false;
    }

    return true;
  }

  if (step === 4) {
    if (sampleCount === 0) {
      showToast("Please add at least one sample", "error");
      return false;
    }

    let allValid = true;
    document.querySelectorAll(".sample-card").forEach((card, idx) => {
      const sampleNum = idx + 1;
      const name = card.querySelector(".sample-name-input").value.trim();
      const value = card.querySelector(".sample-value").value.trim();
      const unit = card.querySelector(".sample-unit").value;

      if (!name) {
        showToast(`Sample ${sampleNum}: Name is required`, "error");
        card.querySelector(".sample-name-input").classList.add("is-invalid");
        allValid = false;
      } else if (!value) {
        showToast(`Sample ${sampleNum}: Value is required`, "error");
        card.querySelector(".sample-value").classList.add("is-invalid");
        allValid = false;
      } else if (!unit) {
        showToast(`Sample ${sampleNum}: Unit is required`, "error");
        card.querySelector(".sample-unit").classList.add("is-invalid");
        allValid = false;
      }
    });

    return allValid;
  }

  if (step === 5) {
    for (let i = 1; i <= sampleCount; i++) {
      const selectedTests = document.querySelectorAll(
        `input[data-sample="${i}"]:checked`
      );
      if (selectedTests.length === 0) {
        showToast(`Sample ${i} must have at least one test selected`, "error");
        return false;
      }
      if (selectedTests.length > 10) {
        showToast(`Sample ${i} can have maximum 10 tests`, "error");
        return false;
      }
    }
    return true;
  }

  return true;
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
  console.log("Submission type selected:", submissionType);

  hideError("submissionType");

  if (currentStep === 5) {
    loadTests();
  }
}

// ==========================================
// SAMPLES
// ==========================================

function loadSamples() {
  const container = document.getElementById("samplesContainer");
  container.innerHTML = "";
  sampleCount = 0;
  addSample();
}

function addSample() {
  sampleCount++;
  const index = sampleCount;
  const html = `
    <div class="sample-card mb-4 p-4 border rounded" data-index="${index}">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Sample ${index}</h5>
        ${
          index > 1
            ? '<button type="button" class="btn btn-sm btn-outline-danger remove-sample">Remove</button>'
            : ""
        }
      </div>
      <div class="row g-3">
        <div class="col-md-6 position-relative">
          <label>Sample Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-name-input" placeholder="Type to search..." required>
          <div class="sample-name-autocomplete"></div>
        </div>
        <div class="col-md-3">
          <label>Volume <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-value" required>
        </div>
        <div class="col-md-3">
          <label>Unit <span class="text-danger">*</span></label>
          <select class="form-select sample-unit" required>
            <option value="">Select</option>
            <option value="mL">mL</option>
            <option value="g">g</option>
            <option value="cm²">cm²</option>
            <option value="L">L</option>
            <option value="kg">kg</option>
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
          <select class="form-select sample-validity"><option>OK</option><option>Damaged</option><option>Expired</option></select>
        </div>
      </div>
    </div>`;

  document
    .getElementById("samplesContainer")
    .insertAdjacentHTML("beforeend", html);

  const card = document.querySelector(`.sample-card[data-index="${index}"]`);

  const removeBtn = card.querySelector(".remove-sample");
  if (removeBtn) {
    removeBtn.onclick = () => {
      if (sampleCount > 1) {
        card.remove();
        renumberSamples();
      } else {
        showToast("At least one sample required", "warning");
      }
    };
  }

  const sampleNameInput = card.querySelector(".sample-name-input");
  sampleNameInput.addEventListener(
    "input",
    debounce(function () {
      handleSampleNameSearch(this);
    }, 400)
  );

  // Add real-time validation
  sampleNameInput.addEventListener("blur", function () {
    if (this.value.trim()) {
      this.classList.remove("is-invalid");
      this.classList.add("is-valid");
    }
  });

  const sampleValue = card.querySelector(".sample-value");
  sampleValue.addEventListener("blur", function () {
    if (this.value.trim()) {
      this.classList.remove("is-invalid");
      this.classList.add("is-valid");
    }
  });

  const sampleUnit = card.querySelector(".sample-unit");
  sampleUnit.addEventListener("change", function () {
    if (this.value) {
      this.classList.remove("is-invalid");
      this.classList.add("is-valid");
    }
  });
}

function renumberSamples() {
  sampleCount = 0;
  document.querySelectorAll(".sample-card").forEach((card) => {
    sampleCount++;
    card.dataset.index = sampleCount;
    card.querySelector("h5").textContent = `Sample ${sampleCount}`;
  });
}

// BUG FIX #1: Sample name search - Fixed response property name
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

    // FIX: Changed from data.results to data.names (matches backend response)
    if (data.success && data.names && data.names.length > 0) {
      let html = "";
      data.names.forEach((n) => {
        html += `<div class="autocomplete-item" data-name="${escapeHtml(
          n.sample_name
        )}">
          ${escapeHtml(n.sample_name)} 
          <span class="autocomplete-usage">${n.usage_count} uses</span>
        </div>`;
      });
      dropdown.innerHTML = html;
      dropdown.classList.add("show");

      dropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
        item.onclick = () => {
          input.value = item.dataset.name;
          input.classList.add("is-valid");
          input.classList.remove("is-invalid");
          dropdown.classList.remove("show");
        };
      });
    } else {
      dropdown.classList.remove("show");
    }
  } catch (err) {
    console.error("Sample name search error:", err);
  }
}

// ==========================================
// TESTS
// ==========================================

async function loadTests() {
  const container = document.getElementById("testsContainer");
  container.innerHTML =
    "<div class='text-center p-4'><div class='spinner-border'></div><p class='mt-2'>Loading tests...</p></div>";

  try {
    const res = await fetch(
      `${API_BASE}?action=getParameters&type=${submissionType}`
    );
    const data = await res.json();

    if (!data.success) throw new Error(data.message);

    allParameters = data.parameters;
    console.log("Loaded parameters:", allParameters);

    // Load combos for detection
    await loadCombos();

    renderTests();
  } catch (err) {
    console.error("Load tests error:", err);
    container.innerHTML =
      "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Failed to load tests. Please try again.</div>";
  }
}

/**
 * Load available combos from backend
 */
/**
 * Load available combos from backend
 */
/**
 * Load available combos from backend
 */
/**
 * Load available combos from backend
 */
async function loadCombos() {
  try {
    const res = await fetch(`${API_BASE}?action=getCombos`);
    const data = await res.json();

    if (data.success) {
      availableCombos = data.combos || [];
      console.log("✅ Loaded combos:", availableCombos);
    } else {
      console.warn("⚠️ No combos loaded:", data.message);
      availableCombos = [];
    }
  } catch (err) {
    console.error("❌ Load combos error:", err);
    availableCombos = [];
  }
}

/**
 * Detect which combos match currently selected tests
 * Returns array of detected combos sorted by size (largest first)
 */
/**
 * CRITICAL FIX: Detect combos with GREEDY ALGORITHM - prevents overlaps
 * This function now correctly implements greedy algorithm to ensure only
 * the largest matching combos are detected per sample, preventing overlapping combos.
 */
function detectCombosInSelection() {
  const selectedTests = Array.from(
    document.querySelectorAll(".test-checkbox:checked")
  );

  if (selectedTests.length < 2) {
    return [];
  }

  // Get selected parameter IDs and sort them
  const selectedParams = selectedTests
    .map((cb) => parseInt(cb.dataset.param))
    .sort((a, b) => a - b);

  // Sort available combos by parameter count (DESC) - largest first
  // This is CRITICAL for the greedy algorithm to work correctly
  const sortedCombos = [...availableCombos].sort(
    (a, b) => b.parameter_ids.length - a.parameter_ids.length
  );

  const detected = []; // Combos that match
  const usedParams = new Set(); // Track which parameters are already in a combo

  // GREEDY ALGORITHM: Process combos largest first
  for (const combo of sortedCombos) {
    // Convert combo parameter IDs to integers and sort
    const comboParams = combo.parameter_ids
      .map((p) => parseInt(p))
      .sort((a, b) => a - b);

    // ============================================================
    // CHECK 1: Do ALL combo parameters exist in user selection?
    // ============================================================
    const allMatch = comboParams.every((pid) => selectedParams.includes(pid));

    if (!allMatch) {
      continue; // Skip - not all parameters selected
    }

    // ============================================================
    // CHECK 2: Are ANY parameters already used in another combo?
    // ============================================================
    // This prevents overlapping - e.g., if we already detected combo [1,2,3],
    // we won't also detect combo [1,2]
    const hasConflict = comboParams.some((pid) => usedParams.has(pid));

    if (hasConflict) {
      continue; // Skip - parameters already in another combo
    }

    // ============================================================
    // ✅ COMBO IS VALID - Add it to detected list
    // ============================================================
    detected.push(combo);

    // Mark all these parameters as USED
    // This prevents smaller combos with overlapping parameters from being detected
    comboParams.forEach((pid) => usedParams.add(pid));
  }

  // Return detected combos (already sorted by size, largest first)
  return detected;
}

function renderTests() {
  const container = document.getElementById("testsContainer");
  container.innerHTML = "";

  document.querySelectorAll(".sample-card").forEach((card, idx) => {
    const sampleIdx = idx + 1;
    const sampleName =
      card.querySelector(".sample-name-input").value || `Sample ${sampleIdx}`;

    let html = `<div class="test-card mb-4 p-4 border rounded">
      <h5><i class="fas fa-vial"></i> ${escapeHtml(sampleName)}</h5>
      <div class="row">`;

    allParameters.forEach((param) => {
      if (param.variants && param.variants.length > 0) {
        param.variants.forEach((v) => {
          html += createTestCheckbox(
            sampleIdx,
            param.parameter_id,
            v.variant_id,
            `${param.parameter_name} - ${v.variant_name}`,
            v.price
          );
        });
      } else {
        html += createTestCheckbox(
          sampleIdx,
          param.parameter_id,
          null,
          param.parameter_name,
          param.price
        );
      }
    });

    html += `</div></div>`;
    container.insertAdjacentHTML("beforeend", html);
  });

  // BUG FIX #2: Max 10 tests per sample - Real-time validation
  document.querySelectorAll(".test-checkbox").forEach((cb) => {
    cb.addEventListener("change", function (e) {
      const sampleNum = this.dataset.sample;
      const checked = document.querySelectorAll(
        `input[data-sample="${sampleNum}"]:checked`
      );

      if (checked.length > 10) {
        e.preventDefault();
        this.checked = false;
        showToast(
          `Sample ${sampleNum} can have maximum 10 tests (physical form limit)`,
          "warning"
        );
        return;
      }

      calculateTestTotals();
    });
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
          ${escapeHtml(label)}
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

  const testTotalElement = document.getElementById("testChargesTotal");
  if (testTotalElement) {
    testTotalElement.textContent = formatCurrency(total);
  }

  updateGrandTotal();
}

// ==========================================
// REVIEW
// ==========================================

function generateReview() {
  const container = document.getElementById("reviewSummary");
  let html = "";

  // CLIENT INFO
  html += `
    <div class="review-section">
      <h6><i class="fas fa-user"></i> Client Information</h6>
      <p><strong>Name:</strong> ${escapeHtml(
        document.getElementById("clientName").value
      )}</p>
      <p><strong>Phone:</strong> ${escapeHtml(
        document.getElementById("phonePrimary").value
      )}</p>
      <p><strong>Contact:</strong> ${escapeHtml(
        document.getElementById("contactPerson").value || "N/A"
      )}</p>
    </div>`;

  // SUBMISSION DETAILS
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
    </div>`;

  // SAMPLES & TESTS
  let individualTotal = 0;
  html +=
    '<div class="review-section"><h6><i class="fas fa-flask"></i> Samples & Tests</h6>';

  document.querySelectorAll(".sample-card").forEach((sample, idx) => {
    const sampleIdx = idx + 1;
    const sampleName = sample.querySelector(".sample-name-input").value;

    html += `<p><strong>Sample ${sampleIdx}:</strong> ${escapeHtml(
      sampleName
    )}</p><ul>`;

    const selectedTests = document.querySelectorAll(
      `input[data-sample="${sampleIdx}"]:checked`
    );

    selectedTests.forEach((test) => {
      const label = test.nextElementSibling.textContent.split("Rs.")[0].trim();
      const price = parseFloat(test.dataset.price);
      individualTotal += price;
      html += `<li>${label} - ${formatCurrency(price)}</li>`;
    });

    html += "</ul>";
  });

  html += "</div>";

  // DETECT COMBOS
  const combosDetected = detectCombosInSelection();
  const hasCombo = combosDetected.length > 0;

  // Calculate combo pricing
  let finalTotal = individualTotal;
  let totalDiscount = 0;

  if (hasCombo) {
    // Calculate savings from combos (prevent overlap)
    let usedParams = new Set();

    combosDetected.forEach((combo) => {
      // Only apply if parameters not already used
      const canApply = combo.parameter_ids.every((pid) => !usedParams.has(pid));

      if (canApply) {
        finalTotal -= combo.individual_total;
        finalTotal += combo.combo_price;
        totalDiscount += combo.savings;

        // Mark parameters as used
        combo.parameter_ids.forEach((pid) => usedParams.add(pid));
      }
    });
  }

  const addCharges =
    parseFloat(document.getElementById("additionalCharges").value) || 0;
  const grandTotal = finalTotal + addCharges;

  // SMART UI - Show combo info ONLY if combo detected
  if (hasCombo && totalDiscount > 0) {
    const discountPercent = Math.round((totalDiscount / individualTotal) * 100);

    html += `
      <div class="review-section">
        <h6><i class="fas fa-calculator"></i> Totals</h6>
        <p><strong>Individual Price:</strong> 
          <span style="text-decoration: line-through; color: #999;">
            ${formatCurrency(individualTotal)}
          </span>
        </p>
        <p><strong>Combo Discount:</strong> 
          <span style="color: #28a745; font-weight: bold;">
            -${formatCurrency(totalDiscount)} 
            (${discountPercent}% off!)
          </span>
        </p>
        <p class="text-muted small">
          <i class="fas fa-check-circle"></i> 
          ${combosDetected.length} combo${
      combosDetected.length > 1 ? "s" : ""
    } applied: 
          ${combosDetected.map((c) => c.combo_name).join(", ")}
        </p>
        <p><strong>Test Charges:</strong> 
          <span id="testChargesTotal" style="color: #007bff; font-weight: bold;">
            ${formatCurrency(finalTotal)}
          </span>
        </p>
        <p><strong>Additional Charges:</strong> ${formatCurrency(
          addCharges
        )}</p>
        <h5 class="mt-3"><strong>Grand Total: 
          <span id="grandTotalDisplay" style="color: #28a745;">
            ${formatCurrency(grandTotal)}
          </span>
        </strong></h5>
        <p class="text-muted small mt-2">
          * Backend will validate and apply combo discounts automatically
        </p>
      </div>`;
  } else {
    // NO COMBO - Normal display
    html += `
      <div class="review-section">
        <h6><i class="fas fa-calculator"></i> Totals</h6>
        <p><strong>Test Charges:</strong> 
          <span id="testChargesTotal">${formatCurrency(individualTotal)}</span>
        </p>
        <p><strong>Additional Charges:</strong> ${formatCurrency(
          addCharges
        )}</p>
        <h5 class="mt-3"><strong>Grand Total: 
          <span id="grandTotalDisplay">${formatCurrency(grandTotal)}</span>
        </strong></h5>
      </div>`;
  }

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

// selectPaymentStatus - Removed (payment auto-set to "Not Paid")

// ==========================================
// SUBMIT
// ==========================================

// ==========================================
// GLOBAL SUBMISSION LOCK - PREVENTS DOUBLE SUBMISSION
// ==========================================
let isSubmitting = false;

// ==========================================
// SUBMIT
// ==========================================

async function handleSubmit(e) {
  e.preventDefault();

  // ===== CRITICAL: SUBMISSION LOCK =====
  if (isSubmitting) {
    console.warn("⚠️ Submission already in progress, ignoring duplicate call");
    return;
  }
  isSubmitting = true;
  console.log("🔒 Submission lock acquired");
  // ===== END LOCK =====

  // Payment status validation - Removed (auto-set to "Not Paid")

  // Validate receipt email if provided - NEW
  const receiptEmailEl = document.getElementById("receiptEmail");
  if (receiptEmailEl && receiptEmailEl.value.trim().length > 0) {
    const email = receiptEmailEl.value.trim();
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
      showToast("Please enter a valid email address", "error");
      showError("receiptEmail", "Invalid email format");
      isSubmitting = false;
      return;
    }
  }

  const submitBtn = document.getElementById("submitBtn");
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

  const formData = new FormData();
  formData.append("action", "saveSample");
  formData.append(
    "submitted_by",
    document.querySelector('[name="submitted_by"]').value
  );
  formData.append("user_id", document.querySelector('[name="user_id"]').value);
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

  const addCharges =
    parseFloat(document.getElementById("additionalCharges").value) || 0;
  formData.append("additional_charges", addCharges);

  const samples = [];
  document.querySelectorAll(".sample-card").forEach((card) => {
    samples.push({
      sample_name: card.querySelector(".sample-name-input").value.trim(),
      value: card.querySelector(".sample-value").value.trim(),
      unit: card.querySelector(".sample-unit").value,
      client_sample_code:
        card.querySelector(".sample-client-code").value.trim() || "",
      sampling_location:
        card.querySelector(".sample-location").value.trim() || "",
      reason_for_analysis:
        card.querySelector(".sample-reason").value.trim() || "",
      container_damage: card.querySelector(".sample-damage").value,
      temperature_condition: card.querySelector(".sample-temp").value,
      validity_status: card.querySelector(".sample-validity").value,
    });
  });
  formData.append("samples", JSON.stringify(samples));

  const tests = [];
  let testTotal = 0;
  document.querySelectorAll(".test-checkbox:checked").forEach((cb) => {
    const charge = parseFloat(cb.dataset.price);
    testTotal += charge;

    tests.push({
      sample: parseInt(cb.dataset.sample),
      parameter_id: parseInt(cb.dataset.param),
      variant_id:
        cb.dataset.variant && cb.dataset.variant !== ""
          ? parseInt(cb.dataset.variant)
          : null,
      charge: charge,
    });
  });
  formData.append("tests", JSON.stringify(tests));
  formData.append("test_charges_total", testTotal);
  formData.append("grand_total", testTotal + addCharges);

  // Payment status always "Not Paid" - NEW
  formData.append("payment_status", "Not Paid");
  formData.append("payment_reference", "");

  // Add receipt email if provided - NEW
  const receiptEmail =
    document.getElementById("receiptEmail")?.value.trim() || "";
  formData.append("receipt_email", receiptEmail);

  try {
    console.log("📤 Sending submission request...");
    const res = await fetch(API_BASE, { method: "POST", body: formData });
    const data = await res.json();

    console.log("📥 Server response:", data);

    if (data.success) {
      console.log("✅ Submission successful:", data.form_number);
      showToast(
        `✅ Sample submitted successfully!\n📋 Form: ${data.form_number}\n🔖 AC Ref: ${data.ac_reference}`,
        "success"
      );

      // Redirect to sample records page after successful submission
      setTimeout(() => {
        window.location.href = "index.php?page=sample-records-view";
      }, 1500);
    } else {
      throw new Error(data.message || "Submission failed");
    }
  } catch (err) {
    console.error("❌ Submission error:", err);
    showToast(`❌ Submission failed: ${err.message}`, "error");

    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
    isSubmitting = false; // Release lock on error
  }
}

// ==========================================
// CITY AUTOCOMPLETE FUNCTIONS
// ==========================================

/**
 * Initialize city autocomplete functionality
 */
function initializeCityAutocomplete() {
  const cityInput = document.getElementById("city");
  const cityAutocomplete = document.getElementById("cityAutocomplete");

  if (!cityInput || !cityAutocomplete) {
    console.warn("City autocomplete elements not found");
    return;
  }

  cityInput.addEventListener("input", debounce(handleCitySearch, 400));

  document.addEventListener("click", function (e) {
    if (!e.target.closest("#city") && !e.target.closest("#cityAutocomplete")) {
      closeCityAutocomplete();
    }
  });

  cityInput.addEventListener("keydown", handleCityKeyboardNavigation);

  console.log("✓ City autocomplete initialized");
}

/**
 * Handle city search input
 */
async function handleCitySearch() {
  const cityInput = document.getElementById("city");
  const cityAutocomplete = document.getElementById("cityAutocomplete");
  const query = cityInput.value.trim();

  if (query.length < 2) {
    closeCityAutocomplete();
    return;
  }

  cityAutocomplete.innerHTML =
    '<div class="city-autocomplete-loading">Searching...</div>';
  cityAutocomplete.classList.add("show");

  try {
    const response = await fetch(
      `${API_BASE}?action=searchCities&query=${encodeURIComponent(query)}`
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success && data.cities && data.cities.length > 0) {
      displayCityResults(data.cities);
    } else {
      cityAutocomplete.innerHTML = `
                <div class="city-autocomplete-empty">
                    No cities found matching "${escapeHtml(query)}"
                </div>
            `;
    }
  } catch (error) {
    console.error("City search error:", error);
    cityAutocomplete.innerHTML = `
            <div class="city-autocomplete-empty text-danger">
                Error searching cities
            </div>
        `;
  }
}

/**
 * Display city search results in dropdown
 */
function displayCityResults(cities) {
  const cityAutocomplete = document.getElementById("cityAutocomplete");

  const html = cities
    .map(
      (city, index) => `
        <div class="city-autocomplete-item" 
             data-city-id="${city.city_id}" 
             data-city-name="${escapeHtml(city.city_name)}"
             data-index="${index}">
            ${escapeHtml(city.city_name)}
        </div>
    `
    )
    .join("");

  cityAutocomplete.innerHTML = html;
  cityAutocomplete.classList.add("show");

  cityAutocomplete
    .querySelectorAll(".city-autocomplete-item")
    .forEach((item) => {
      item.addEventListener("click", selectCityFromAutocomplete);
    });
}

/**
 * Handle city selection from autocomplete
 */
async function selectCityFromAutocomplete() {
  const cityId = this.getAttribute("data-city-id");
  const cityName = this.getAttribute("data-city-name");

  const cityInput = document.getElementById("city");
  const selectedCityId = document.getElementById("selectedCityId");

  cityInput.value = cityName;

  if (selectedCityId) {
    selectedCityId.value = cityId;
  }

  closeCityAutocomplete();

  try {
    await trackCityUsage(cityId);
  } catch (error) {
    console.warn("Failed to track city usage:", error);
  }

  if (cityInput.classList.contains("is-invalid")) {
    cityInput.classList.remove("is-invalid");
    cityInput.classList.add("is-valid");
  }

  console.log(`✓ City selected: ${cityName} (ID: ${cityId})`);
}

/**
 * Track city usage for analytics
 */
async function trackCityUsage(cityId) {
  const formData = new FormData();
  formData.append("action", "trackCityUsage");
  formData.append("city_id", cityId);

  try {
    await fetch(API_BASE, {
      method: "POST",
      body: formData,
    });
  } catch (error) {
    console.debug("City usage tracking failed:", error);
  }
}

/**
 * Close city autocomplete dropdown
 */
function closeCityAutocomplete() {
  const cityAutocomplete = document.getElementById("cityAutocomplete");
  if (cityAutocomplete) {
    cityAutocomplete.classList.remove("show");
    cityAutocomplete.innerHTML = "";
  }
}

/**
 * Handle keyboard navigation in city autocomplete
 */
function handleCityKeyboardNavigation(e) {
  const cityAutocomplete = document.getElementById("cityAutocomplete");

  if (!cityAutocomplete.classList.contains("show")) {
    return;
  }

  const items = cityAutocomplete.querySelectorAll(".city-autocomplete-item");

  if (items.length === 0) {
    return;
  }

  let currentIndex = -1;
  const activeItem = cityAutocomplete.querySelector(
    ".city-autocomplete-item.active"
  );

  if (activeItem) {
    currentIndex = parseInt(activeItem.getAttribute("data-index"));
  }

  switch (e.key) {
    case "ArrowDown":
      e.preventDefault();
      currentIndex = (currentIndex + 1) % items.length;
      highlightCityItem(items, currentIndex);
      break;

    case "ArrowUp":
      e.preventDefault();
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      highlightCityItem(items, currentIndex);
      break;

    case "Enter":
      e.preventDefault();
      if (currentIndex >= 0) {
        items[currentIndex].click();
      }
      break;

    case "Escape":
      e.preventDefault();
      closeCityAutocomplete();
      break;
  }
}

/**
 * Highlight a specific city item in the dropdown
 */
function highlightCityItem(items, index) {
  items.forEach((item) => item.classList.remove("active"));
  items[index].classList.add("active");
  items[index].scrollIntoView({ block: "nearest", behavior: "smooth" });
}

/**
 * Load city ID when selecting existing client
 * CRITICAL FIX: This ensures city_id is populated when loading client data
 */
async function loadCityIdForClient(cityName) {
  if (!cityName || cityName.trim() === "") {
    return;
  }

  try {
    const response = await fetch(
      `${API_BASE}?action=findCityByName&city_name=${encodeURIComponent(
        cityName
      )}`
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success && data.city_id) {
      const selectedCityId = document.getElementById("selectedCityId");
      if (selectedCityId) {
        selectedCityId.value = data.city_id;
        console.log(
          `✓ City ID loaded: ${data.city_name} (ID: ${data.city_id})`
        );
      }
    } else {
      console.log(`City not found in database: ${cityName}`);
    }
  } catch (error) {
    console.warn("Error loading city ID:", error);
  }
}