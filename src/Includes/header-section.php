<?php
// includes/header.php

// Get page title based on current page
$pageTitles = [
    'dashboard' => 'Dashboard',
    'clients' => 'Client Management',
    'users' => 'User Management',
    'sample-submission' => 'Sample Submission',
    'form-info' => 'Sample Information',
    'form-acceptance' => 'Sample Acceptance',
    'form-acknowledgement' => 'Sample Acknowledgement',
    'form-analyst' => 'Sample Analyst Report',
    'test-assignment' => 'Assign Tests',
    'test-results' => 'Enter Results',
    'test-status' => 'Test Status Tracking',
    'manage-parameter' => 'Manage Parameters',
    'param-variants' => 'Parameter Variants',
    'swab-parameter' => 'Swab Parameter',
    'methods' => 'Test Methods',
    'pricing' => 'Pricing Management',
    'samples' => 'Sample Records',
    'reports' => 'Reports & Analytics',
    'settings-general' => 'General Settings',
    'settings-lab' => 'Lab Configuration',
    'settings-users' => 'User Roles & Permissions',
    'settings-backup' => 'Backup & Restore',
    'settings-notifications' => 'Notifications'
];

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pageTitle = isset($pageTitles[$currentPage]) ? $pageTitles[$currentPage] : 'Dashboard';
?>

<header class="bg-white shadow-sm border-bottom">
    <div class="d-flex align-items-center justify-content-between px-3 px-md-4 py-3">
        <div class="d-flex align-items-center gap-3">
            <!-- Mobile Menu Toggle -->
            <button class="btn btn-link text-secondary p-0 d-lg-none" id="sidebarToggle" type="button">
                <i class="bi bi-list" style="font-size: 1.5rem;"></i>
            </button>
            
            <!-- Desktop Sidebar Toggle -->
            <button class="btn btn-link text-secondary p-0 d-none d-lg-block" id="sidebarToggleDesktop" type="button">
                <i class="bi bi-layout-sidebar-inset" style="font-size: 1.25rem;"></i>
            </button>
            
            <h2 class="h4 mb-0 fw-bold text-gray-800 d-none d-sm-block"><?php echo $pageTitle; ?></h2>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <!-- <div class="dropdown d-none d-md-block">
                <button class="btn btn-link text-secondary position-relative p-2" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        5
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 300px;">
                    <li class="px-3 py-2 border-bottom">
                        <h6 class="mb-0 fw-bold">Notifications</h6>
                    </li>
                    <li><a class="dropdown-item py-2" href="#">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-semibold">3 Overdue Tests</p>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">Require immediate attention</p>
                            </div>
                        </div>
                    </a></li>
                    <li><a class="dropdown-item py-2" href="#">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-clock text-primary"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-semibold">5 Tests Due Today</p>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">Complete by end of day</p>
                            </div>
                        </div>
                    </a></li>
                    <li class="border-top">
                        <a class="dropdown-item text-center py-2 text-primary small" href="#">View All</a>
                    </li>
                </ul>
            </div> -->
            
            <!-- Lab Info -->

            <div>
              <img src="public/images/Nara logo.png" alt="" >
            </div>
            <div class="text-end d-none d-md-block">
                <p class="mb-0 small text-secondary">National Aquatic Resources</p>
                <p class="mb-0 text-muted" style="font-size: 0.75rem;">Research & Development Agency</p>
            </div>
            
            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none p-0" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-center fw-semibold" style="width: 40px; height: 40px;">
                            <?php echo $user['initials']; ?>
                        </div>
                        <!-- <div class="text-start d-none d-md-block">
                            <p class="mb-0 small fw-semibold text-dark"><?php echo $user['name']; ?></p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?php echo $user['role']; ?></p>
                        </div> -->
                        <i class="bi bi-chevron-down text-secondary d-none d-md-block"></i>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                   
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="src/Views/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>