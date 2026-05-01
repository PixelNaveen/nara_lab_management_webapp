(() => {
  const getCsrfToken = () => document.getElementById("csrf_token").value;
  const API_URL = "src/Controllers/SignatoryController.php";
  let allSignatories = [];
  let deleteSignatoryId = null;

  // ========================================
  // DOM REFERENCES
  // ========================================
  const modal = document.getElementById("sigModal");
  const form = document.getElementById("signatoryForm");
  const deleteModal = document.getElementById("sigDeleteModal");

  // ========================================
  // INIT
  // ========================================
  loadSignatories();

  // ========================================
  // TOAST HELPER
  // ========================================
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
    document.getElementById("sigToastContainer").appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
  }

  // ========================================
  // LOAD SIGNATORIES
  // ========================================
  function loadSignatories() {
    const fd = new FormData();
    fd.append("csrf_token", getCsrfToken());
    fd.append("action", "fetchAll");

    fetch(API_URL, { method: "POST", body: fd })
      .then((r) => r.json())
      .then((res) => {
        if (res.status !== "success") return;
        allSignatories = res.data || [];
        updateStats(allSignatories);
        renderTable(allSignatories);
      });
  }

  function updateStats(data) {
    document.getElementById("statTotal").textContent = data.length;
    document.getElementById("statScientists").textContent = data.filter(
      (s) => s.role_type === "scientist",
    ).length;
    document.getElementById("statHeads").textContent = data.filter(
      (s) => s.role_type === "head",
    ).length;
  }

  function renderTable(data) {
    const tbody = document.getElementById("signatoryTableBody");

    if (!data || data.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="text-center text-muted py-4">No signatories found</td></tr>';
      return;
    }

    tbody.innerHTML = data
      .map((s) => {
        const roleBadge =
          s.role_type === "scientist"
            ? '<span class="badge bg-success-subtle text-success border border-success"><i class="fas fa-microscope me-1"></i> Scientist</span>'
            : '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fas fa-user-tie me-1"></i> Head</span>';

        const defaultBadge =
          s.is_default == 1
            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Yes</span>'
            : '<span class="badge bg-secondary">No</span>';

        const displayName = s.full_name;
        const shortLabel = s.title
          ? `<br><small class="text-muted">${escHtml(s.title)}</small>`
          : "";

        return `<tr>
        <td data-label="Full Name:" class="param-name-cell">${escHtml(displayName)}</td>
        <td data-label="Job Title:">${escHtml(s.title)}</td>
        <td data-label="Division:">${escHtml(s.division)}</td>
        <td data-label="Role:" class="text-center">${roleBadge}</td>
        <td data-label="Default:" class="text-center">${defaultBadge}</td>
        <td data-label="Actions:" class="text-center">
          <button class="btn-signatories-edit" onclick="editSignatory(${s.signatory_id})"><i class="fas fa-edit"></i></button>
          <button class="btn-signatories-delete" onclick="deleteSignatory(${s.signatory_id}, '${escHtml(s.full_name)}')"><i class="fas fa-trash"></i></button>
        </td>
      </tr>`;
      })
      .join("");
  }

  // ========================================
  // FILTER & SEARCH
  // ========================================
  document
    .getElementById("sigSearchInput")
    .addEventListener("input", applyFilters);
  document
    .getElementById("btnSigFilter")
    .addEventListener("click", applyFilters);
  document
    .getElementById("sigRoleFilter")
    .addEventListener("change", applyFilters);
  document.getElementById("btnSigReset").addEventListener("click", () => {
    document.getElementById("sigSearchInput").value = "";
    document.getElementById("sigRoleFilter").value = "";
    renderTable(allSignatories);
  });

  function applyFilters() {
    const search = document
      .getElementById("sigSearchInput")
      .value.trim()
      .toLowerCase();
    const role = document.getElementById("sigRoleFilter").value;
    let filtered = allSignatories;

    if (search) {
      filtered = filtered.filter(
        (s) =>
          s.full_name.toLowerCase().includes(search) ||
          (s.title && s.title.toLowerCase().includes(search)),
      );
    }
    if (role) {
      filtered = filtered.filter((s) => s.role_type === role);
    }
    renderTable(filtered);
  }

  // ========================================
  // MODAL CONTROL
  // ========================================
  document.getElementById("btnNewSignatory").addEventListener("click", () => {
    form.reset();
    document.getElementById("sigEditId").value = "";
    document.getElementById("sigModalTitle").innerHTML =
      '<i class="fas fa-user-plus"></i> Add Signatory';
    clearValidation();
    modal.classList.add("active");
  });

  document
    .getElementById("btnCloseSigModal")
    .addEventListener("click", closeModal);
  document.getElementById("btnCancelSig").addEventListener("click", closeModal);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  function closeModal() {
    modal.classList.remove("active");
    clearValidation();
  }

  // ========================================
  // VALIDATION
  // ========================================
  const nameInput = document.getElementById("sigFullName");
  const titleInput = document.getElementById("sigTitle");
  const divisionInput = document.getElementById("sigDivision");
  const nameRegex = /^[a-zA-Z\s.\-]+$/;

  nameInput.addEventListener("input", () =>
    validateField(nameInput, "sigFullNameError", {
      required: true,
      minLength: 3,
      regex: nameRegex,
      regexMsg: "Only letters, dots, spaces, and hyphens allowed.",
    }),
  );
  titleInput.addEventListener("change", () =>
    validateField(titleInput, "sigTitleError", { required: true }),
  );
  divisionInput.addEventListener("change", () =>
    validateField(divisionInput, "sigDivisionError", { required: true }),
  );

  function validateField(inputEl, errorElId, rules) {
    const val = inputEl.value.trim();
    const errorEl = document.getElementById(errorElId);
    let isValid = true;
    let msg = "";

    if (rules.required && val === "") {
      isValid = false;
      msg = "This field is required.";
    } else if (rules.minLength && val.length < rules.minLength) {
      isValid = false;
      msg = `Must be at least ${rules.minLength} characters.`;
    } else if (rules.regex && val !== "" && !rules.regex.test(val)) {
      isValid = false;
      msg = rules.regexMsg || "Invalid format.";
    }

    if (!isValid) {
      inputEl.classList.add("is-invalid");
      errorEl.textContent = msg;
      errorEl.style.display = "block";
    } else {
      inputEl.classList.remove("is-invalid");
      if (val.length > 0) inputEl.classList.add("is-valid");
      errorEl.textContent = "";
      errorEl.style.display = "none";
    }
    return isValid;
  }

  function clearValidation() {
    document
      .querySelectorAll(".is-invalid, .is-valid")
      .forEach((el) => el.classList.remove("is-invalid", "is-valid"));
    document.querySelectorAll(".invalid-feedback").forEach((el) => {
      el.textContent = "";
      el.style.display = "none";
    });
  }

  // ========================================
  // SAVE (CREATE / UPDATE)
  // ========================================
  document.getElementById("btnSaveSig").addEventListener("click", () => {
    const vName = validateField(nameInput, "sigFullNameError", {
      required: true,
      minLength: 3,
      regex: nameRegex,
      regexMsg: "Only letters, dots, spaces, and hyphens allowed.",
    });
    const vTitle = validateField(titleInput, "sigTitleError", {
      required: true,
    });
    const vDiv = validateField(divisionInput, "sigDivisionError", {
      required: true,
    });

    if (!vName || !vTitle || !vDiv) {
      showToast("Please correct the highlighted errors.", "warning");
      return;
    }

    const editId = document.getElementById("sigEditId").value;
    const isEdit = editId && editId !== "";

    const fd = new FormData();
    fd.append("csrf_token", getCsrfToken());
    fd.append("action", isEdit ? "update" : "create");
    if (isEdit) fd.append("signatory_id", editId);
    fd.append("full_name", nameInput.value.trim());
    fd.append("title", titleInput.value);
    fd.append("division", divisionInput.value);
    fd.append("role_type", document.getElementById("sigRoleType").value);
    fd.append("display_order", 0);
    fd.append(
      "is_default",
      document.getElementById("sigIsDefault").checked ? 1 : 0,
    );

    const btn = document.getElementById("btnSaveSig");
    btn.disabled = true;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(API_URL, { method: "POST", body: fd })
      .then((r) => r.json())
      .then((res) => {
        if (res.status === "success") {
          showToast(res.message || "Saved successfully!", "success");
          closeModal();
          loadSignatories();
        } else if (res.code === "DUPLICATE") {
          showToast(res.message, "warning");
          nameInput.classList.add("is-invalid");
          nameInput.classList.remove("is-valid");
          const errEl = document.getElementById("sigFullNameError");
          errEl.textContent = res.message;
          errEl.style.display = "block";
        } else if (res.code === "NO_CHANGES") {
          showToast(res.message, "warning");
          closeModal();
        } else {
          showToast(res.message || "Save failed", "danger");
        }
      })
      .catch(() => showToast("Network error!", "danger"))
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = isEdit
          ? '<i class="fas fa-save"></i> Update Signatory'
          : '<i class="fas fa-save"></i> Save Signatory';
      });
  });

  // ========================================
  // EDIT SIGNATORY
  // ========================================
  window.editSignatory = function (id) {
    const sig = allSignatories.find((s) => s.signatory_id == id);
    if (!sig) return;

    clearValidation();
    document.getElementById("sigEditId").value = sig.signatory_id;
    document.getElementById("sigFullName").value = sig.full_name;

    // Case-insensitive match for dropdowns
    setSelectCaseInsensitive("sigTitle", sig.title);
    setSelectCaseInsensitive("sigDivision", sig.division);

    document.getElementById("sigRoleType").value = sig.role_type;
    document.getElementById("sigIsDefault").checked = sig.is_default == 1;
    document.getElementById("sigModalTitle").innerHTML =
      '<i class="fas fa-edit"></i> Edit Signatory';

    const saveBtn = document.getElementById("btnSaveSig");
    saveBtn.className = "btn btn-warning";
    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Signatory';

    modal.classList.add("active");
    clearValidation();
  };

  function setSelectCaseInsensitive(selectId, value) {
    const sel = document.getElementById(selectId);
    const match = Array.from(sel.options).find(
      (opt) => opt.value.toLowerCase() === (value || "").toLowerCase(),
    );
    sel.value = match ? match.value : "";
  }

  // ========================================
  // DELETE SIGNATORY
  // ========================================
  window.deleteSignatory = function (id, name) {
    deleteSignatoryId = id;
    document.getElementById("deleteSignatoryName").textContent = name;
    new bootstrap.Modal(deleteModal).show();
  };

  document
    .getElementById("btnConfirmSigDelete")
    .addEventListener("click", () => {
      if (!deleteSignatoryId) return;

      const fd = new FormData();
      fd.append("csrf_token", getCsrfToken());
      fd.append("action", "delete");
      fd.append("signatory_id", deleteSignatoryId);

      fetch(API_URL, { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          if (res.status === "success") {
            showToast("Signatory deleted!", "success");
            loadSignatories();
          } else {
            showToast(res.message || "Delete failed", "danger");
          }
          const m = bootstrap.Modal.getInstance(deleteModal);
          m.hide();
          deleteSignatoryId = null;
        });
    });

  // ========================================
  // ESCAPE HTML
  // ========================================
  function escHtml(str) {
    if (!str) return "";
    const d = document.createElement("div");
    d.textContent = str;
    return d.innerHTML;
  }
})();
