const getCsrfToken = () => document.getElementById("csrf_token").value;
const CONTROLLER_PATH_SWAB = "src/Controllers/SwabController.php";

// DOM Elements
const swabModalOverlay = document.getElementById("swabParametersModal");
const swabPriceForm = document.getElementById("swabPriceForm");
const btnCloseModal = document.getElementById("btnCloseModal");
const btnCancel = document.getElementById("btnCancel");
const btnAddIndividual = document.getElementById("btnAddIndividual");
const btnAddCombo = document.getElementById("btnAddCombo");

const formMode = document.getElementById("formMode");
const recordType = document.getElementById("recordType");
const swabParamId = document.getElementById("swabParamId");
const individualSection = document.getElementById("individualSection");
const comboSection = document.getElementById("comboSection");
const comboParametersSelect = document.getElementById("comboParameters");
const swabPrice = document.getElementById("swabPrice");
const swabStatus = document.getElementById("swabStatus");

const btnFilter = document.getElementById("btnFilter");
const btnReset = document.getElementById("btnReset");
const searchInput = document.getElementById("searchInput");
const statusFilter = document.getElementById("statusFilter");
const tbody = document.querySelector(".swab-parameters-table tbody");
const deleteConfirmModal = document.getElementById("deleteConfirmModal");
const btnCancelDelete = document.getElementById("cancelDelete");
const btnConfirmDelete = document.getElementById("confirmDelete");
const btnCloseDeleteModal = document.getElementById("btnCloseDeleteModal");

let currentFilters = {};
let deleteSwabId = null;
let comboChoices = null;

// === TOAST ===
function showToast(message, type = "success") {
  const colors = {
    success: "bg-success text-white",
    warning: "bg-warning text-dark",
    danger: "bg-danger text-white",
    info: "bg-warning text-dark",
  };

  const toastEl = document.createElement("div");
  toastEl.className = `toast align-items-center ${colors[type]} border-0`;
  toastEl.setAttribute("role", "alert");
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close ${type === "warning" ? "btn-close-black" : "btn-close-white"} me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;

  document.getElementById("toastContainer").appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, {
    delay: 3000,
  });
  toast.show();
  toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

// === AJAX ===
async function sendAjax(action, data = {}) {
  try {
    const formData = new URLSearchParams({
      action,
      csrf_token: getCsrfToken(),
      ...data,
    });
    const response = await fetch(CONTROLLER_PATH_SWAB, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData,
    });

    const result = await response.json();
    console.log(`Action: ${action}`, result); // Debug logging
    return result;
  } catch (error) {
    console.error("AJAX Error:", error);
    return {
      status: "error",
      message: "Network error occurred",
    };
  }
}

// === LOAD PARAMETERS DROPDOWN ===
async function loadParametersDropdown() {
  const result = await sendAjax("fetchDropdown");
  const select = document.getElementById("parameterSelect");

  select.innerHTML = '<option value="">-- Select Parameter --</option>';

  if (result.status === "success") {
    if (result.data && result.data.length > 0) {
      result.data.forEach((p) => {
        const option = document.createElement("option");
        option.value = p.parameter_id;
        option.textContent = `${p.parameter_name} (${p.parameter_code})`;
        select.appendChild(option);
      });
    } else {
      select.innerHTML = '<option value="">No available parameters</option>';
      showToast(
        "All swab-enabled parameters already have pricing configured",
        "warning",
      );
    }
  } else {
    console.error("Failed to load dropdown:", result);
    showToast("Failed to load parameters", "danger");
  }
}

