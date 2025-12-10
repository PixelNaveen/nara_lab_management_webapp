/**
 * Sample Submission JavaScript - Simplified Version (No Validation)
 * Only includes step navigation and dummy search functionality
 *
 * @version 2.1 (Validation Removed)
 */

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString("en-GB");
  } catch {
    return dateStr;
  }
}

function formatCurrency(amount) {
  return "Rs. " + parseFloat(amount).toFixed(2);
}

function showToast(message, type = "info") {
  const toast = document.getElementById("notificationToast");
  const toastBody = document.getElementById("toastMessage");
  const toastTitle = document.getElementById("toastTitle");
  const icon = toast.querySelector(".toast-icon");

  const icons = {
    success: "fa-check-circle",
    error: "fa-times-circle",
    warning: "fa-exclamation-triangle",
    info: "fa-info-circle",
  };

  const colorClasses = {
    success: "text-success",
    error: "text-danger",
    warning: "text-warning",
    info: "text-info",
  };

  const titles = {
    success: "Success",
    error: "Error",
    warning: "Warning",
    info: "Information",
  };

  icon.className = `fas ${icons[type] || icons.info} ${
    colorClasses[type] || colorClasses.info
  } toast-icon`;
  toastTitle.textContent = titles[type] || titles.info;
  toastBody.textContent = message;

  const bsToast = new bootstrap.Toast(toast, { delay: 2500 });
  bsToast.show();
}

// ==========================================
// GLOBAL STATE
// ==========================================

let currentStep = 1;
let sampleCount = 0;
let submissionType = "";

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  initializeDateRestrictions();
  initializeEventListeners();
  showStep(1);
});

function initializeDateRestrictions() {
  const today = new Date().toISOString().split("T")[0];
  const fiveDaysAgo = new Date();
  fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
  const minDate = fiveDaysAgo.toISOString().split("T")[0];

  document.getElementById("receivedDate").min = minDate;
  document.getElementById("receivedDate").max = today;
  document.getElementById("tentativeDate").min = today;
}

function initializeEventListeners() {
  // Client search with dummy data
  const clientSearchInput = document.getElementById("clientSearch");
  clientSearchInput.addEventListener("input", handleClientSearch);

  // Submission type selection
  document.querySelectorAll(".type-card").forEach((card) => {
    card.addEventListener("click", selectSubmissionType);
  });

  // Add sample button
  document.getElementById("addSampleBtn").addEventListener("click", addSample);

  // Navigation buttons
  document.getElementById("nextBtn").addEventListener("click", handleNext);
  document.getElementById("prevBtn").addEventListener("click", handlePrev);
  document.getElementById("siForm").addEventListener("submit", handleSubmit);

  // Close dropdowns when clicking outside
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".position-relative")) {
      document
        .querySelectorAll(".sample-name-autocomplete, #clientResults")
        .forEach((el) => el.classList.remove("show"));
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
  showStep(currentStep + 1);
}

function handlePrev() {
  showStep(currentStep - 1);
}

// ==========================================
// CLIENT STEP (Step 1) - With Dummy Data
// ==========================================

// Dummy client data
const dummyClients = [
  { id: 1, name: "ABC Laboratories", phone: "0712345678", contact: "John Doe" },
  { id: 2, name: "XYZ Testing Center", phone: "0723456789", contact: "Jane Smith" },
  { id: 3, name: "BioTech Solutions", phone: "0734567890", contact: "Robert Johnson" },
  { id: 4, name: "Medical Diagnostics Ltd", phone: "0745678901", contact: "Emily Davis" },
  { id: 5, name: "Food Safety Institute", phone: "0756789012", contact: "Michael Wilson" }
];

