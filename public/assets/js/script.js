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
    // DESKTOP BEHAVIOR - ENHANCED
    // =======================

    // Desktop: toggle collapse state with smooth transition
    if (sidebarToggleDesktop) {
        sidebarToggleDesktop.addEventListener('click', function() {
            // Toggle the collapsed state
            document.body.classList.toggle('sidebar-collapsed');
            
            // Force a reflow to ensure smooth transition
            void document.body.offsetHeight;
            
            // Save state
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
            
            // Trigger resize event to help charts and other components adjust
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        });
    }

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        // Trigger resize after restoration
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    }

    // =======================
    // SIDEBAR FIX - COLLAPSE MANAGEMENT
    // =======================

    const sidebarLinks = document.querySelectorAll('#sidebar a.nav-link, #sidebar a.submenu-link');
 
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            const href = this.getAttribute('href');

            // Close all open submenus before navigation
            const openSubmenus = document.querySelectorAll('#sidebar .collapse.show');
            openSubmenus.forEach(submenu => {
                const bsCollapse = bootstrap.Collapse.getInstance(submenu);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            });

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

    // =======================
    // ACCORDION BEHAVIOR (Only one submenu open)
    // =======================

    const submenuToggles = document.querySelectorAll('#sidebar [data-bs-toggle="collapse"]');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-bs-target');
            
            // Close all OTHER submenus (accordion behavior)
            document.querySelectorAll('#sidebar .collapse.show').forEach(collapse => {
                if ('#' + collapse.id !== targetId) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    });

    // =======================
    // CLEAN STATE ON PAGE LOAD
    // =======================

    // Close submenus that shouldn't be open
    document.querySelectorAll('#sidebar .collapse').forEach(collapse => {
        const hasActiveChild = collapse.querySelector('.submenu-link.active');
        
        // If no active child, force close
        if (!hasActiveChild && collapse.classList.contains('show')) {
            collapse.classList.remove('show');
            const parentButton = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
            if (parentButton) {
                parentButton.setAttribute('aria-expanded', 'false');
            }
        }
    });

    // =======================
    // TRANSITION HELPER - Ensures smooth animations
    // =======================
    
    // Monitor sidebar state changes and ensure proper transitions
    const observeBodyClass = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const pageWrapper = document.getElementById('page-content-wrapper');
                const header = document.querySelector('header');
                
                if (pageWrapper && header) {
                    // Force reflow for smooth transition
                    void pageWrapper.offsetHeight;
                    void header.offsetHeight;
                }
            }
        });
    });

    // Start observing body class changes
    observeBodyClass.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    console.log('✅ Sidebar initialized successfully with enhanced transitions');
});