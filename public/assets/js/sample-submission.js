/**
 * Sample Submission JavaScript - COMPLETE VERSION 3.0
 * @version 3.0 - 100% Production Ready with Server Time
 * @date February 5, 2026
 * @features Server time sync, 30-day backdate, auto tentative, time validation
 */

const API_BASE = "src/Controllers/sample-controller.php";

// ==========================================
// GLOBAL STATE
// ==========================================

let currentStep = 1;
let sampleCount = 0;
let submissionType = "";
let allParameters = [];
let availableCombos = [];
let serverDateTime = null;
let serverTimeInterval = null;
let isSubmitting = false;
let allExtraItems = [];
let sampleCategories = [
  { id: 1, name: "Water and Ice", code: "WAT" },
  { id: 2, name: "Fish and Shellfish", code: "FSH" },
  { id: 3, name: "Surface Swab", code: "SWB" },
  { id: 4, name: "Other", code: "OTH" },
];

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
  toastEl.className = `toast align-items-center ${colors[type] || colors.info} border-0 mb-2`;
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
  console.log(
    "✅ Sample Submission initialized - Version 3.0 (Server Time Edition)",
  );
  initializeEventListeners();
  initializeRealTimeValidation();
  initializeCityAutocomplete();

  // Fetch server time immediately
  fetchServerTime().then(() => {
    console.log("✅ Server time fetched successfully");
  });

  showStep(1);
});

// ============================================================================
// SERVER TIME & DATE/TIME MANAGEMENT
// ============================================================================

/**
 * Fetch server date and time from backend
 */
async function fetchServerTime() {
  try {
    console.log("📡 Fetching server time...");
    const response = await fetch(`${API_BASE}?action=getServerTime`);
    const data = await response.json();

    if (data.success) {
      serverDateTime = data;
      console.log("✅ Server time loaded:", data.formatted);

      // Update display immediately
      updateServerTimeDisplay();

      // Start clock
      startServerTimeClock();

      return true;
    } else {
      throw new Error(data.message || "Failed to get server time");
    }
  } catch (error) {
    console.error("❌ Server time fetch error:", error);
    // showToast("Warning: Could not sync with server time. Using browser time.", "warning");
    useBrowserTime();
    return false;
  }
}

/**
 * Fallback to browser time if server unavailable
 */
function useBrowserTime() {
  console.warn("⚠️ Using browser time as fallback");
  const now = new Date();
  serverDateTime = {
    success: true,
    date: now.toISOString().split("T")[0],
    time: now.toTimeString().split(" ")[0],
    time_short: now.toTimeString().split(" ")[0].substring(0, 5),
    formatted: now.toLocaleString("en-GB", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: true,
    }),
  };

  updateServerTimeDisplay();
  startServerTimeClock();
}

/**
 * Update server time display
 */
function updateServerTimeDisplay() {
  const display = document.getElementById("currentServerTime");
  if (display && serverDateTime) {
    display.textContent = serverDateTime.formatted;
  }
}

/**
 * Start server time clock (updates every second)
 */
function startServerTimeClock() {
  // Clear any existing interval
  if (serverTimeInterval) {
    clearInterval(serverTimeInterval);
  }

  // Update function
  function updateClock() {
    const now = new Date();
    const formatted = now.toLocaleString("en-GB", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      // second: "2-digit",
      hour12: true,
    });

    const display = document.getElementById("currentServerTime");
    if (display) {
      display.textContent = formatted;
    }
  }

  // Update immediately and then every second
  updateClock();
  serverTimeInterval = setInterval(updateClock, 1000);

  console.log("⏰ Server time clock started");
}

/**
 * Initialize date and time fields with server values
 */
function initializeDateTimeFields() {
  const receivedDateEl = document.getElementById("receivedDate");
  const receivedTimeEl = document.getElementById("receivedTime");
  const tentativeDateEl = document.getElementById("tentativeDate");

  // Check if elements exist (might not be in DOM yet)
  if (!receivedDateEl || !receivedTimeEl || !tentativeDateEl) {
    console.warn(
      "⏳ Date/time fields not in DOM yet, will initialize when Step 3 is shown",
    );
    return false;
  }

  // Check if server time is available
  if (!serverDateTime) {
    console.warn("⏳ Server time not yet loaded");
    return false;
  }

  // Set received date to server date
  receivedDateEl.value = serverDateTime.date;

  // Set received time to server time
  receivedTimeEl.value = serverDateTime.time_short;

  // Set date range: 30 days back to today
  const today = new Date(serverDateTime.date);
  const minDate = new Date(today);
  minDate.setDate(minDate.getDate() - 30);

  receivedDateEl.min = minDate.toISOString().split("T")[0];
  receivedDateEl.max = serverDateTime.date;

  // Calculate and set tentative date
  updateTentativeDate();

  console.log("✅ Date/time fields initialized:");
  console.log("   Received Date:", receivedDateEl.value);
  console.log("   Received Time:", receivedTimeEl.value);
  console.log("   Tentative Date:", tentativeDateEl.value);
  console.log("   Date Range:", receivedDateEl.min, "to", receivedDateEl.max);

  return true;
}