function handleClientSearch() {
  const query = this.value.trim().toLowerCase();
  const resultsContainer = document.getElementById("clientResults");

  if (query.length < 2) {
    resultsContainer.innerHTML = "";
    resultsContainer.classList.remove("show");
    return;
  }

  // Filter dummy clients based on query
  const filteredClients = dummyClients.filter(client => 
    client.name.toLowerCase().includes(query) || 
    client.phone.includes(query) || 
    client.contact.toLowerCase().includes(query)
  );

  if (filteredClients.length > 0) {
    let html = "";
    filteredClients.forEach((client) => {
      html += `
        <div class="client-item" 
             data-id="${client.id}"
             data-name="${client.name}"
             data-phone="${client.phone}"
             data-contact="${client.contact}">
          <strong>${client.name}</strong>
          <small>${client.phone} - ${client.contact}</small>
        </div>
      `;
    });
    resultsContainer.innerHTML = html;
    resultsContainer.classList.add("show");

    // Add click handlers
    document.querySelectorAll(".client-item").forEach((item) => {
      item.addEventListener("click", selectClient);
    });
  } else {
    resultsContainer.innerHTML = '<div class="client-item">No clients found</div>';
    resultsContainer.classList.add("show");
  }
}

function selectClient() {
  const clientId = this.dataset.id;
  const clientName = this.dataset.name;
  const clientPhone = this.dataset.phone;
  const clientContact = this.dataset.contact;

  document.getElementById("selectedClientId").value = clientId;
  document.getElementById("clientName").value = clientName;
  document.getElementById("phonePrimary").value = clientPhone;
  document.getElementById("contactPerson").value = clientContact;

  // Store originals for comparison
  document.getElementById("originalClientName").value = clientName;
  document.getElementById("originalPhone").value = clientPhone;
  document.getElementById("originalContactPerson").value = clientContact;

  document.getElementById("clientResults").classList.remove("show");
  document.getElementById("clientSearch").value = clientName;
}

// ==========================================
// TYPE STEP (Step 2)
// ==========================================

function selectSubmissionType() {
  document
    .querySelectorAll(".type-card")
    .forEach((c) => c.classList.remove("selected"));
  this.classList.add("selected");
  submissionType = this.dataset.type;
}

// ==========================================
// SAMPLES STEP (Step 4) - With Dummy Data
// ==========================================

function loadSamples() {
  const container = document.getElementById("samplesContainer");
  container.innerHTML = "";
  sampleCount = 0;
  addSample(); // Add first sample by default
}

function addSample() {
  sampleCount++;
  const html = `
    <div class="sample-card" data-index="${sampleCount}">
      <div class="sample-header">
        <h5>Sample ${sampleCount}</h5>
        <button type="button" class="btn btn-sm btn-outline-danger remove-sample">
          <i class="fas fa-trash"></i> Remove
        </button>
      </div>
      <div class="row g-3">
        <div class="col-md-4 position-relative">
          <label class="form-label">Sample Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-name-input" required 
                 placeholder="e.g., Drinking Water">
          <div class="sample-name-autocomplete"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Value <span class="text-danger">*</span></label>
          <input type="text" class="form-control sample-value" required placeholder="e.g., 100">
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit <span class="text-danger">*</span></label>
          <select class="form-select sample-unit" required>
            <option value="">Select unit</option>
            <option value="cm²">cm²</option>
            <option value="ml">ml</option>
            <option value="g">g</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Client Sample Code</label>
          <input type="text" class="form-control sample-client-code">
        </div>
        <div class="col-md-6">
          <label class="form-label">Sampling Location</label>
          <input type="text" class="form-control sample-location">
        </div>
        <div class="col-12">
          <label class="form-label">Reason for Analysis</label>
          <textarea class="form-control sample-reason" rows="2"></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label">Container Damage</label>
          <select class="form-select sample-damage">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Temperature Condition</label>
          <select class="form-select sample-temp">
            <option value="Ambient">Ambient</option>
            <option value="Chilled">Chilled</option>
            <option value="Frozen">Frozen</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Validity Status</label>
          <select class="form-select sample-validity">
            <option value="OK">OK</option>
            <option value="Not OK">Not OK</option>
          </select>
        </div>
      </div>
    </div>
  `;

  document.getElementById("samplesContainer").insertAdjacentHTML("beforeend", html);

  const card = document.querySelector(`.sample-card[data-index="${sampleCount}"]`);
  card.querySelector(".remove-sample").addEventListener("click", removeSample);

  const nameInput = card.querySelector(".sample-name-input");
  nameInput.addEventListener("input", handleSampleNameSearch);
}

function removeSample() {
  if (sampleCount <= 1) {
    showToast("At least one sample is required", "warning");
    return;
  }

  this.closest(".sample-card").remove();
  sampleCount--;

  // Renumber remaining samples and update data attributes
  document.querySelectorAll(".sample-card").forEach((card, idx) => {
    const newIndex = idx + 1;
    card.dataset.index = newIndex;
    card.querySelector("h5").textContent = `Sample ${newIndex}`;
  });
}

