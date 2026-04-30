// assets/js/script.js - COMPLETE FILE WITH SMOOTH ANIMATIONS

document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarClose = document.getElementById("sidebarClose");
  const sidebarToggleDesktop = document.getElementById("sidebarToggleDesktop");

  // =======================
  // CRITICAL FIX: Ensure sidebar starts hidden on mobile
  // =======================
  function initializeSidebarState() {
    if (window.innerWidth < 992) {
      sidebar.classList.remove("show");
      sidebarOverlay.classList.remove("show");
      document.body.style.overflow = "";
    } else {
      if (localStorage.getItem("sidebarCollapsed") === "true") {
        document.body.classList.add("sidebar-collapsed");
        setTimeout(() => {
          window.dispatchEvent(new Event("resize"));
        }, 100);
      }
    }
  }

  initializeSidebarState();

  // =======================
  // MOBILE BEHAVIOR
  // =======================

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      sidebar.classList.add("show");
      sidebarOverlay.classList.add("show");
      document.body.style.overflow = "hidden";
    });
  }

  function closeMobileSidebar() {
    sidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
    document.body.style.overflow = "";

    const openSubmenus = document.querySelectorAll("#sidebar .collapse.show");
    openSubmenus.forEach((submenu) => {
      const bsCollapse = bootstrap.Collapse.getInstance(submenu);
      if (bsCollapse) {
        bsCollapse.hide();
      }
    });
  }

  if (sidebarClose) {
    sidebarClose.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      closeMobileSidebar();
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      closeMobileSidebar();
    });
  }

  // =======================
  // DESKTOP BEHAVIOR
  // =======================

  if (sidebarToggleDesktop) {
    sidebarToggleDesktop.addEventListener("click", function () {
      document.body.classList.toggle("sidebar-collapsed");
      void document.body.offsetHeight;
      localStorage.setItem(
        "sidebarCollapsed",
        document.body.classList.contains("sidebar-collapsed"),
      );
      setTimeout(() => {
        window.dispatchEvent(new Event("resize"));
      }, 300);
    });
  }

  // =======================
  // AUTO-CLOSE ON NAVIGATION - WITH SMOOTH ANIMATION
  // =======================

  const sidebarLinks = document.querySelectorAll(
    "#sidebar a.nav-link, #sidebar a.submenu-link",
  );

  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const href = this.getAttribute("href");

      if (window.innerWidth < 992) {
        // SMOOTH ANIMATION: Prevent default, animate, then navigate
        e.preventDefault();

        // Start close animation
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
        document.body.style.overflow = "";

        // Close submenus
        const openSubmenus = document.querySelectorAll(
          "#sidebar .collapse.show",
        );
        openSubmenus.forEach((submenu) => {
          const bsCollapse = bootstrap.Collapse.getInstance(submenu);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        });

        // Wait for animation to complete (500ms), then navigate
        setTimeout(() => {
          window.location.href = href;
        }, 550); // Longer animation for smoother, more visible effect
      } else {
        // Desktop behavior
        const openSubmenus = document.querySelectorAll(
          "#sidebar .collapse.show",
        );
        openSubmenus.forEach((submenu) => {
          const bsCollapse = bootstrap.Collapse.getInstance(submenu);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        });

        if (!href.includes("page=dashboard")) {
          document.body.classList.add("sidebar-collapsed");
          localStorage.setItem("sidebarCollapsed", "true");
        } else {
          document.body.classList.remove("sidebar-collapsed");
          localStorage.setItem("sidebarCollapsed", "false");
        }
      }
    });
  });

  // =======================
  // ACCORDION BEHAVIOR
  // =======================

  const submenuToggles = document.querySelectorAll(
    '#sidebar [data-bs-toggle="collapse"]',
  );

  submenuToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const targetId = this.getAttribute("data-bs-target");

      document
        .querySelectorAll("#sidebar .collapse.show")
        .forEach((collapse) => {
          if ("#" + collapse.id !== targetId) {
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
              bsCollapse.hide();
            }
          }
        });
    });
  });

  // =======================
  // CLEAN STATE ON PAGE LOAD - DISABLED (Minimal Fix)
  // =======================

  /*
  document.querySelectorAll("#sidebar .collapse").forEach((collapse) => {
    const hasActiveChild = collapse.querySelector(".submenu-link.active");

    if (!hasActiveChild && collapse.classList.contains("show")) {
      collapse.classList.remove("show");
      const parentButton = document.querySelector(
        `[data-bs-target="#${collapse.id}"]`,
      );
      if (parentButton) {
        parentButton.setAttribute("aria-expanded", "false");
      }
    }
  });
  */

  // =======================
  // WINDOW RESIZE HANDLER
  // =======================

  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth >= 992) {
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
        document.body.style.overflow = "";
      } else {
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
        document.body.classList.remove("sidebar-collapsed");
        document.body.style.overflow = "";
      }
    }, 250);
  });

  // =======================
  // TRANSITION HELPER
  // =======================

  const observeBodyClass = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === "class") {
        const pageWrapper = document.getElementById("page-content-wrapper");
        const header = document.querySelector("header");

        if (pageWrapper && header) {
          void pageWrapper.offsetHeight;
          void header.offsetHeight;
        }
      }
    });
  });

  observeBodyClass.observe(document.body, {
    attributes: true,
    attributeFilter: ["class"],
  });

  // =======================
  // PREVENT BODY SCROLL
  // =======================

  const observeSidebar = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === "class" && window.innerWidth < 992) {
        if (sidebar.classList.contains("show")) {
          document.body.style.overflow = "hidden";
        } else {
          document.body.style.overflow = "";
        }
      }
    });
  });

  observeSidebar.observe(sidebar, {
    attributes: true,
    attributeFilter: ["class"],
  });

  console.log("✅ Sidebar initialized with smooth animations");
});