/**
 * Update tentative date based on received date + 10 days
 */
function updateTentativeDate() {
  const receivedDateEl = document.getElementById("receivedDate");
  const tentativeDateEl = document.getElementById("tentativeDate");

  if (!receivedDateEl || !tentativeDateEl) return;

  const receivedDate = receivedDateEl.value;
  if (!receivedDate) return;

  // Calculate tentative date = received date + 10 days
  const received = new Date(receivedDate);
  const tentative = new Date(received);
  tentative.setDate(tentative.getDate() + 10);

  const tentativeDate = tentative.toISOString().split("T")[0];
  tentativeDateEl.value = tentativeDate;

  // Set tentative date minimum to received date
  tentativeDateEl.min = receivedDate;

  console.log(
    "📅 Tentative date updated:",
    tentativeDate,
    "(+10 days from",
    receivedDate + ")",
  );
}

/**
 * Initialize date and time field event listeners
 */
function initializeDateTimeListeners() {
  const receivedDateEl = document.getElementById("receivedDate");
  const receivedTimeEl = document.getElementById("receivedTime");
  const tentativeDateEl = document.getElementById("tentativeDate");

  if (receivedDateEl) {
    receivedDateEl.addEventListener("change", function () {
      validateReceivedDate();
      updateTentativeDate();
    });
  }

  if (receivedTimeEl) {
    receivedTimeEl.addEventListener("change", validateReceivedTime);
    receivedTimeEl.addEventListener("blur", validateReceivedTime);
  }

  if (tentativeDateEl) {
    tentativeDateEl.addEventListener("change", validateTentativeDate);
  }

  console.log("✅ Date/time event listeners initialized");
}

/**
 * Validate received date
 */
function validateReceivedDate() {
  const receivedDateEl = document.getElementById("receivedDate");
  if (!receivedDateEl) return false;

  const value = receivedDateEl.value;

  if (!value) {
    showError("receivedDate", "Received date is required");
    return false;
  }

  if (!serverDateTime) {
    console.warn("⏳ Server time not loaded, skipping date validation");
    return true; // Allow if server time not loaded yet
  }

  const selectedDate = new Date(value);
  const today = new Date(serverDateTime.date);
  const minDate = new Date(today);
  minDate.setDate(minDate.getDate() - 30);

  if (selectedDate > today) {
    showError("receivedDate", "Received date cannot be in the future");
    return false;
  }

  if (selectedDate < minDate) {
    showError(
      "receivedDate",
      "Received date cannot be more than 30 days in the past",
    );
    return false;
  }

  showSuccess("receivedDate");
  return true;
}

/**
 * Validate received time
 */
function validateReceivedTime() {
  const receivedTimeEl = document.getElementById("receivedTime");
  if (!receivedTimeEl) return false;

  const value = receivedTimeEl.value;

  if (!value) {
    showError("receivedTime", "Received time is required");
    return false;
  }

  // Check format HH:MM
  const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
  if (!timeRegex.test(value)) {
    showError("receivedTime", "Invalid time format (use HH:MM)");
    return false;
  }

  showSuccess("receivedTime");
  return true;
}

/**
 * Validate tentative date
 */
function validateTentativeDate() {
  const receivedDateEl = document.getElementById("receivedDate");
  const tentativeDateEl = document.getElementById("tentativeDate");

  if (!receivedDateEl || !tentativeDateEl) return false;

  const receivedDate = receivedDateEl.value;
  const tentativeDate = tentativeDateEl.value;

  if (!tentativeDate) {
    showError("tentativeDate", "Tentative date is required");
    return false;
  }

  if (!receivedDate) {
    showError("tentativeDate", "Please select received date first");
    return false;
  }

  const received = new Date(receivedDate);
  const tentative = new Date(tentativeDate);

  if (tentative <= received) {
    showError("tentativeDate", "Tentative date must be after received date");
    return false;
  }

  showSuccess("tentativeDate");
  return true;
}

