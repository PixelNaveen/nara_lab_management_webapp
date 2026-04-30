/**
 * Client Reports (CRM) - JavaScript Controller v2.0
 * Laboratory Management System
 *
 * - Loads all active clients into the selector on init
 * - Fetches selected client profile, history, finances, and trends
 * - Populates the full dashboard
 * - Handles tab switching
 *
 * @version 2.0
 */

const ClientReport = (() => {
  "use strict";

  const URL = "../../src/Controllers/ClientReportController.php";

  // References
  const selectEl = document.getElementById("crmClientSelect"); // Now a hidden input
  const clientInput = document.getElementById("crmClientInput");
  const clientDropdown = document.getElementById("crmClientDropdown");
  const btnLoad = document.getElementById("crmBtnLoad");
  const emptyEl = document.getElementById("crmEmptyState");
  const dashboard = document.getElementById("crmDashboard");

  let clientList = []; // Store fetched clients

  // ==================== INIT ====================

  function init() {
    loadClients();

    // Dropdown events
    if (clientInput) {
      clientInput.addEventListener("focus", () => {
        if (!clientInput.disabled) {
          renderDropdown(clientList);
          clientDropdown.classList.add("show");
        }
      });

      clientInput.addEventListener("input", (e) => {
        const val = e.target.value.toLowerCase().trim();
        if (!val) {
          if (selectEl) selectEl.value = "";
          if (btnLoad) btnLoad.disabled = true;
          renderDropdown(clientList);
        } else {
          const filtered = clientList.filter((c) => {
            const searchStr = (
              (c.client_name || "") +
              " " +
              (c.city || "")
            ).toLowerCase();
            return searchStr.includes(val);
          });
          renderDropdown(filtered);
        }
        clientDropdown.classList.add("show");
      });

      // Close dropdown if clicked outside
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".crm-client-wrapper")) {
          if (clientDropdown) clientDropdown.classList.remove("show");
        }
      });
    }

    if (btnLoad) {
      btnLoad.addEventListener("click", () => {
        const id = selectEl ? selectEl.value : "";
        if (id) loadClientData(id);
      });
    }

    // Tab switching
    document.querySelectorAll(".crm-tab-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        document
          .querySelectorAll(".crm-tab-btn")
          .forEach((b) => b.classList.remove("active"));
        document
          .querySelectorAll(".crm-tab-content")
          .forEach((p) => p.classList.remove("active"));
        e.currentTarget.classList.add("active");
        const target = document.getElementById(e.currentTarget.dataset.target);
        if (target) target.classList.add("active");
      });
    });
  }

  // ==================== LOAD CLIENTS LIST ====================

  function loadClients() {
    fetch(`${URL}?action=getClients`)
      .then((r) => r.json())
      .then((resp) => {
        if (resp.status === "success") {
          populateSelect(resp.data);
        } else {
          if (clientInput) {
            clientInput.placeholder = "Error loading clients";
            clientInput.disabled = true;
          }
          console.error("getClients error:", resp.message);
        }
      })
      .catch((err) => {
        if (clientInput) {
          clientInput.placeholder = "Network error fetching clients";
          clientInput.disabled = true;
        }
        console.error("Fetch error (getClients):", err);
      });
  }

  function populateSelect(clients) {
    if (!clientInput) return;

    clientList = clients || [];

    if (clientList.length === 0) {
      clientInput.placeholder = "No active clients found";
      clientInput.disabled = true;
      return;
    }

    clientInput.placeholder = "Type to search client...";
    clientInput.disabled = false;
    renderDropdown(clientList);
  }

  function renderDropdown(list) {
    if (!clientDropdown) return;
    clientDropdown.innerHTML = "";

    if (list.length === 0) {
      clientDropdown.innerHTML =
        '<div class="crm-client-dropdown-item" style="cursor:default;"><small>No matches found</small></div>';
      return;
    }

    list.forEach((c) => {
      const div = document.createElement("div");
      div.className = "crm-client-dropdown-item";
      const name = c.client_name || "";
      const city = c.city ? `(${c.city})` : "";
      div.innerHTML = `${esc(name)} <small>${esc(city)}</small>`;

      div.addEventListener("click", () => {
        selectClient(c);
      });
      clientDropdown.appendChild(div);
    });
  }

  function selectClient(c) {
    if (clientInput) {
      clientInput.value = c.client_name + (c.city ? ` (${c.city})` : "");
    }
    if (selectEl) {
      selectEl.value = c.client_id;
    }
    if (btnLoad) {
      btnLoad.disabled = false;
    }
    if (clientDropdown) {
      clientDropdown.classList.remove("show");
    }
  }

  // ==================== LOAD CLIENT DATA ====================

  function loadClientData(clientId) {
    const origHtml = btnLoad.innerHTML;
    btnLoad.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
    btnLoad.disabled = true;

    const fd = new FormData();
    fd.append("action", "getClientData");
    fd.append("client_id", clientId);

    fetch(URL, { method: "POST", body: fd })
      .then((r) => r.json())
      .then((resp) => {
        if (resp.status === "success") {
          renderDashboard(resp.data);
        } else {
          alert("Error: " + resp.message);
        }
      })
      .catch((err) => {
        alert("A network error occurred while loading client data.");
        console.error("Fetch error (getClientData):", err);
      })
      .finally(() => {
        btnLoad.innerHTML = origHtml;
        btnLoad.disabled = false;
      });
  }

  // ==================== RENDER ====================

  function renderDashboard(data) {
    if (emptyEl) emptyEl.style.display = "none";
    if (dashboard) dashboard.style.display = "block";

    renderProfile(data.details || {});
    renderFinances(data.finances || {});
    renderHistory(data.history || []);
    renderTrends(data.trends || []);
  }

  function renderProfile(d) {
    setText("crmClientName", d.client_name || "—");
    setText("crmClientPhone", d.phone_primary || "—");
    setText("crmClientContact", d.contact_person || "—");
    setText("crmClientCity", d.city || "—");
    setText(
      "crmClientSince",
      d.registration_date ? fmtDate(d.registration_date) : "—",
    );
  }

  function renderFinances(f) {
    const fmt = (v) =>
      "LKR " +
      Number(v || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    setText("crmFinBilled", fmt(f.total_billed));
    setText("crmFinPaid", fmt(f.total_paid));
    setText("crmFinDue", fmt(f.total_outstanding));
    setText("crmFinTotal", (f.total_samples || 0) + " samples");
  }

  function renderHistory(rows) {
    const tbody = document.getElementById("crmHistoryBody");
    const countEl = document.getElementById("crmHistoryCount");
    if (!tbody) return;
    if (countEl) countEl.textContent = rows.length;

    if (rows.length === 0) {
      tbody.innerHTML =
        '<tr class="crm-no-data-row"><td colspan="9">No samples found for this client.</td></tr>';
      return;
    }

    let html = "";
    rows.forEach((r, i) => {
      const labBadge = getLabBadge(r.status);
      const payBadge = getPayBadge(r.payment_status);

      html += `<tr>
                <td>${i + 1}</td>
                <td>${esc(r.form_number || "—")}</td>
                <td><strong>${esc(r.sample_code || "—")}</strong></td>
                <td>${fmtDate(r.received_date)}</td>
                <td>${fmtDate(r.tentative_date)}</td>
                <td>${fmtDate(r.analysis_end_date)}</td>
                <td>${labBadge}</td>
                <td>${payBadge}</td>
                <td class="crm-money">${fmtMoney(r.grand_total)}</td>
            </tr>`;
    });
    tbody.innerHTML = html;
  }

  function renderTrends(rows) {
    const grid = document.getElementById("crmTrendsGrid");
    if (!grid) return;

    if (!rows || rows.length === 0) {
      grid.innerHTML =
        '<div style="padding:20px; color:#64748b; text-align:center; font-size:0.9rem;">No testing data available for this client yet.</div>';
      return;
    }

    let html = "";
    rows.forEach((r) => {
      html += `<div class="crm-trend-item">
                <span class="crm-trend-name"><i class="bi bi-tag-fill text-muted me-2" style="font-size:0.8rem;"></i>${esc(r.parameter_name)}</span>
                <span class="crm-trend-count">${r.test_count} test${r.test_count > 1 ? "s" : ""}</span>
            </div>`;
    });
    grid.innerHTML = html;
  }

  // ==================== HELPERS ====================

  function getLabBadge(s) {
    const map = {
      Completed: ["badge-completed", "bi-check-circle-fill", "Completed"],
      "In Progress": ["badge-progress", "bi-gear-fill", "In Progress"],
      Pending: ["badge-pending", "bi-clock-fill", "Pending"],
    };
    const [cls, icon, label] = map[s] || [
      "badge-pending",
      "bi-dash-circle",
      s || "—",
    ];
    return `<span class="crm-badge ${cls}"><i class="bi ${icon}"></i> ${label}</span>`;
  }

  function getPayBadge(s) {
    if (s === "Paid")
      return '<span class="crm-badge badge-pay-paid">Paid</span>';
    if (s === "Not Paid")
      return '<span class="crm-badge badge-pay-notpaid">Not Paid</span>';
    if (s === "Pending")
      return '<span class="crm-badge badge-pay-pending">Pending</span>';
    return `<span class="crm-badge badge-pay-pending">${esc(s || "—")}</span>`;
  }

  function fmtDate(d) {
    if (!d) return '<span style="color:#94a3b8;">—</span>';
    try {
      const dt = new Date(d.length === 10 ? d + "T00:00:00" : d);
      return dt.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
    } catch {
      return d;
    }
  }

  function fmtMoney(v) {
    return Number(v || 0).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function esc(s) {
    if (!s && s !== 0) return "";
    const d = document.createElement("div");
    d.textContent = String(s);
    return d.innerHTML;
  }

  // Auto-init
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  return {};
})();