// === LOAD COMBO PARAMETERS DROPDOWN (Choices.js multi-select) ===
async function loadComboParametersDropdown() {
  const result = await sendAjax("fetchComboDropdown");

  if (result.status === "success" && result.data) {
    // Destroy existing Choices instance if exists
    if (comboChoices) {
      comboChoices.destroy();
      comboChoices = null;
    }

    // Reset the raw select element
    const selectEl = document.getElementById("comboParameters");
    selectEl.innerHTML = "";

    // Initialize Choices.js
    comboChoices = new Choices(selectEl, {
      removeItemButton: true,
      searchEnabled: true,
      placeholderValue: "Select parameters",
      shouldSort: false,
      removeItems: true,
      duplicateItemsAllowed: false,
    });

    // Build choices array from the API data
    const choiceOptions = result.data.map((p) => ({
      value: `${p.parameter_id}`,
      label: p.parameter_name,
    }));

    comboChoices.setChoices(choiceOptions, "value", "label", true);
  } else {
    console.error("Failed to load combo parameters:", result);
    showToast("Failed to load parameters for combo", "danger");
  }
}

// === LOAD SWAB PRICES ===
async function loadSwabPrices() {
  tbody.innerHTML =
    '<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

  const result = await sendAjax("fetchAll", currentFilters);

  if (result.status === "success") {
    renderTable(result.data);
  } else {
    tbody.innerHTML =
      '<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>';
  }
}

// === RENDER TABLE ===
function renderTable(data) {
  if (!data || data.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="4" class="text-center text-muted">No swab parameters found</td></tr>';
    return;
  }

  tbody.innerHTML = data
    .map((v) => {
      const isCombo = v.type === "combo";
      const displayName = isCombo ? v.name : v.name;
      const price = parseFloat(v.price).toFixed(2);
      const isActive = parseInt(v.is_active) === 1;
      const typeBadge = isCombo
        ? '<span class="badge bg-warning text-dark ms-1" style="font-size:0.7em;">Combo</span>'
        : "";

      return `
      <tr data-id="${v.id}" data-type="${v.type}" data-name="${displayName}">
        <td data-label="Parameter Name:">${displayName} ${typeBadge}</td>
        <td data-label="Price:">${price}</td>
        <td data-label="Status:">
          <span class="badge-status bg-${isActive ? "success" : "secondary"}">
            ${isActive ? "Active" : "Inactive"}
          </span>
        </td>
        <td data-label="Actions:">
          <button class="btn-swab-parameters-edit" data-id="${v.id}" data-type="${v.type}" title="Edit">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn-swab-parameters-delete" data-id="${v.id}" data-type="${v.type}" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>`;
    })
    .join("");

  attachRowEvents();
}

// === ATTACH EVENTS ===
let deleteRecordType = "individual"; // Track type for delete

function attachRowEvents() {
  document.querySelectorAll(".btn-swab-parameters-edit").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      const type = btn.dataset.type;

      if (type === "combo") {
        const result = await sendAjax("getComboById", { combo_id: id });
        if (result.status === "success") {
          openEditModal(result.data, "combo");
        } else {
          showToast(result.message || "Failed to load combo", "danger");
        }
      } else {
        const result = await sendAjax("getById", { swab_param_id: id });
        if (result.status === "success") {
          openEditModal(result.data, "individual");
        } else {
          showToast(result.message || "Failed to load record", "danger");
        }
      }
    });
  });

  document.querySelectorAll(".btn-swab-parameters-delete").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const row = e.target.closest("tr");
      const name = row.dataset.name;
      const type = row.dataset.type;
      deleteSwabId = btn.dataset.id;
      deleteRecordType = type;
      document.getElementById("deleteParamName").textContent = name;
      deleteConfirmModal.classList.add("active");
    });
  });
}

// === OPEN/CLOSE MODAL ===
async function openCreateModal(type) {
  swabModalOverlay.classList.add("active");
  document.body.style.overflow = "hidden";
  swabPriceForm.reset();

  formMode.value = "create";
  recordType.value = type;
  document.getElementById("swabModalTitle").textContent =
    type === "individual" ? "New Individual Swab Parameter" : "New Swab Combo";
  swabParamId.value = "";
  document.getElementById("btnSave").className = "btn btn-success";
  document.getElementById("btnSave").innerHTML =
    '<i class="fas fa-save"></i> Save';

  // Show/hide sections based on type
  if (type === "individual") {
    individualSection.classList.remove("d-none");
    comboSection.classList.add("d-none");

    // Setup Individual Form
    document.getElementById("parameterSelectRow").style.display = "block";
    document.getElementById("parameterNameRow").style.display = "none";

    await loadParametersDropdown();
  } else {
    individualSection.classList.add("d-none");
    comboSection.classList.remove("d-none");

    // Setup Combo Form
    await loadComboParametersDropdown();
  }

  clearSwabValidations();
}

