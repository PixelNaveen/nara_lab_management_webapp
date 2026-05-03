// ===== CLIENT MANAGEMENT SCRIPT =====

// === DOM ELEMENTS ===
const modalOverlay = document.getElementById("modalOverlay");
const clientForm = document.getElementById("clientForm");
const btnNewClient = document.getElementById("btnNewClient");
const btnCloseModal = document.getElementById("btnCloseModal");
const btnCancel = document.getElementById("btnCancel");
const btnSave = document.getElementById("btnSave");
const btnUpdate = document.getElementById("btnUpdate");
const formTitle = document.getElementById("formTitle");
const deleteModal = document.getElementById("deleteModal");
const toastContainer = document.getElementById("toastContainer");
const cityInput = document.getElementById("city");
const cityAutocomplete = document.getElementById("cityAutocomplete");
const cityWarningDiv = document.getElementById("cityWarning");
let deleteClientId = null;
let originalData = {};
let cityExactMatch = false;

const CONTROLLER_PATH = "src/Controllers/ClientController.php";
const CITY_CONTROLLER_PATH = "src/Controllers/CityController.php";

// === TOAST FUNCTION ===
function showToast(message, type = "success") {
  const colors = {
    success: "bg-success text-white",
    warning: "bg-warning text-dark",
    danger: "bg-danger text-white",
  };
  const icons = {
    success: '<i class="fas fa-check-circle me-2"></i>',
    warning: '<i class="fas fa-exclamation-triangle me-2"></i>',
    danger: '<i class="fas fa-exclamation-circle me-2"></i>',
  };
  const toastEl = document.createElement("div");
  toastEl.className = `toast fade align-items-center ${colors[type] || "bg-success text-white"} border-0 mb-2 shadow-lg`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">
        ${icons[type] || ""}
        ${message}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  toastContainer.appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, {
    delay: 3000,
    autohide: true,
  });
  toast.show();
  toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

async function sendAjax(url, action, data) {
  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action,
        ...data,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const text = await response.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("Invalid JSON response:", text);
      return {
        status: "error",
        message: "Server returned an invalid response. Please check logs.",
      };
    }
  } catch (error) {
    console.error("AJAX Error:", error);
    return {
      status: "error",
      message: `Network error: ${error.message}`,
    };
  }
}