// ==========================================
// EVENT LISTENERS
// ==========================================

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

  // Extra items total drives additional charges — no manual listener needed

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
  // Client Name
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

  // Phone
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

  // Additional charges are now auto-calculated from extra items — no manual validation needed

  // Email Validation
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
      `${API_BASE}?action=searchClients&query=${encodeURIComponent(query)}`,
    );

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const data = await res.json();

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
            <small class="text-muted">${escapeHtml(client.phone_primary)} • ${escapeHtml(
              client.contact_person || "No contact",
            )}</small>
          </div>`;
      });
      resultsDiv.innerHTML = html;
      resultsDiv.classList.add("show");

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
  document.getElementById("selectedClientId").value = this.dataset.id;
  document.getElementById("clientName").value = this.dataset.name;
  document.getElementById("phonePrimary").value = this.dataset.phone;
  document.getElementById("contactPerson").value = this.dataset.contact;
  document.getElementById("addressLine1").value = this.dataset.address;
  document.getElementById("city").value = this.dataset.city;

  loadCityIdForClient(this.dataset.city);

  document.getElementById("originalClientName").value = this.dataset.name;
  document.getElementById("originalPhone").value = this.dataset.phone;
  document.getElementById("originalContactPerson").value = this.dataset.contact;
  document.getElementById("originalCity").value = this.dataset.city;

  document.getElementById("clientSearch").value = this.dataset.name;
  document.getElementById("clientResults").classList.remove("show");

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

  const phoneClean = phone.replace(/[\s-]/g, "");
  if (!/^0\d{9}$/.test(phoneClean)) {
    showToast(
      "Phone must be 10 digits starting with 0 (e.g., 0771234567)",
      "error",
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
      document.getElementById("addressLine1").value.trim(),
    );
    formData.append("city", document.getElementById("city").value.trim());
    formData.append(
      "contact_person",
      document.getElementById("contactPerson").value.trim(),
    );

    const res = await fetch(API_BASE, {
      method: "POST",
      body: formData,
    });

    const data = await res.json();

    if (data.success) {
      document.getElementById("selectedClientId").value = data.client_id;
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
    return true;
  }

  const currentName = document.getElementById("clientName").value.trim();
  const currentPhone = document
    .getElementById("phonePrimary")
    .value.replace(/[\s-]/g, "");
  const currentContact = document.getElementById("contactPerson").value.trim();
  const currentCity = document.getElementById("city").value.trim();

  const originalName = document.getElementById("originalClientName").value;
  const originalPhone = document.getElementById("originalPhone").value;
  const originalContact = document.getElementById(
    "originalContactPerson",
  ).value;
  const originalCity = document.getElementById("originalCity").value;

  const isModified =
    currentName !== originalName ||
    currentPhone !== originalPhone ||
    currentContact !== originalContact ||
    currentCity !== originalCity;

  if (!isModified) {
    return true;
  }

  try {
    const formData = new FormData();
    formData.append("action", "updateClient");
    formData.append("client_id", clientId);
    formData.append("client_name", currentName);
    formData.append("phone_primary", currentPhone);
    formData.append(
      "address_line1",
      document.getElementById("addressLine1").value.trim(),
    );
    formData.append("city", document.getElementById("city").value.trim());
    formData.append("contact_person", currentContact);

    const res = await fetch(API_BASE, {
      method: "POST",
      body: formData,
    });

    const data = await res.json();

    if (data.success) {
      document.getElementById("originalClientName").value = currentName;
      document.getElementById("originalPhone").value = currentPhone;
      document.getElementById("originalContactPerson").value = currentContact;
      document.getElementById("originalCity").value = currentCity;

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

  // INITIALIZE DATE/TIME FIELDS WHEN STEP 3 IS SHOWN
  if (step === 3) {
    const initialized = initializeDateTimeFields();
    if (initialized) {
      initializeDateTimeListeners();
    }
    loadExtraItems();
  }

  if (step === 4) loadSamples();
  if (step === 5) loadTests();
  if (step === 6) generateReview();

  currentStep = step;
  console.log("📍 Switched to step:", step);
}

async function handleNext() {
  console.log("➡️ Next button clicked, current step:", currentStep);

  if (!validateStep(currentStep)) {
    return;
  }

  if (currentStep === 1) {
    const clientId = document.getElementById("selectedClientId").value;

    if (!clientId) {
      const created = await createNewClient();
      if (!created) {
        return;
      }
    } else {
      const updated = await updateClientIfModified();
      if (!updated) {
        return;
      }
    }
  }

  // CATEGORY INTERCEPTOR: Before going from Step 4 → Step 5
  if (currentStep === 4) {
    const intercepted = await checkNewSampleNames();
    if (intercepted) {
      return; // Modal is shown, don't advance yet
    }
  }

  showStep(currentStep + 1);
}

function handlePrev() {
  showStep(currentStep - 1);
}

function validateStep(step) {
  console.log("🔍 Validating step:", step);

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
    if (!validateReceivedDate()) {
      showToast("Please fix received date errors", "error");
      return false;
    }

    if (!validateReceivedTime()) {
      showToast("Please fix received time errors", "error");
      return false;
    }

    if (!validateTentativeDate()) {
      showToast("Please fix tentative date errors", "error");
      return false;
    }

    // Additional charges are auto-calculated from extra items - always valid
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
        `input[data-sample="${i}"]:checked`,
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
  console.log("✅ Submission type selected:", submissionType);

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

  // Build container options from extra items
  let containerOptions = '<option value="">None</option>';
  allExtraItems.forEach((item) => {
    containerOptions += `<option value="${item.item_id}">${escapeHtml(item.item_name)} (${item.item_value}${item.item_unit})</option>`;
  });

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
          <input type="hidden" class="sample-category-id" value="">
          <input type="hidden" class="sample-category-name" value="">
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
        <div class="col-12"><hr class="my-2 opacity-25"></div>
        <div class="col-md-4">
          <label>Container Type</label>
          <select class="form-select sample-container">${containerOptions}</select>
        </div>
        <div class="col-md-4">
          <label>Container Damage</label>
          <select class="form-select sample-damage"><option>No</option><option>Yes</option></select>
        </div>
        <div class="col-md-4">
          <label>Validity</label>
          <select class="form-select sample-validity"><option>OK</option><option>Damaged</option><option>Expired</option></select>
        </div>
        <div class="col-md-4">
          <label>Temperature Condition</label>
          <select class="form-select sample-temp">
            <option>Ambient</option><option>Chilled</option><option>Frozen</option>
          </select>
        </div>
        <div class="col-md-4 temp-slider-col" style="display:none;">
          <label>Temperature (°C)</label>
          <div class="temp-slider-wrapper">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="temp-slider-value badge bg-primary">4.0°C</span>
              <small class="text-muted">2.0 – 6.0°C</small>
            </div>
            <input type="range" class="form-range sample-temp-value" min="2" max="6" step="0.5" value="4">
          </div>
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
    }, 400),
  );

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

  // Temperature slider toggle
  const tempSelect = card.querySelector(".sample-temp");
  const sliderCol = card.querySelector(".temp-slider-col");
  const tempSlider = card.querySelector(".sample-temp-value");
  const tempBadge = card.querySelector(".temp-slider-value");

  tempSelect.addEventListener("change", function () {
    if (this.value === "Chilled") {
      sliderCol.style.display = "block";
    } else {
      sliderCol.style.display = "none";
    }
  });

  if (tempSlider && tempBadge) {
    tempSlider.addEventListener("input", function () {
      tempBadge.textContent = parseFloat(this.value).toFixed(1) + "°C";
    });
  }
}

function renumberSamples() {
  sampleCount = 0;
  document.querySelectorAll(".sample-card").forEach((card) => {
    sampleCount++;
    card.dataset.index = sampleCount;
    card.querySelector("h5").textContent = `Sample ${sampleCount}`;
  });
}

async function handleSampleNameSearch(input) {
  const query = input.value.trim();
  const card = input.closest(".sample-card");
  const dropdown = input
    .closest(".position-relative")
    .querySelector(".sample-name-autocomplete");
  const categoryIdField = card.querySelector(".sample-category-id");
  const categoryNameField = card.querySelector(".sample-category-name");

  if (query.length < 2) {
    dropdown.classList.remove("show");
    return;
  }

  // ✅ FIX: Include submission type for filtering
  if (!submissionType) {
    console.warn("⚠️ Submission type not selected yet");
    dropdown.classList.remove("show");
    return;
  }

  try {
    const res = await fetch(
      `${API_BASE}?action=searchSampleNames&query=${encodeURIComponent(query)}&type=${submissionType}`,
    );
    const data = await res.json();

    if (data.success && data.names && data.names.length > 0) {
      let html = "";
      data.names.forEach((n) => {
        const catBadge = n.category_name
          ? `<span class="badge bg-secondary ms-1">${escapeHtml(n.category_name)}</span>`
          : "";
        html += `<div class="autocomplete-item" data-name="${escapeHtml(n.sample_name)}" data-category-id="${n.category_id || ""}" data-category-name="${escapeHtml(n.category_name || "")}">
          ${escapeHtml(n.sample_name)} ${catBadge}
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
          // Store category info
          if (categoryIdField)
            categoryIdField.value = item.dataset.categoryId || "";
          if (categoryNameField)
            categoryNameField.value = item.dataset.categoryName || "";
        };
      });
    } else {
      dropdown.classList.remove("show");
      // Clear category since name is not in DB
      if (categoryIdField) categoryIdField.value = "";
      if (categoryNameField) categoryNameField.value = "";
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
      `${API_BASE}?action=getParameters&type=${submissionType}`,
    );
    const data = await res.json();

    if (!data.success) throw new Error(data.message);

    allParameters = data.parameters;
    console.log("✅ Loaded parameters:", allParameters);

    await loadCombos();

    renderTests();
  } catch (err) {
    console.error("Load tests error:", err);
    container.innerHTML =
      "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Failed to load tests. Please try again.</div>";
  }
}

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

