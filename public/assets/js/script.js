// assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');

    // =======================
    // MOBILE BEHAVIOR
    // =======================

    // Mobile: open sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        });
    }

    // Mobile: close sidebar
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Mobile: click outside to close
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // =======================
    // DESKTOP BEHAVIOR
    // =======================

    // Desktop: toggle collapse state
    if (sidebarToggleDesktop) {
        sidebarToggleDesktop.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        });
    }

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    // =======================
    // AUTO COLLAPSE ON LINK CLICK (Desktop)
    // =======================

    const sidebarLinks = document.querySelectorAll('#sidebar a.nav-link, #sidebar a.submenu-link');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            const href = this.getAttribute('href');

            // If not dashboard, collapse sidebar
            if (!href.includes('page=dashboard')) {
                document.body.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', true);
            } else {
                // Keep sidebar open for dashboard
                document.body.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', false);
            }

            // Also close sidebar if on mobile (extra safeguard)
            if (window.innerWidth < 992) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    });
});
