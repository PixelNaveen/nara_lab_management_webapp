<?php
// includes/sidebar.php

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Menu items with proper navigation URLs
$menuItems = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-house-door', 'url' => 'index.php?page=dashboard'],
    ['id' => 'clients', 'label' => 'Client Management', 'icon' => 'bi-building', 'url' => 'index.php?page=clients'],
    ['id' => 'users', 'label' => 'User Management', 'icon' => 'bi-people', 'url' => 'index.php?page=users'],
    ['id' => 'sample-submission', 'label' => 'Sample Submission', 'icon' => 'bi-file-text', 'url' => 'index.php?page=sample-submission'],
    ['id' => 'manage-forms', 'label' => 'Form Management', 'icon' => 'bi-clipboard-check', 'submenu' => [
        ['id' => 'form-info', 'label' => 'Sample Information', 'url' => 'index.php?page=form-info'],
        ['id' => 'form-acceptance', 'label' => 'Sample Acceptance', 'url' => 'index.php?page=form-acceptance'],
        ['id' => 'form-acknowledgement', 'label' => 'Sample Acknowledgement', 'url' => 'index.php?page=form-acknowledgement'],
        ['id' => 'form-analyst', 'label' => 'Sample Analyst Report', 'url' => 'index.php?page=form-analyst']
    ]],
    ['id' => 'testing', 'label' => 'Testing & Analysis', 'icon' => 'bi-bar-chart', 'submenu' => [
        ['id' => 'test-assignment', 'label' => 'Assign Tests', 'url' => 'index.php?page=test-assignment'],
        ['id' => 'test-results', 'label' => 'Enter Results', 'url' => 'index.php?page=test-results'],
        ['id' => 'test-status', 'label' => 'Test Status Tracking', 'url' => 'index.php?page=test-status']
    ]],
    ['id' => 'parameters', 'label' => 'Test Parameters', 'icon' => 'bi-gear-wide-connected', 'submenu' => [
        ['id' => 'manage-parameter', 'label' => 'Manage Parameter', 'url' => 'index.php?page=manage-parameter'],
        ['id' => 'param-variants', 'label' => 'Parameter Variants', 'url' => 'index.php?page=param-variants'],
        ['id' => 'swab-parameter', 'label' => 'Swab Parameter', 'url' => 'index.php?page=swab-parameter']
    ]],
    ['id' => 'methods', 'label' => 'Test Methods', 'icon' => 'bi-funnel', 'url' => 'index.php?page=methods'],
    ['id' => 'pricing', 'label' => 'Pricing Management', 'icon' => 'bi-currency-dollar', 'url' => 'index.php?page=pricing'],
    ['id' => 'samples', 'label' => 'Sample Records', 'icon' => 'bi-search', 'url' => 'index.php?page=samples'],
    ['id' => 'reports', 'label' => 'Reports & Analytics', 'icon' => 'bi-graph-up', 'submenu' => [
        ['id' => 'report-daily', 'label' => 'Daily Summary', 'url' => 'index.php?page=report-daily'],
        ['id' => 'report-client', 'label' => 'Client Reports', 'url' => 'index.php?page=report-client'],
        ['id' => 'report-revenue', 'label' => 'Revenue Analysis', 'url' => 'index.php?page=report-revenue'],
        ['id' => 'report-turnaround', 'label' => 'Turnaround Time', 'url' => 'index.php?page=report-turnaround']
    ]],
    ['id' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear', 'submenu' => [
        ['id' => 'settings-general', 'label' => 'General Settings', 'url' => 'index.php?page=settings-general'],
        ['id' => 'settings-lab', 'label' => 'Lab Configuration', 'url' => 'index.php?page=settings-lab'],
        ['id' => 'settings-users', 'label' => 'User Roles & Permissions', 'url' => 'index.php?page=settings-users'],
        ['id' => 'settings-backup', 'label' => 'Backup & Restore', 'url' => 'index.php?page=settings-backup'],
        ['id' => 'settings-notifications', 'label' => 'Notifications', 'url' => 'index.php?page=settings-notifications']
    ]]
];

function isActive($menuId, $currentPage) {
    return $currentPage === $menuId;
}

function hasActiveSubmenu($submenu, $currentPage) {
    foreach ($submenu as $item) {
        if ($item['id'] === $currentPage) {
            return true;
        }
    }
    return false;
}
?>

<!-- Sidebar -->
<div class="sidebar bg-gradient-primary text-white" id="sidebar">
    <!-- User Info -->
    <div class="p-3 bg-white bg-opacity-10">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-semibold" style="width: 40px; height: 40px; min-width: 40px;">
                <?php echo $user['initials']; ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <p class="mb-0 small fw-medium text-truncate"><?php echo $user['name']; ?></p>
                <p class="mb-0 text-white-50" style="font-size: 0.75rem;"><?php echo $user['role']; ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav flex-grow-1 overflow-auto py-3 px-2">
        <?php foreach ($menuItems as $item): 
            $isItemActive = isActive($item['id'], $currentPage);
            $hasSubmenu = isset($item['submenu']);
            $submenuActive = $hasSubmenu ? hasActiveSubmenu($item['submenu'], $currentPage) : false;
        ?>
            <div class="nav-item mb-1">
                <?php if ($hasSubmenu): ?>
                    <button class="nav-link w-100 text-start d-flex align-items-center justify-content-between <?php echo $submenuActive ? 'active' : ''; ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#submenu-<?php echo $item['id']; ?>"
                        aria-expanded="<?php echo $submenuActive ? 'true' : 'false'; ?>"
                        aria-controls="submenu-<?php echo $item['id']; ?>">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden">
                            <i class="bi <?php echo $item['icon']; ?>"></i>
                            <span class="text-truncate"><?php echo $item['label']; ?></span>
                        </div>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </button>

                    <div class="collapse <?php echo $submenuActive ? 'show' : ''; ?>" id="submenu-<?php echo $item['id']; ?>">
                        <div class="submenu ps-4 mt-1">
                            <?php foreach ($item['submenu'] as $subItem): ?>
                                <a href="<?php echo $subItem['url']; ?>" 
                                   class="nav-link submenu-link <?php echo isActive($subItem['id'], $currentPage) ? 'active' : ''; ?>"
                                   <?php if (isActive($subItem['id'], $currentPage)) echo 'aria-current="page"'; ?>>
                                    <i class="bi bi-circle-fill me-2" style="font-size: 0.4rem;"></i>
                                    <?php echo $subItem['label']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-link d-flex align-items-center gap-2 <?php echo $isItemActive ? 'active' : ''; ?>"
                       <?php if ($isItemActive) echo 'aria-current="page"'; ?>>
                        <i class="bi <?php echo $item['icon']; ?>"></i>
                        <span class="text-truncate"><?php echo $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar Footer -->
  <!--  <div class="sidebar-footer p-2 border-top border-white border-opacity-25">
        <a href="logout.php" class="nav-link d-flex align-items-center gap-2 text-danger logout-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div> -->
</div>

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>