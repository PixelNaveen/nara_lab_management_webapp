// ===== TEST METHOD MANAGEMENT SCRIPT =====

// === DOM ELEMENTS ===
const modalOverlay = document.getElementById("modalOverlay");
const testMethodForm = document.getElementById("testMethodForm");
const btnNewTestMethod = document.getElementById("btnNewTestMethod");
const btnCloseModal = document.getElementById("btnCloseModal");
const btnCancel = document.getElementById("btnCancel");
const btnSave = document.getElementById("btnSave");
const btnUpdate = document.getElementById("btnUpdate");
const formTitle = document.getElementById("formTitle");
const deleteModal = document.getElementById("deleteModal");
const toastContainer = document.getElementById("toastContainer");
const searchInput = document.getElementById("searchInput");
const standardBodyFilter = document.getElementById("standardBodyFilter");
const statusFilter = document.getElementById("statusFilter");
const btnFilter = document.getElementById("btnFilter");
let deleteTestMethodId = null;
let originalData = {};

const getCsrfToken = () => document.getElementById("csrf_token").value;
const CONTROLLER_PATH = "src/Controllers/TestMethodController.php";

// === TOAST FUNCTION ===
function showToast(message, type = "success") {
  const colors = {
    success: "bg-success text-white",
    warning: "bg-warning text-dark",
    danger: "bg-danger text-white",
    info: "bg-warning text-dark",
  };
  const toastEl = document.createElement("div");
  toastEl.className = `toast align-items-center ${colors[type] || "bg-success text-white"} border-0 mb-2`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  toastContainer.appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, {
    delay: 2500,
  });
  toast.show();
  toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

// === AJAX HELPER ===
function sendAjax(action, data) {
  return fetch(CONTROLLER_PATH, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action,
      csrf_token: getCsrfToken(),
      ...data,
    }),
  })
    .then((res) => res.json())
    .catch(() => ({
      status: "error",
      message: "Network error!",
    }));
}