// Dummy sample names
const dummySampleNames = [
  { name: "Drinking Water", usage_count: 15 },
  { name: "Ice Cubes", usage_count: 8 },
  { name: "Surface Swab", usage_count: 12 },
  { name: "Food Sample", usage_count: 20 },
  { name: "Treated Water", usage_count: 6 },
  { name: "Raw Milk", usage_count: 9 },
  { name: "Fruit Juice", usage_count: 4 },
  { name: "Vegetable Sample", usage_count: 7 }
];

function handleSampleNameSearch() {
  const input = this;
  const query = input.value.trim().toLowerCase();
  const autocomplete = input.nextElementSibling;

  if (query.length < 2) {
    autocomplete.innerHTML = "";
    autocomplete.classList.remove("show");
    return;
  }

  // Filter dummy sample names based on query
  const filteredNames = dummySampleNames.filter(sample => 
    sample.name.toLowerCase().includes(query)
  );

  if (filteredNames.length > 0) {
    let html = "";
    filteredNames.forEach((sample) => {
      html += `
        <div class="autocomplete-item" data-name="${sample.name}">
          <span>${sample.name}</span>
          <span class="autocomplete-usage">${sample.usage_count} uses</span>
        </div>
      `;
    });
    autocomplete.innerHTML = html;
    autocomplete.classList.add("show");

    autocomplete.querySelectorAll(".autocomplete-item").forEach((item) => {
      item.addEventListener("click", () => {
        input.value = item.dataset.name;
        autocomplete.classList.remove("show");
      });
    });
  } else {
    autocomplete.innerHTML = '<div class="autocomplete-item">No suggestions found</div>';
    autocomplete.classList.add("show");
  }
}

// ==========================================
// TESTS STEP (Step 5) - With Dummy Data
// ==========================================

function loadTests() {
  const container = document.getElementById("testsContainer");
  container.innerHTML = "";

  // Dummy test parameters
  const dummyParameters = [
    { 
      id: 1, 
      name: "Aerobic Plate Count", 
      price: 1250.00,
      variants: [
        { id: 1, name: "37°C", price: 1250.00 },
        { id: 2, name: "30°C", price: 1350.00 },
        { id: 3, name: "22°C", price: 1450.00 }
      ]
    },
    { 
      id: 2, 
      name: "Coliforms", 
      price: 1125.00,
      variants: []
    },
    { 
      id: 3, 
      name: "E. coli", 
      price: 1375.00,
      variants: []
    },
    { 
      id: 4, 
      name: "Salmonella spp.", 
      price: 2800.00,
      variants: [
        { id: 4, name: "Standard", price: 2800.00 },
        { id: 5, name: "Enhanced", price: 3200.00 }
      ]
    }
  ];

  // Create test selection for each sample
  document.querySelectorAll(".sample-card").forEach((sampleCard, idx) => {
    const sampleIndex = idx + 1;
    const sampleName = sampleCard.querySelector(".sample-name-input").value || `Sample ${sampleIndex}`;

    let html = `
      <div class="test-card">
        <h5><i class="fas fa-flask"></i> ${sampleName}</h5>
        <div class="test-list">
    `;

    dummyParameters.forEach((param) => {
      if (param.variants && param.variants.length > 0) {
        // Show variants
        param.variants.forEach((variant) => {
          html += `
            <div class="form-check">
              <input class="form-check-input test-checkbox" 
                     type="checkbox" 
                     name="test_${sampleIndex}" 
                     data-sample="${sampleIndex}"
                     data-param="${param.id}"
                     data-variant="${variant.id}"
                     data-price="${variant.price}"
                     id="test_${sampleIndex}_v${variant.id}">
              <label class="form-check-label" for="test_${sampleIndex}_v${variant.id}">
                ${param.name} - ${variant.name}
                <strong class="float-end">${formatCurrency(variant.price)}</strong>
              </label>
            </div>
          `;
        });
      } else {
        // Show parameter only
        html += `
          <div class="form-check">
            <input class="form-check-input test-checkbox" 
                   type="checkbox" 
                   name="test_${sampleIndex}" 
                   data-sample="${sampleIndex}"
                   data-param="${param.id}"
                   data-price="${param.price}"
                   id="test_${sampleIndex}_p${param.id}">
            <label class="form-check-label" for="test_${sampleIndex}_p${param.id}">
              ${param.name}
              <strong class="float-end">${formatCurrency(param.price)}</strong>
            </label>
          </div>
        `;
      }
    });

    html += `
        </div>
      </div>
    `;

    container.insertAdjacentHTML("beforeend", html);
  });

  // Add change listeners to all test checkboxes for live total calculation
  document.querySelectorAll(".test-checkbox").forEach((checkbox) => {
    checkbox.addEventListener("change", calculateTestTotals);
  });
}