function detectCombosInSelection() {
  const selectedTests = Array.from(
    document.querySelectorAll(".test-checkbox:checked"),
  );

  if (selectedTests.length < 2) {
    return [];
  }

  const selectedParams = selectedTests
    .map((cb) => parseInt(cb.dataset.param))
    .sort((a, b) => a - b);

  const sortedCombos = [...availableCombos].sort(
    (a, b) => b.parameter_ids.length - a.parameter_ids.length,
  );

  const detected = [];
  const usedParams = new Set();

  for (const combo of sortedCombos) {
    const comboParams = combo.parameter_ids
      .map((p) => parseInt(p))
      .sort((a, b) => a - b);

    const allMatch = comboParams.every((pid) => selectedParams.includes(pid));

    if (!allMatch) {
      continue;
    }

    const hasConflict = comboParams.some((pid) => usedParams.has(pid));

    if (hasConflict) {
      continue;
    }

    detected.push(combo);

    comboParams.forEach((pid) => usedParams.add(pid));
  }

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
            v.price,
          );
        });
      } else {
        html += createTestCheckbox(
          sampleIdx,
          param.parameter_id,
          null,
          param.parameter_name,
          param.price,
        );
      }
    });

    html += `</div></div>`;
    container.insertAdjacentHTML("beforeend", html);
  });

  document.querySelectorAll(".test-checkbox").forEach((cb) => {
    cb.addEventListener("change", function (e) {
      const sampleNum = this.dataset.sample;
      const checked = document.querySelectorAll(
        `input[data-sample="${sampleNum}"]:checked`,
      );

      if (checked.length > 10) {
        e.preventDefault();
        this.checked = false;
        showToast(
          `Sample ${sampleNum} can have maximum 10 tests (physical form limit)`,
          "warning",
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

  html += `
    <div class="review-section">
      <h6><i class="fas fa-user"></i> Client Information</h6>
      <p><strong>Name:</strong> ${escapeHtml(document.getElementById("clientName").value)}</p>
      <p><strong>Phone:</strong> ${escapeHtml(document.getElementById("phonePrimary").value)}</p>
      <p><strong>Contact:</strong> ${escapeHtml(document.getElementById("contactPerson").value || "N/A")}</p>
    </div>`;

  const collectedDateVal = document.getElementById("collectedDate")?.value;
  const collectedTimeVal = document.getElementById("collectedTime")?.value;
  let collectedHtml = "";
  if (collectedDateVal) {
    collectedHtml = `<p><strong>Collected:</strong> ${formatDate(collectedDateVal)}${collectedTimeVal ? " at " + collectedTimeVal : ""}</p>`;
  }

  html += `
    <div class="review-section">
      <h6><i class="fas fa-calendar-alt"></i> Submission Details</h6>
      <p><strong>Type:</strong> ${submissionType.charAt(0).toUpperCase() + submissionType.slice(1)}</p>
      <p><strong>Received:</strong> ${formatDate(document.getElementById("receivedDate").value)} at ${
        document.getElementById("receivedTime").value
      }</p>
      ${collectedHtml}
      <p><strong>Tentative:</strong> ${formatDate(document.getElementById("tentativeDate").value)}</p>
    </div>`;

  let individualTotal = 0;
  html +=
    '<div class="review-section"><h6><i class="fas fa-flask"></i> Samples & Tests</h6>';

  document.querySelectorAll(".sample-card").forEach((sample, idx) => {
    const sampleIdx = idx + 1;
    const sampleName = sample.querySelector(".sample-name-input").value;

    html += `<p><strong>Sample ${sampleIdx}:</strong> ${escapeHtml(sampleName)}</p><ul>`;

    const selectedTests = document.querySelectorAll(
      `input[data-sample="${sampleIdx}"]:checked`,
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

  const combosDetected = detectCombosInSelection();
  const hasCombo = combosDetected.length > 0;

  let finalTotal = individualTotal;
  let totalDiscount = 0;

  if (hasCombo) {
    let usedParams = new Set();

    combosDetected.forEach((combo) => {
      const canApply = combo.parameter_ids.every((pid) => !usedParams.has(pid));

      if (canApply) {
        finalTotal -= combo.individual_total;
        finalTotal += combo.combo_price;
        totalDiscount += combo.savings;

        combo.parameter_ids.forEach((pid) => usedParams.add(pid));
      }
    });
  }

  const addCharges =
    parseFloat(document.getElementById("additionalCharges").value) || 0;
  const grandTotal = finalTotal + addCharges;

  // Show extra items in review if any
  const extraItemsForReview = getExtraItemsData();
  if (extraItemsForReview.length > 0) {
    html +=
      '<div class="review-section"><h6><i class="fas fa-box-open"></i> Additional Items</h6><ul>';
    extraItemsForReview.forEach((ei) => {
      const item = allExtraItems.find((i) => i.item_id == ei.item_id);
      if (item) {
        html += `<li>${escapeHtml(item.item_name)} (${item.item_value}${item.item_unit}) × ${ei.quantity} = ${formatCurrency(ei.unit_price * ei.quantity)}</li>`;
      }
    });
    html += `</ul><p><strong>Extra Items Total:</strong> ${formatCurrency(addCharges)}</p></div>`;
  }

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
          ${combosDetected.length} combo${combosDetected.length > 1 ? "s" : ""} applied: 
          ${combosDetected.map((c) => c.combo_name).join(", ")}
        </p>
        <p><strong>Test Charges:</strong> 
          <span id="testChargesTotal" style="color: #007bff; font-weight: bold;">
            ${formatCurrency(finalTotal)}
          </span>
        </p>
        <p><strong>Additional Charges:</strong> ${formatCurrency(addCharges)}</p>
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
    html += `
      <div class="review-section">
        <h6><i class="fas fa-calculator"></i> Totals</h6>
        <p><strong>Test Charges:</strong> 
          <span id="testChargesTotal">${formatCurrency(individualTotal)}</span>
        </p>
        <p><strong>Additional Charges:</strong> ${formatCurrency(addCharges)}</p>
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
      ?.textContent.replace(/[^0-9.]/g, "") || 0,
  );
  const addCharges = parseFloat(
    document.getElementById("additionalCharges").value || 0,
  );
  const grand = testTotal + addCharges;

  const grandTotalElement = document.getElementById("grandTotalDisplay");
  if (grandTotalElement) {
    grandTotalElement.textContent = formatCurrency(grand);
  }
}

// ==========================================
// SUBMIT
// ==========================================

async function handleSubmit(e) {
  e.preventDefault();

  // ✅ SUBMISSION LOCK
  if (isSubmitting) {
    console.warn("⚠️ Submission already in progress");
    return;
  }
  isSubmitting = true;
  console.log("🔒 Submission lock acquired");

  // Validate email if provided
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

  // ✅ FINAL DATE/TIME VALIDATION
  if (!validateReceivedDate()) {
    showToast("Please fix received date errors", "error");
    isSubmitting = false;
    return;
  }

  if (!validateReceivedTime()) {
    showToast("Please fix received time errors", "error");
    isSubmitting = false;
    return;
  }

  if (!validateTentativeDate()) {
    showToast("Please fix tentative date errors", "error");
    isSubmitting = false;
    return;
  }

  const submitBtn = document.getElementById("submitBtn");
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

  const formData = new FormData();
  formData.append("action", "saveSample");
  formData.append(
    "submitted_by",
    document.querySelector('[name="submitted_by"]').value,
  );
  formData.append("user_id", document.querySelector('[name="user_id"]').value);
  formData.append(
    "client_id",
    document.getElementById("selectedClientId").value,
  );
  formData.append(
    "city_id",
    document.getElementById("selectedCityId")?.value || "",
  );
  formData.append("submission_type", submissionType);
  formData.append(
    "received_date",
    document.getElementById("receivedDate").value,
  );
  formData.append(
    "received_time",
    document.getElementById("receivedTime").value,
  );
  formData.append(
    "tentative_date",
    document.getElementById("tentativeDate").value,
  );
  formData.append(
    "sample_collected_date",
    document.getElementById("collectedDate")?.value || "",
  );
  formData.append(
    "sample_collected_time",
    document.getElementById("collectedTime")?.value || "",
  );

  const addCharges =
    parseFloat(document.getElementById("additionalCharges").value) || 0;
  formData.append("additional_charges", addCharges);

  // Extra items data
  const extraItemsSubmit = getExtraItemsData();
  formData.append("extra_items", JSON.stringify(extraItemsSubmit));

  const samples = [];
  document.querySelectorAll(".sample-card").forEach((card) => {
    const tempCondition = card.querySelector(".sample-temp").value;
    const tempValue =
      tempCondition === "Chilled"
        ? card.querySelector(".sample-temp-value")?.value || null
        : null;
    const containerSelect = card.querySelector(".sample-container");
    const containerItemId = containerSelect?.value || null;
    const categoryId = card.querySelector(".sample-category-id")?.value || null;

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
      temperature_condition: tempCondition,
      temperature_value: tempValue,
      container_item_id: containerItemId,
      sample_category_id: categoryId,
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

  formData.append("payment_status", "Not Paid");
  formData.append("payment_reference", "");

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
        "success",
      );

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
    isSubmitting = false;
  }
}

// ==========================================
// CITY AUTOCOMPLETE
// ==========================================

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

  console.log("✅ City autocomplete initialized");
}

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
      `${API_BASE}?action=searchCities&query=${encodeURIComponent(query)}`,
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
    `,
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

  console.log(`✅ City selected: ${cityName} (ID: ${cityId})`);
}

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

function closeCityAutocomplete() {
  const cityAutocomplete = document.getElementById("cityAutocomplete");
  if (cityAutocomplete) {
    cityAutocomplete.classList.remove("show");
    cityAutocomplete.innerHTML = "";
  }
}

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
    ".city-autocomplete-item.active",
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

function highlightCityItem(items, index) {
  items.forEach((item) => item.classList.remove("active"));
  items[index].classList.add("active");
  items[index].scrollIntoView({ block: "nearest", behavior: "smooth" });
}

async function loadCityIdForClient(cityName) {
  if (!cityName || cityName.trim() === "") {
    return;
  }

  try {
    const response = await fetch(
      `${API_BASE}?action=findCityByName&city_name=${encodeURIComponent(cityName)}`,
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
          `✅ City ID loaded: ${data.city_name} (ID: ${data.city_id})`,
        );
      }
    } else {
      console.log(`City not found in database: ${cityName}`);
    }
  } catch (error) {
    console.warn("Error loading city ID:", error);
  }
}

// ==========================================
// EXTRA ITEMS MANAGEMENT
// ==========================================

async function loadExtraItems() {
  const container = document.getElementById("extraItemsContainer");
  if (!container) return;

  try {
    const res = await fetch(`${API_BASE}?action=getExtraItems`);
    const data = await res.json();

    if (data.success && data.items && data.items.length > 0) {
      allExtraItems = data.items;
      let html = '<div class="row g-2">';

      data.items.forEach((item) => {
        const label = `${escapeHtml(item.item_name)} (${item.item_value}${item.item_unit})`;
        const priceLabel =
          item.item_price > 0
            ? `Rs. ${parseFloat(item.item_price).toFixed(2)}`
            : "Free";

        html += `
          <div class="col-md-6 col-lg-4">
            <div class="extra-item-card border rounded p-3 d-flex align-items-center justify-content-between" data-item-id="${item.item_id}" data-price="${item.item_price}">
              <div>
                <strong>${label}</strong><br>
                <small class="text-muted">${priceLabel} each</small>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary extra-item-minus" disabled>
                  <i class="fas fa-minus"></i>
                </button>
                <span class="extra-item-qty fw-bold" style="min-width:24px;text-align:center;">0</span>
                <button type="button" class="btn btn-sm btn-outline-primary extra-item-plus">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
          </div>`;
      });

      html += "</div>";
      container.innerHTML = html;

      // Wire up +/- buttons
      container.querySelectorAll(".extra-item-card").forEach((card) => {
        const minusBtn = card.querySelector(".extra-item-minus");
        const plusBtn = card.querySelector(".extra-item-plus");
        const qtySpan = card.querySelector(".extra-item-qty");

        plusBtn.addEventListener("click", () => {
          let qty = parseInt(qtySpan.textContent) || 0;
          qty++;
          qtySpan.textContent = qty;
          minusBtn.disabled = qty <= 0;
          updateExtraItemsTotal();
        });

        minusBtn.addEventListener("click", () => {
          let qty = parseInt(qtySpan.textContent) || 0;
          if (qty > 0) qty--;
          qtySpan.textContent = qty;
          minusBtn.disabled = qty <= 0;
          updateExtraItemsTotal();
        });
      });
    } else {
      container.innerHTML =
        '<div class="text-muted p-3">No extra items available</div>';
    }
  } catch (err) {
    console.error("Error loading extra items:", err);
    container.innerHTML =
      '<div class="text-danger p-3"><i class="fas fa-exclamation-triangle"></i> Failed to load extra items</div>';
  }
}

function updateExtraItemsTotal() {
  let total = 0;
  document.querySelectorAll(".extra-item-card").forEach((card) => {
    const qty =
      parseInt(card.querySelector(".extra-item-qty").textContent) || 0;
    const price = parseFloat(card.dataset.price) || 0;
    total += qty * price;
  });

  const display = document.getElementById("extraItemsTotalDisplay");
  if (display) display.textContent = `Rs. ${total.toFixed(2)}`;

  const hidden = document.getElementById("additionalCharges");
  if (hidden) hidden.value = total.toFixed(2);

  updateGrandTotal();
}

function getExtraItemsData() {
  const items = [];
  document.querySelectorAll(".extra-item-card").forEach((card) => {
    const qty =
      parseInt(card.querySelector(".extra-item-qty").textContent) || 0;
    if (qty > 0) {
      items.push({
        item_id: parseInt(card.dataset.itemId),
        quantity: qty,
        unit_price: parseFloat(card.dataset.price) || 0,
      });
    }
  });
  return items;
}

// ==========================================
// CATEGORY INTERCEPTOR (Step 4 → Step 5)
// ==========================================

async function checkNewSampleNames() {
  const newNames = [];

  document.querySelectorAll(".sample-card").forEach((card) => {
    const name = card.querySelector(".sample-name-input").value.trim();
    const categoryId = card.querySelector(".sample-category-id")?.value;

    if (name && !categoryId) {
      // Name not from DB autocomplete — it's new
      if (!newNames.find((n) => n.name.toLowerCase() === name.toLowerCase())) {
        newNames.push({ name });
      }
    }
  });

  if (newNames.length === 0) {
    return false; // No interception needed
  }

  // Build modal content
  const listContainer = document.getElementById("newNamesListContainer");
  let listHtml = "";

  newNames.forEach((entry, idx) => {
    let options = "";
    sampleCategories.forEach((cat) => {
      const selected = cat.id === 4 ? "selected" : "";
      options += `<option value="${cat.id}" ${selected}>${escapeHtml(cat.name)}</option>`;
    });

    listHtml += `
      <div class="new-name-row row align-items-center mb-2 pb-2 border-bottom" data-name="${escapeHtml(entry.name)}">
        <div class="col-5">
            <span class="fw-bold">${escapeHtml(entry.name)}</span>
        </div>
        <div class="col-4">
            <select class="form-select form-select-sm new-name-category">${options}</select>
        </div>
        <div class="col-3 text-center">
            <input class="form-check-input new-name-slab" type="checkbox" value="1">
        </div>
      </div>`;
  });

  listContainer.innerHTML = listHtml;

  // Show modal
  const modal = new bootstrap.Modal(
    document.getElementById("newSampleNamesModal"),
  );
  modal.show();

  // Return a promise that resolves when Save is clicked
  return new Promise((resolve) => {
    const saveBtn = document.getElementById("saveNewNamesBtn");

    // Remove old listeners
    const newSaveBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

    newSaveBtn.addEventListener("click", async () => {
      newSaveBtn.disabled = true;
      newSaveBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Saving...';

      const namesPayload = [];
      listContainer.querySelectorAll(".new-name-row").forEach((row) => {
        namesPayload.push({
          name: row.dataset.name,
          category_id: parseInt(row.querySelector(".new-name-category").value),
          is_slab_accredited: row.querySelector(".new-name-slab").checked
            ? 1
            : 0,
        });
      });

      try {
        const fd = new FormData();
        fd.append("action", "bulkCreateSampleNames");
        fd.append("names", JSON.stringify(namesPayload));

        const res = await fetch(API_BASE, { method: "POST", body: fd });
        const result = await res.json();

        if (result.success) {
          // Update hidden fields on matching sample cards
          namesPayload.forEach((np) => {
            document.querySelectorAll(".sample-card").forEach((card) => {
              const nameInput = card.querySelector(".sample-name-input");
              if (
                nameInput.value.trim().toLowerCase() === np.name.toLowerCase()
              ) {
                const catField = card.querySelector(".sample-category-id");
                const catNameField = card.querySelector(
                  ".sample-category-name",
                );
                if (catField) catField.value = np.category_id;
                if (catNameField) {
                  const cat = sampleCategories.find(
                    (c) => c.id === np.category_id,
                  );
                  catNameField.value = cat ? cat.name : "Other";
                }
              }
            });
          });

          showToast(`${result.count} sample name(s) saved`, "success");
        } else {
          showToast(result.message || "Failed to save names", "error");
        }
      } catch (err) {
        console.error("Error saving new sample names:", err);
        showToast("Error saving sample names", "error");
      }

      modal.hide();
      newSaveBtn.disabled = false;
      newSaveBtn.innerHTML = '<i class="fas fa-check"></i> Save & Continue';

      // Now advance to step 5
      showStep(currentStep + 1);
      resolve(true);
    });
  });
}