// === LOAD TEST METHODS ===
function loadTestMethods() {
  sendAjax("fetchAll", {}).then((res) => {
    const tbody = document.querySelector("#testMethodsTable tbody");
    tbody.innerHTML = "";

    if (res.status === "success" && Array.isArray(res.data)) {
      res.data.forEach((testMethod) => {
        const statusBadge =
          testMethod.status === "active"
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        tbody.insertAdjacentHTML(
          "beforeend",
          `
          <tr data-id="${testMethod.method_id}"
              data-name="${testMethod.method_name}"
              data-standard-body="${testMethod.standard_body}"
              data-status="${testMethod.status}">
              <td class="d-none">${testMethod.method_id}</td>
              <td data-label="Method Name:">${testMethod.method_name || '<em class="text-muted">--</em>'}</td>
              <td data-label="Standard Body:">${testMethod.standard_body}</td>
              <td data-label="Status:">${statusBadge}</td>
                <td data-label="Actions:">
                <button class="btn btn-sm btn-warning btn-edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
  `,
        );
      });

      attachRowEvents();
      filterTable(); // Apply filters after loading
    } else {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No test methods found</td></tr>`;
    }
  });
}

// === MODAL CONTROL ===
function openModal(mode) {
  modalOverlay.classList.add("active");
  document.body.style.overflow = "hidden";

  if (mode === "create") {
    testMethodForm.reset();
    document.getElementById("testMethodId").value = "";
    btnSave.classList.remove("d-none");
    btnUpdate.classList.add("d-none");
    formTitle.textContent = "Create New Test Method";
  } else {
    btnSave.classList.add("d-none");
    btnUpdate.classList.remove("d-none");
    formTitle.textContent = "Update Test Method";
  }
  clearValidations();
}

function clearValidations() {
  ["methodName", "standardBody", "status"].forEach((id) => {
    const el = document.getElementById(id);
    const err = document.getElementById(id + "Error");
    if (el) el.classList.remove("is-invalid", "is-valid");
    if (err) err.style.display = "none";
  });
}

function validateInput(inputEl, errorEl) {
  if (inputEl.value.trim() === "") {
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

document.getElementById("methodName").addEventListener("input", function () {
  validateInput(this, document.getElementById("methodNameError"));
});
document.getElementById("standardBody").addEventListener("change", function () {
  validateInput(this, document.getElementById("standardBodyError"));
});
document.getElementById("status").addEventListener("change", function () {
  validateInput(this, document.getElementById("statusError"));
});

function closeModal() {
  modalOverlay.classList.remove("active");
  document.body.style.overflow = "auto";
  testMethodForm.reset();
  originalData = {};
}

btnNewTestMethod.onclick = () => openModal("create");
btnCloseModal.onclick = closeModal;
btnCancel.onclick = closeModal;
modalOverlay.onclick = (e) => {
  if (e.target === modalOverlay) closeModal();
};

// === INSERT TEST METHOD ===
testMethodForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const vName = validateInput(
    testMethodForm.methodName,
    document.getElementById("methodNameError"),
  );
  const vBody = validateInput(
    testMethodForm.standardBody,
    document.getElementById("standardBodyError"),
  );
  const vStat = validateInput(
    testMethodForm.status,
    document.getElementById("statusError"),
  );

  if (!vName || !vBody || !vStat) {
    showToast("Please correct the highlighted errors.", "danger");
    return;
  }

  const data = {
    method_name: testMethodForm.methodName.value.trim(),
    standard_body: testMethodForm.standardBody.value,
    status: testMethodForm.status.value,
  };

  sendAjax("insert", data).then((res) => {
    if (res.status === "success") {
      showToast(res.message || "Test method created successfully!", "success");
      loadTestMethods();
      closeModal();
    } else {
      showToast(res.message || "Failed to create test method", "danger");
    }
  });
});

// === ATTACH EDIT & DELETE EVENTS ===
function attachRowEvents() {
  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.onclick = (e) => {
      const row = e.target.closest("tr");
      openModal("edit");
      document.getElementById("testMethodId").value = row.dataset.id;
      testMethodForm.methodName.value = row.dataset.name;
      testMethodForm.standardBody.value = row.dataset.standardBody;
      testMethodForm.status.value = row.dataset.status;

      originalData = {
        method_name: row.dataset.name,
        standard_body: row.dataset.standardBody,
        status: row.dataset.status,
      };
    };
  });

  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.onclick = (e) => {
      const row = e.target.closest("tr");
      deleteTestMethodId = row.dataset.id;
      document.getElementById("deleteTestMethodName").textContent =
        row.dataset.name;
      new bootstrap.Modal(deleteModal).show();
    };
  });
}

// === DELETE TEST METHOD ===
document.getElementById("confirmDeleteBtn").onclick = () => {
  if (!deleteTestMethodId) return;
  sendAjax("delete", {
    method_id: deleteTestMethodId,
  }).then((res) => {
    if (res.status === "success") {
      showToast("Test method deleted successfully!", "danger");
      loadTestMethods();
    } else {
      showToast(res.message || "Failed to delete test method", "danger");
    }
    const modal = bootstrap.Modal.getInstance(deleteModal);
    modal.hide();
    deleteTestMethodId = null;
  });
};

// === UPDATE TEST METHOD ===
btnUpdate.onclick = () => {
  const vName = validateInput(
    testMethodForm.methodName,
    document.getElementById("methodNameError"),
  );
  const vBody = validateInput(
    testMethodForm.standardBody,
    document.getElementById("standardBodyError"),
  );
  const vStat = validateInput(
    testMethodForm.status,
    document.getElementById("statusError"),
  );

  if (!vName || !vBody || !vStat) {
    showToast("Please correct the highlighted errors.", "danger");
    return;
  }

  const id = document.getElementById("testMethodId").value;
  const data = {
    method_id: id,
    method_name: testMethodForm.methodName.value.trim(),
    standard_body: testMethodForm.standardBody.value,
    status: testMethodForm.status.value,
  };

  sendAjax("update", data).then((res) => {
    if (res.status === "success") {
      showToast("Test method updated successfully!", "success");
      loadTestMethods();
      closeModal();
    } else if (res.status === "info") {
      showToast(res.message || "No update detected.", "info");
    } else {
      showToast(res.message || "Update failed", "danger");
    }
  });
};

// === FILTER TABLE (INCLUDES SEARCH, STANDARD BODY, STATUS) ===
function filterTable() {
  const search = searchInput.value.toLowerCase();
  const standard = standardBodyFilter.value;
  const stat = statusFilter.value;
  const rows = document.querySelectorAll("#testMethodsTable tbody tr");
  let visibleCount = 0;

  rows.forEach((tr) => {
    if (tr.classList.contains("no-results")) return;
    const combined =
      `${tr.dataset.name} ${tr.dataset.standardBody} ${tr.dataset.status}`.toLowerCase();
    let match = true;

    if (search && !combined.includes(search)) match = false;
    if (
      standard !== "All Standard Bodies" &&
      tr.dataset.standardBody !== standard
    )
      match = false;
    if (stat !== "All Status" && tr.dataset.status !== stat.toLowerCase())
      match = false;

    tr.style.display = match ? "" : "none";
    if (match) visibleCount++;
  });

  let noResultsRow = document.querySelector(
    "#testMethodsTable tbody tr.no-results",
  );
  if (visibleCount === 0) {
    if (!noResultsRow) {
      document
        .querySelector("#testMethodsTable tbody")
        .insertAdjacentHTML(
          "beforeend",
          `<tr class="no-results"><td colspan="5" class="text-center text-muted">No matching test methods found</td></tr>`,
        );
    }
  } else if (noResultsRow) {
    noResultsRow.remove();
  }
}

// Attach events for live search and filter button
searchInput.addEventListener("input", filterTable);
standardBodyFilter.addEventListener("change", filterTable);
statusFilter.addEventListener("change", filterTable);
btnFilter.addEventListener("click", filterTable);

// === INITIAL LOAD ===
loadTestMethods();
