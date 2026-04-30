<?php
// src/Includes/sidebar.php - COMPLETE RBAC IMPLEMENTATION

require_once __DIR__ . '/../../Config/RolePermissions.php';

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get user role
$userRole = $user['role'] ?? 'Client';

// Minimal safety fallback for $user data
if (!isset($user)) {
    $user = ['name' => 'User', 'role' => $userRole, 'initials' => 'U'];
}

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
        // ['id' => 'test-assignment', 'label' => 'Assign Tests', 'url' => 'index.php?page=test-assignment'],
        ['id' => 'test-results', 'label' => 'Enter Results', 'url' => 'index.php?page=test-results'],
        ['id' => 'test-reports', 'label' => 'Test Reports', 'url' => 'index.php?page=test-reports']
        // ['id' => 'test-status', 'label' => 'Test Status Tracking', 'url' => 'index.php?page=test-status']
    ]],

    ['id' => 'parameters', 'label' => 'Test Parameters', 'icon' => 'bi-gear-wide-connected', 'submenu' => [
        ['id' => 'manage-parameter', 'label' => 'Manage Parameter', 'url' => 'index.php?page=manage-parameter'],
        ['id' => 'param-variants', 'label' => 'Parameter Variants', 'url' => 'index.php?page=param-variants'],
        ['id' => 'swab-parameter', 'label' => 'Swab Parameter', 'url' => 'index.php?page=swab-parameter']
    ]],

    ['id' => 'methods', 'label' => 'Test Methods', 'icon' => 'bi-funnel', 'url' => 'index.php?page=methods'],
    ['id' => 'pricing', 'label' => 'Pricing Management', 'icon' => 'bi-currency-dollar', 'url' => 'index.php?page=pricing'],
    ['id' => 'sample-records-view', 'label' => 'Sample Records', 'icon' => 'bi-search', 'url' => 'index.php?page=sample-records-view'],

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
        ['id' => 'settings-notifications', 'label' => 'Notifications', 'url' => 'index.php?page=settings-notifications'],
        ['id' => 'manage-cities', 'label' => 'Manage Cities', 'url' => 'index.php?page=manage-cities'],
        ['id' => 'manage-extra-items', 'label' => 'Manage Extra Items', 'url' => 'index.php?page=manage-extra-items'],
        ['id' => 'certificates', 'label' => 'Certificates', 'url' => 'index.php?page=certificates'],
        ['id' => 'manage-sample-names', 'label' => 'Sample Names', 'url' => 'index.php?page=manage-sample-names'],
        ['id' => 'manage-signatories', 'label' => 'Report Signatories', 'url' => 'index.php?page=manage-signatories']
    ]]
];

function isActive($menuId, $currentPage)
{
    return $currentPage === $menuId;
}

function hasActiveSubmenu($submenu, $currentPage, $userRole)
{
    foreach ($submenu as $item) {
        if (RolePermissions::hasPermission($userRole, $item['id']) && $item['id'] === $currentPage) {
            return true;
        }
    }
    return false;
}

/**
 * Check if user has access to menu item
 */
function hasAccess($item, $userRole)
{
    return RolePermissions::hasPermission($userRole, $item['id']);
}

/**
 * Check if submenu has any visible items
 */
function hasVisibleSubmenu($submenu, $userRole)
{
    foreach ($submenu as $item) {
        if (RolePermissions::hasPermission($userRole, $item['id'])) {
            return true;
        }
    }
    return false;
}
?>

<div class="sidebar bg-gradient-primary text-white" id="sidebar">
    <button class="sidebar-close-btn" id="sidebarClose" type="button" aria-label="Close sidebar">
        <i class="bi bi-x-lg" style="font-size: 1.25rem;"></i>
    </button>

    <div class="p-3 bg-white bg-opacity-10">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-semibold" style="width: 40px; height: 40px; min-width: 40px;">
                <?php echo $user['initials']; ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <p class="mb-0 small fw-medium text-truncate"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="mb-0 text-white-50" style="font-size: 0.75rem;"><?php echo htmlspecialchars($user['role']); ?></p>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav flex-grow-1 overflow-auto py-3 px-2">
        <?php foreach ($menuItems as $item):
            // Check if user has access to this menu item
            if (!hasAccess($item, $userRole)) {
                continue;
            }

            $isItemActive = isActive($item['id'], $currentPage);
            $hasSubmenu = isset($item['submenu']);

            // For submenus, check if there are any visible items
            if ($hasSubmenu && !hasVisibleSubmenu($item['submenu'], $userRole)) {
                continue;
            }

            $submenuActive = $hasSubmenu ? hasActiveSubmenu($item['submenu'], $currentPage, $userRole) : false;
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
                            <?php foreach ($item['submenu'] as $subItem):
                                // Check access for submenu items
                                if (!RolePermissions::hasPermission($userRole, $subItem['id'])) {
                                    continue;
                                }
                            ?>
                                <a href="<?php echo $subItem['url']; ?>"
                                    class="nav-link submenu-link d-flex align-items-center <?php echo isActive($subItem['id'], $currentPage) ? 'active' : ''; ?>"
                                    <?php if (isActive($subItem['id'], $currentPage)) echo 'aria-current="page"'; ?>
                                    title="<?php echo htmlspecialchars($subItem['label']); ?>">
                                    <i class="bi bi-circle-fill me-2 flex-shrink-0" style="font-size: 0.4rem;"></i>
                                    <span class="text-nowrap"><?php echo $subItem['label']; ?></span>
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
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>