function calculateTestTotals() {
  // Optional: Display running total during test selection
  let total = 0;
  document.querySelectorAll(".test-checkbox:checked").forEach((test) => {
    total += parseFloat(test.dataset.price);
  });
  // You can update a display element here if you have one
  // Example: document.getElementById("runningTotal").textContent = formatCurrency(total);
}

// ==========================================
// REVIEW STEP (Step 6)
// ==========================================

function generateReview() {
  const container = document.getElementById("reviewSummary");
  let html = "";

  // Client Information
  html += `
    <div class="review-section">
      <h6><i class="fas fa-user"></i> Client Information</h6>
      <p><strong>Name:</strong> ${document.getElementById("clientName").value}</p>
      <p><strong>Phone:</strong> ${document.getElementById("phonePrimary").value}</p>
      <p><strong>Contact Person:</strong> ${document.getElementById("contactPerson").value || "N/A"}</p>
    </div>
  `;

  // Submission Details
  html += `
    <div class="review-section">
      <h6><i class="fas fa-calendar-alt"></i> Submission Details</h6>
      <p><strong>Type:</strong> ${submissionType.charAt(0).toUpperCase() + submissionType.slice(1)}</p>
      <p><strong>Received:</strong> ${formatDate(document.getElementById("receivedDate").value)}</p>
      <p><strong>Tentative:</strong> ${formatDate(document.getElementById("tentativeDate").value)}</p>
      <p><strong>Notes:</strong> ${document.getElementById("additionalNotes").value || "None"}</p>
    </div>
  `;

  // Samples and Tests
  let testTotal = 0;
  html += '<div class="review-section"><h6><i class="fas fa-flask"></i> Samples & Tests</h6>';

  document.querySelectorAll(".sample-card").forEach((sample, idx) => {
    const sampleIndex = idx + 1;
    const sampleName = sample.querySelector(".sample-name-input").value;

    html += `<p><strong>Sample ${sampleIndex}:</strong> ${sampleName}</p><ul>`;

    const selectedTests = document.querySelectorAll(`input[name="test_${sampleIndex}"]:checked`);
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
  const addCharges = Math.max(0, parseFloat(document.getElementById("additionalCharges").value) || 0);
  const grandTotal = testTotal + addCharges;

  html += `
    <div class="review-section">
      <h6><i class="fas fa-calculator"></i> Totals</h6>
      <p><strong>Test Charges:</strong> ${formatCurrency(testTotal)}</p>
      <p><strong>Additional Charges:</strong> ${formatCurrency(addCharges)}</p>
      <h5 class="mt-3"><strong>Grand Total: ${formatCurrency(grandTotal)}</strong></h5>
    </div>
  `;

  container.innerHTML = html;

  // Payment selection listeners
  document.querySelectorAll(".payment-option").forEach((option) => {
    option.addEventListener("click", selectPaymentStatus);
  });
}

function selectPaymentStatus() {
  document.querySelectorAll(".payment-option").forEach((o) => o.classList.remove("selected"));
  this.classList.add("selected");
  const status = this.dataset.status;
  document.getElementById("paymentReferenceSection").classList.toggle("d-none", status !== "paid");
}

// ==========================================
// FORM SUBMISSION - Simplified (No Validation)
// ==========================================

function handleSubmit(e) {
  e.preventDefault();
  
  showToast("Form submitted successfully! (This is a demo - no actual submission occurred)", "success");
  
  // In a real implementation, you would submit the form here
  // For this demo, we'll just show a success message
}