async function openEditModal(data, type = "individual") {
  swabModalOverlay.classList.add("active");
  document.body.style.overflow = "hidden";
  clearSwabValidations();

  document.getElementById("formMode").value = "edit";
  document.getElementById("recordType").value = type;
  document.getElementById("swabModalTitle").textContent =
    type === "individual"
      ? "Edit Individual Swab Parameter"
      : "Edit Swab Combo";
  document.getElementById("swabParamId").value =
    type === "individual" ? data.swab_param_id : data.combo_id;
  document.getElementById("swabPrice").value = parseFloat(
    data.swab_price || data.price || 0,
  );
  document.getElementById("swabStatus").value = data.is_active;
  document.getElementById("btnSave").className = "btn btn-warning";
  document.getElementById("btnSave").innerHTML =
    '<i class="fas fa-save"></i> Update Swab';

  if (type === "individual") {
    individualSection.classList.remove("d-none");
    comboSection.classList.add("d-none");

    // Hide dropdown, show read-only fields
    document.getElementById("parameterSelectRow").style.display = "none";
    document.getElementById("parameterNameRow").style.display = "block";
    document.getElementById("parameterName").value =
      data.parameter_name || data.name || "";
  } else {
    // Combo Mode Edit
    individualSection.classList.add("d-none");
    comboSection.classList.remove("d-none");

    // Load combo parameters dropdown then set selected values
    await loadComboParametersDropdown();

    // Pre-select the combo's existing parameters
    if (data.param_ids && Array.isArray(data.param_ids) && comboChoices) {
      const stringIds = data.param_ids.map((id) => String(id));
      comboChoices.setChoiceByValue(stringIds);
    }
  }
}

function closeModal() {
  swabModalOverlay.classList.remove("active");
  document.body.style.overflow = "auto";
}

// === VALIDATION ===
function validateSwabInput(inputEl, errorEl) {
  const val = inputEl.value.trim();
  if (val === "") {
    inputEl.classList.add("is-invalid");
    errorEl.textContent = "This field is required.";
    errorEl.style.display = "block";
    return false;
  } else {
    inputEl.classList.remove("is-invalid");
    inputEl.classList.add("is-valid");
    errorEl.style.display = "none";
    return true;
  }
}

document
  .getElementById("parameterSelect")
  .addEventListener("change", function () {
    validateSwabInput(this, document.getElementById("parameterSelectError"));
  });
document.getElementById("swabPrice").addEventListener("input", function () {
  validateSwabInput(this, document.getElementById("swabPriceError"));
});
document.getElementById("swabStatus").addEventListener("change", function () {
  validateSwabInput(this, document.getElementById("swabStatusError"));
});

function clearSwabValidations() {
  ["parameterSelect", "swabPrice", "swabStatus"].forEach((id) => {
    const el = document.getElementById(id);
    const err = document.getElementById(id + "Error");
    if (el) el.classList.remove("is-invalid", "is-valid");
    if (err) err.style.display = "none";
  });
  // Also clear combo validations
  const comboErr = document.getElementById("comboParametersError");
  if (comboErr) comboErr.style.display = "none";
}

