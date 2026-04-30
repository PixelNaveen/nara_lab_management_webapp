<?php

/**
 * Main Index File
 * Laboratory Management System
 * 
 * Entry point for the application with authentication and RBAC
 */

// ✅ INCLUDE AUTHENTICATION CHECK FIRST
require_once __DIR__ . '/src/Includes/auth.php';

// ✅ INCLUDE ACCESS CONTROL (Page-level protection)
require_once __DIR__ . '/src/Includes/access-control.php';

// Database connection
require_once __DIR__ . '/Config/Database.php';
$db = new Database();
$conn = $db->connect();

// Prepare user data for sidebar
$user = [
    'name' => $_SESSION['fullname'],
    'username' => $_SESSION['username'],
    'role' => $_SESSION['role'],
    'initials' => $userInitials
];

// Get current page from URL parameter
$page = $_GET['page'] ?? 'dashboard';

// Show access denied message if redirected
$showAccessDenied = isset($_SESSION['access_denied']) && $_SESSION['access_denied'] === true;
if ($showAccessDenied) {
    $deniedPage = $_SESSION['access_denied_page'] ?? 'that page';
    unset($_SESSION['access_denied']);
    unset($_SESSION['access_denied_page']);
}

// Map "page" IDs to actual file names in src/Includes
$pageMap = [
    'dashboard' => 'dashboard-page.php',
    'form-info' => 'manage-info.php',
    'form-acceptance' => 'manage-saf.php',
    'form-acknowledgement' => 'manage-sacf.php',
    'form-analyst' => 'manage-aif.php',
    'sample-submission' => 'sample-submission.php',
    'users' => 'manage-users.php',
    'clients' => 'manage-clients.php',
    'manage-parameter' => 'manage-param.php',
    'param-variants' => 'manage-param-variants.php',
    'swab-parameter' => 'swab-param.php',
    'pricing' => 'param-prices.php',
    'methods' => 'manage-test-methods.php',
    'sample-records-view' => 'sample-records-view.php',
    'manage-cities' => 'manage-cities.php',
    'manage-extra-items' => 'manage-extra-items.php',
    'certificates' => 'manage-certificates.php',
    'manage-sample-names' => 'manage-sample-names.php',
    'test-results' => 'result-entry.php',
    'test-reports' => 'test-report.php',
    'manage-signatories' => 'manage-signatories.php',
    'report-turnaround' => 'report-turnaround.php',
    'report-client' => 'report-client.php',
    'report-daily' => 'report-daily.php',
    'report-revenue' => 'report-revenue.php'
];

// Resolve the file path safely
$pageFile = __DIR__ . '/src/Includes/' . ($pageMap[$page] ?? 'dashboard-page.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NARA Lab Management System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="public/images/Nara logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">


    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/header.css">
    <link rel="stylesheet" href="public/assets/css/sidebar.css">
    <link rel="stylesheet" href="public/assets/css/dashboard.css">
    <link rel="stylesheet" href="public/assets/css/manage-users.css">
    <link rel="stylesheet" href="public/assets/css/manage-clients.css">
    <link rel="stylesheet" href="public/assets/css/manage-param.css">
    <link rel="stylesheet" href="public/assets/css/manage-param-variants.css">
    <link rel="stylesheet" href="public/assets/css/swab-param.css">
    <link rel="stylesheet" href="public/assets/css/param-prices.css">
    <link rel="stylesheet" href="public/assets/css/manage-test-methods.css">
    <link rel="stylesheet" href="public/assets/css/sample-submission.css">
    <link rel="stylesheet" href="public/assets/css/manage-sample-names.css">
    <link rel="stylesheet" href="public/assets/css/result-entry.css">
    <link rel="stylesheet" href="public/assets/css/test-report.css">

    <?php if (isset($_GET['from'])): ?>
        <script>
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('from');
                window.history.replaceState({
                    path: url.toString()
                }, '', url.toString());
            }
        </script>
    <?php endif; ?>
</head>

<body>
    <?php include 'src/Includes/loader.php'; ?>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'src/Includes/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="flex-grow-1">
            <!-- Header -->
            <?php include 'src/Includes/header-section.php'; ?>

            <!-- Main Content -->
            <main class="p-3 p-md-4 bg-light" style="min-height: calc(100vh - 70px);">
                <div class="container-fluid">
                    <?php if ($showAccessDenied): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Access Denied!</strong> You don't have permission to access <strong><?php echo htmlspecialchars($deniedPage); ?></strong>.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo "<div class='alert alert-danger'>
                                <h4><i class='fas fa-exclamation-triangle'></i> Page Not Found</h4>
                                <p>The requested page could not be found.</p>
                              </div>";
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="public/assets/js/script.js"></script>
    <script src="public/assets/js/load.js"></script>
</body>

</html>