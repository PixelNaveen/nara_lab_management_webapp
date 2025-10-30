<?php
// index.php
require_once __DIR__ . '/Config/Database.php';
//Database::connect();


session_start();

// Example user data
$user = [
    'name' => 'Kavidu Naveen',
    'role' => 'Lab Technician',
    'initials' => 'KN'
];

// Get current page from URL parameter
$page = $_GET['page'] ?? 'dashboard';

// Map "page" IDs to actual file names in src/Includes
$pageMap = [
    'dashboard' => 'dashboard-page.php',
    'form-info' => 'form-info.php',
    'sample-submission' => 'sample-submission.php',
    'header-section' => 'header-section.php',
    'users' => 'manage-users.php',
    'clients' => 'manage-clients.php',
    'manage-parameter' => 'manage-param.php',
    'param-variants' => 'manage-param-variants.php',
    'swab-parameter' => 'swab-param.php',
    'pricing'=> 'param-prices.php'
    // Add all other pages here
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- Custom CSS -->

    <link rel="stylesheet" href="public/assets/css/main.css">
    <link rel="stylesheet" href="public/assets/css/header.css">
    <link rel="stylesheet" href="public/assets/css/sidebar.css">
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/dashboard.css">
    <link rel="stylesheet" href="public/assets/css/manage-users.css">
    <link rel="stylesheet" href="public/assets/css/manage-clients.css">
    <link rel="stylesheet" href="public/assets/css/manage-param.css">
    <link rel="stylesheet" href="public/assets/css/manage-param-variants.css">
    <link rel="stylesheet" href="public/assets/css/swab-param.css">
    <link rel="stylesheet" href="public/assets/css/param-prices.css">

    </div>
</head>

<body>



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
                    <?php
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo "<h1 class='text-danger'>Page not found!</h1>";
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


    <!-- jQuery (optional) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script src="public/assets/js/script.js"></script>
    <script src="public/assets/js/load.js"></script>
</body>

</html>