// === SAVE (INSERT/UPDATE - Individual & Combo) ===
swabPriceForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const mode = document.getElementById("formMode").value;
  const type = document.getElementById("recordType").value;

  const vPrice = validateSwabInput(
    document.getElementById("swabPrice"),
    document.getElementById("swabPriceError"),
  );
  const vStatus = validateSwabInput(
    document.getElementById("swabStatus"),
    document.getElementById("swabStatusError"),
  );

  if (type === "individual") {
    // Individual validation
    let vParam = true;
    if (mode === "create") {
      vParam = validateSwabInput(
        document.getElementById("parameterSelect"),
        document.getElementById("parameterSelectError"),
      );
    }

    if (!vPrice || !vStatus || !vParam) {
      showToast("Please correct the highlighted errors.", "danger");
      return;
    }

    const data = {
      price: document.getElementById("swabPrice").value.trim(),
      is_active: document.getElementById("swabStatus").value,
    };

    if (mode === "create") {
      data.param_id = document.getElementById("parameterSelect").value;
    } else {
      data.swab_param_id = document.getElementById("swabParamId").value;
    }

    const action = mode === "create" ? "insert" : "update";
    const result = await sendAjax(action, data);

    if (result.status === "success") {
      showToast(result.message, "success");
      closeModal();
      loadSwabPrices();
    } else if (result.status === "info") {
      showToast(result.message || "No update detected.", "info");
    } else {
      showToast(result.message || "Failed to save", "danger");
    }
  } else {
    // Combo validation
    const selectedValues = comboChoices ? comboChoices.getValue(true) : [];
    if (selectedValues.length < 2) {
      const comboErr = document.getElementById("comboParametersError");
      comboErr.textContent = "Please select at least 2 parameters.";
      comboErr.style.display = "block";
      showToast("Please select at least 2 parameters for the combo.", "danger");
      return;
    }

    if (!vPrice || !vStatus) {
      showToast("Please correct the highlighted errors.", "danger");
      return;
    }

    const data = {
      price: document.getElementById("swabPrice").value.trim(),
      is_active: document.getElementById("swabStatus").value,
      param_ids: selectedValues.join(","),
      combo_id:
        mode === "edit" ? document.getElementById("swabParamId").value : "0",
    };

    const result = await sendAjax("saveCombo", data);

    if (result.status === "success") {
      showToast(result.message, "success");
      closeModal();
      loadSwabPrices();
    } else {
      showToast(result.message || "Failed to save combo", "danger");
    }
  }
});

// === DELETE (Individual & Combo) ===
btnConfirmDelete.addEventListener("click", async () => {
  if (!deleteSwabId) return;

  let result;
  if (deleteRecordType === "combo") {
    result = await sendAjax("deleteCombo", {
      combo_id: deleteSwabId,
    });
  } else {
    result = await sendAjax("delete", {
      swab_param_id: deleteSwabId,
    });
  }

  if (result.status === "success") {
    showToast(result.message, "danger");
    deleteConfirmModal.classList.remove("active");
    loadSwabPrices();
  } else {
    showToast(result.message || "Failed to delete", "danger");
  }

  deleteSwabId = null;
  deleteRecordType = "individual";
});

// === FILTER ===
btnFilter.addEventListener("click", () => {
  currentFilters = {
    search: searchInput.value.trim(),
    is_active: statusFilter.value,
  };
  loadSwabPrices();
});

btnReset.addEventListener("click", () => {
  searchInput.value = "";
  statusFilter.value = "";
  currentFilters = {};
  loadSwabPrices();
});

searchInput.addEventListener("keypress", (e) => {
  if (e.key === "Enter") btnFilter.click();
});

// === MODAL CONTROLS ===
btnAddIndividual.addEventListener("click", () => openCreateModal("individual"));
btnAddCombo.addEventListener("click", () => openCreateModal("combo"));

btnCloseModal.addEventListener("click", closeModal);
btnCancel.addEventListener("click", closeModal);
swabModalOverlay.addEventListener("click", (e) => {
  if (e.target === swabModalOverlay) closeModal();
});

btnCancelDelete.addEventListener("click", () =>
  deleteConfirmModal.classList.remove("active"),
);
btnCloseDeleteModal.addEventListener("click", () =>
  deleteConfirmModal.classList.remove("active"),
);
deleteConfirmModal.addEventListener("click", (e) => {
  if (e.target === deleteConfirmModal)
    deleteConfirmModal.classList.remove("active");
});

// === INITIAL LOAD ===
loadSwabPrices();