// === DEBOUNCE HELPER ===
function debounce(func, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

// === LOAD CLIENTS ===
function loadClients() {
  sendAjax(CONTROLLER_PATH, "fetchAll", {}).then((res) => {
    const tbody = document.querySelector("#clientsTable tbody");
    tbody.innerHTML = "";

    if (res.status === "success" && Array.isArray(res.data)) {
      res.data.forEach((client) => {
        tbody.insertAdjacentHTML(
          "beforeend",
          `
          <tr data-id="${client.client_id}"
              data-name="${client.client_name}"
              data-address="${client.address_line1}"
              data-city="${client.city}"
              data-phone="${client.phone_primary}"
              data-contact="${client.contact_person || ""}">
            <td class="d-none" data-label="ID">${client.client_id}</td>
            <td data-label="Client Name">${client.client_name}</td>
            <td data-label="Address">${client.address_line1}</td>
            <td data-label="City">${client.city}</td>
            <td data-label="Phone">${client.phone_primary}</td>
            <td data-label="Contact Person">${client.contact_person || ""}</td>
            <td data-label="Reg. Date">${client.registration_date}</td>
            <td data-label="Actions">
              <button class="btn btn-sm btn-warning btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
              <button class="btn btn-sm btn-danger btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        `,
        );
      });
      attachRowEvents();
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No clients found</td></tr>`;
    }
  });
}

// === LIVE VALIDATION ===
/**
 * Granular Validation Helper
 * Checks cascading rules: required -> minLength -> regex
 */
function validateInputGranular(
  inputElement,
  errorDivId,
  fieldLabel,
  options = {},
) {
  const value = inputElement.value.trim();
  const errorDiv = document.getElementById(errorDivId);

  // 1. Check Required
  if (options.required && value === "") {
    setError(inputElement, errorDiv, `${fieldLabel} is required.`);
    return false;
  }

  // 2. Check Min Length
  if (options.minLength && value.length < options.minLength && value !== "") {
    setError(
      inputElement,
      errorDiv,
      `${fieldLabel} must be at least ${options.minLength} characters.`,
    );
    return false;
  }

  // 3. Check Regex Pattern
  if (options.pattern && !options.pattern.test(value) && value !== "") {
    setError(
      inputElement,
      errorDiv,
      options.patternMsg || `${fieldLabel} format is invalid.`,
    );
    return false;
  }

  setSuccess(inputElement, errorDiv);
  return true;
}

function setError(input, errorDiv, msg) {
  input.classList.add("is-invalid");
  input.classList.remove("is-valid");
  errorDiv.textContent = msg;
}

function setSuccess(input, errorDiv) {
  input.classList.remove("is-invalid");
  input.classList.add("is-valid");
  errorDiv.textContent = "";
}

const nameRegex = /^[A-Za-z0-9\s,.\-&\/()\'#]{3,}$/;
const phoneRegex = /^0[0-9]{9}$/;
const addressRegex = /^[a-zA-Z0-9\s,.\-\/#():]{5,}$/;
const cityRegex = /^[a-zA-Z0-9\s\-]{2,}$/;

document.getElementById("clientName").addEventListener("input", function () {
  validateInputGranular(this, "clientNameError", "Client Name", {
    required: true,
    minLength: 3,
    pattern: nameRegex,
    patternMsg: "Name contains invalid characters or is too short.",
  });
});

document.getElementById("contactPerson").addEventListener("input", function () {
  validateInputGranular(this, "contactPersonError", "Contact Person", {
    required: true,
    minLength: 3,
    pattern: nameRegex,
    patternMsg: "Name contains invalid characters or is too short.",
  });
});

document.getElementById("phonePrimary").addEventListener("input", function () {
  validateInputGranular(this, "phoneError", "Phone number", {
    required: true,
    pattern: phoneRegex,
    patternMsg: "Phone number must be exactly 10 digits starting with 0.",
  });
});

document.getElementById("addressLine1").addEventListener("input", function () {
  validateInputGranular(this, "addressError", "Address", {
    required: true,
    minLength: 5,
    pattern: addressRegex,
    patternMsg: "Address contains invalid characters.",
  });
});

// === CITY AUTOCOMPLETE LOGIC ===
cityInput.addEventListener(
  "input",
  debounce(async function () {
    // Real-time filtering: Allow letters, numbers, spaces, and hyphens
    this.value = this.value.replace(/[^a-zA-Z0-9\s\-]/g, "");

    const query = this.value.trim();

    // Base validation for empty check and regex
    const isValid = validateInputGranular(this, "cityError", "City", {
      required: true,
      minLength: 2,
      pattern: cityRegex,
      patternMsg:
        "City must contain only letters, numbers, spaces, or hyphens.",
    });

    if (query.length < 2) {
      cityAutocomplete.classList.remove("show");
      cityWarningDiv.style.display = "none";
      cityExactMatch = false;
      return;
    }

    cityAutocomplete.innerHTML =
      '<div class="city-autocomplete-loading">Searching...</div>';
    cityAutocomplete.classList.add("show");

    try {
      const res = await sendAjax(CITY_CONTROLLER_PATH, "fetchAll", {
        search: query,
        is_active: 1,
        csrf_token: document.getElementById("csrfToken").value,
      });
      cityExactMatch = false;

      if (res.status === "success" && res.data && res.data.length > 0) {
        let html = "";
        res.data.forEach((city) => {
          if (city.city_name.toLowerCase() === query.toLowerCase()) {
            cityExactMatch = true;
          }
          html += `
            <div class="city-autocomplete-item" 
                 data-city-id="${city.city_id}" 
                 data-city-name="${city.city_name}">
              ${city.city_name}
            </div>
          `;
        });
        cityAutocomplete.innerHTML = html;

        cityAutocomplete
          .querySelectorAll(".city-autocomplete-item")
          .forEach((item) => {
            item.addEventListener("click", function () {
              cityInput.value = this.getAttribute("data-city-name");
              cityAutocomplete.classList.remove("show");
              validateInputGranular(cityInput, "cityError", "City", {
                required: true,
              });
              cityWarningDiv.style.display = "none";
              cityExactMatch = true;
            });
          });
      } else {
        cityAutocomplete.innerHTML = `<div class="city-autocomplete-empty">No matching cities found</div>`;
      }

      if (!cityExactMatch && query.length > 0) {
        cityWarningDiv.style.display = "block";
      } else {
        cityWarningDiv.style.display = "none";
      }
    } catch (error) {
      console.error("City search error:", error);
      cityAutocomplete.innerHTML = `<div class="city-autocomplete-empty text-danger">Error searching</div>`;
    }
  }, 400),
);

// Close dropdown on click outside
document.addEventListener("click", function (e) {
  if (!e.target.closest("#city") && !e.target.closest("#cityAutocomplete")) {
    cityAutocomplete.classList.remove("show");
  }
});

function validateAll() {
  const isNameValid = validateInputGranular(
    document.getElementById("clientName"),
    "clientNameError",
    "Client Name",
    {
      required: true,
      minLength: 3,
      pattern: nameRegex,
      patternMsg: "Name contains invalid characters or is too short.",
    },
  );
  const isContactValid = validateInputGranular(
    document.getElementById("contactPerson"),
    "contactPersonError",
    "Contact Person",
    {
      required: true,
      minLength: 3,
      pattern: nameRegex,
      patternMsg: "Name contains invalid characters or is too short.",
    },
  );
  const isPhoneValid = validateInputGranular(
    document.getElementById("phonePrimary"),
    "phoneError",
    "Phone number",
    {
      required: true,
      pattern: phoneRegex,
      patternMsg: "Phone number must be exactly 10 digits starting with 0.",
    },
  );
  const isAddressValid = validateInputGranular(
    document.getElementById("addressLine1"),
    "addressError",
    "Address",
    {
      required: true,
      minLength: 5,
      pattern: addressRegex,
      patternMsg: "Address contains invalid characters.",
    },
  );
  const isCityValid = validateInputGranular(
    document.getElementById("city"),
    "cityError",
    "City",
    {
      required: true,
      minLength: 2,
      pattern: cityRegex,
      patternMsg:
        "City must contain only letters, numbers, spaces, or hyphens.",
    },
  );

  return (
    isNameValid &&
    isContactValid &&
    isPhoneValid &&
    isAddressValid &&
    isCityValid
  );
}

// === MODAL CONTROL ===
function openModal(mode) {
  modalOverlay.classList.add("active");
  document.body.style.overflow = "hidden";

  // Reset validation styles
  const inputs = [
    "clientName",
    "contactPerson",
    "addressLine1",
    "city",
    "phonePrimary",
  ];
  inputs.forEach((id) => {
    const el = document.getElementById(id);
    el.classList.remove("is-invalid", "is-valid");
  });
  document.getElementById("cityWarning").style.display = "none";

  if (mode === "create") {
    clientForm.reset();
    document.getElementById("clientId").value = "";
    btnSave.classList.remove("d-none");
    btnUpdate.classList.add("d-none");
    formTitle.textContent = "Create New Client";
  } else {
    btnSave.classList.add("d-none");
    btnUpdate.classList.remove("d-none");
    formTitle.textContent = "Update Client";
  }
}

function closeModal() {
  modalOverlay.classList.remove("active");
  document.body.style.overflow = "auto";
  clientForm.reset();
  originalData = {};
}

btnNewClient.onclick = () => openModal("create");
btnCloseModal.onclick = closeModal;
btnCancel.onclick = closeModal;
modalOverlay.onclick = (e) => {
  if (e.target === modalOverlay) closeModal();
};

// === INSERT CLIENT ===
clientForm.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!validateAll()) {
    showToast("Please fix the highlighted errors before saving.", "danger");
    return;
  }

  const data = {
    client_name: clientForm.clientName.value.trim(),
    address_line1: clientForm.address.value.trim(),
    city: clientForm.city.value.trim(),
    phone_primary: clientForm.phoneNo.value.trim(),
    contact_person: clientForm.contactPerson.value.trim(),
    csrf_token: document.getElementById("csrfToken").value,
  };

  sendAjax(CONTROLLER_PATH, "insert", data).then((res) => {
    if (res.status === "success") {
      showToast(res.message || "Client created successfully!", "success");
      loadClients();
      closeModal();
    } else {
      showToast(res.message || "Failed to create client", "danger");
    }
  });
});

// === ATTACH EDIT & DELETE EVENTS ===
function attachRowEvents() {
  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.onclick = (e) => {
      const row = e.target.closest("tr");
      openModal("edit");
      document.getElementById("clientId").value = row.dataset.id;
      clientForm.clientName.value = row.dataset.name;
      clientForm.address.value = row.dataset.address;

      // Handle city setting
      clientForm.city.value = row.dataset.city;

      clientForm.phoneNo.value = row.dataset.phone;
      clientForm.contactPerson.value = row.dataset.contact;

      originalData = {
        client_name: row.dataset.name,
        address_line1: row.dataset.address,
        city: row.dataset.city,
        phone_primary: row.dataset.phone,
        contact_person: row.dataset.contact,
      };
    };
  });

  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.onclick = (e) => {
      const row = e.target.closest("tr");
      deleteClientId = row.dataset.id;
      document.getElementById("deleteClientName").textContent =
        row.dataset.name;
      new bootstrap.Modal(deleteModal).show();
    };
  });
}

// === DELETE CLIENT ===
document.getElementById("confirmDeleteBtn").onclick = () => {
  if (!deleteClientId) return;
  sendAjax(CONTROLLER_PATH, "delete", {
    client_id: deleteClientId,
    csrf_token: document.getElementById("csrfToken").value,
  }).then((res) => {
    if (res.status === "success") {
      showToast("Client deleted successfully!", "success");
      loadClients();
    } else {
      showToast(res.message || "Failed to delete client", "danger");
    }
    const modal = bootstrap.Modal.getInstance(deleteModal);
    modal.hide();
    deleteClientId = null;
  });
};

// === UPDATE CLIENT ===
btnUpdate.onclick = () => {
  if (!validateAll()) {
    showToast("Please fix the highlighted errors before saving.", "danger");
    return;
  }

  const id = document.getElementById("clientId").value;
  const data = {
    client_id: id,
    client_name: clientForm.clientName.value.trim(),
    address_line1: clientForm.address.value.trim(),
    city: clientForm.city.value.trim(),
    phone_primary: clientForm.phoneNo.value.trim(),
    contact_person: clientForm.contactPerson.value.trim(),
    csrf_token: document.getElementById("csrfToken").value,
  };

  const changed = Object.keys(originalData).some(
    (key) => data[key] !== originalData[key],
  );
  if (!changed) {
    showToast("No changes detected", "warning");
    return;
  }

  sendAjax(CONTROLLER_PATH, "update", data).then((res) => {
    if (res.status === "success") {
      showToast("Client updated successfully!", "success");
      loadClients();
      closeModal();
    } else {
      showToast(res.message || "Update failed", "danger");
    }
  });
};

// === SEARCH FILTER ===
document.getElementById("searchInput").addEventListener("input", (e) => {
  const search = e.target.value.toLowerCase();
  const rows = document.querySelectorAll("#clientsTable tbody tr");
  let visibleCount = 0;

  rows.forEach((tr) => {
    const combined =
      `${tr.dataset.name} ${tr.dataset.city} ${tr.dataset.phone}`.toLowerCase();
    if (combined.includes(search)) {
      tr.style.display = "";
      visibleCount++;
    } else {
      tr.style.display = "none";
    }
  });

  const noResultsRow = document.querySelector(
    "#clientsTable tbody tr.no-results",
  );
  if (visibleCount === 0) {
    if (!noResultsRow) {
      document
        .querySelector("#clientsTable tbody")
        .insertAdjacentHTML(
          "beforeend",
          `<tr class="no-results"><td colspan="8" class="text-center text-muted">No matching clients found</td></tr>`,
        );
    }
  } else if (noResultsRow) {
    noResultsRow.remove();
  }
});

// === INITIAL LOAD ===
loadClients();


