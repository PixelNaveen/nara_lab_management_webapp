<?php
/**
 * SAMPLE RECORDS SYSTEM - QUICK TEST SCRIPT
 * 
 * Run this file in your browser to test if everything is set up correctly
 * URL: http://localhost/your-project/test-sample-records.php
 * 
 * This will check:
 * - Database connection
 * - Required tables exist
 * - Sample data exists
 * - Model class works
 * - Controller endpoints respond
 */

// Start session
session_start();

// For testing purposes, simulate logged-in user
// REMOVE THIS IN PRODUCTION!
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['fullname'] = 'Test User';
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sample Records System Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-pass { color: #198754; }
        .test-fail { color: #dc3545; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 8px; }
    </style>
</head>
<body class='bg-light'>
<div class='container py-5'>
    <h1 class='mb-4'>🧪 Sample Records System Test</h1>";

$allTestsPassed = true;

// ====================================
// TEST 1: Database Connection
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 1: Database Connection</h3>";

try {
    require_once __DIR__ . '/Config/Database.php';
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p class='test-pass'>✅ Database connection successful</p>";
    } else {
        echo "<p class='test-fail'>❌ Database connection failed</p>";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    echo "<p class='test-fail'>❌ Database connection error: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
}

echo "</div>";

// ====================================
// TEST 2: Check Tables Exist
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 2: Required Tables</h3>";

$requiredTables = ['samples', 'clients', 'sample_status_log'];
foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='test-pass'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='test-fail'>❌ Table '$table' not found</p>";
        $allTestsPassed = false;
    }
}

echo "</div>";

// ====================================
// TEST 3: Check Sample Data
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 3: Sample Data</h3>";

$result = $conn->query("SELECT COUNT(*) as count FROM samples");
if ($result) {
    $row = $result->fetch_assoc();
    $sampleCount = $row['count'];
    
    if ($sampleCount > 0) {
        echo "<p class='test-pass'>✅ Found $sampleCount sample records</p>";
    } else {
        echo "<p class='test-fail'>⚠️ No sample records found (This is OK if you haven't added samples yet)</p>";
    }
} else {
    echo "<p class='test-fail'>❌ Could not query samples table</p>";
    $allTestsPassed = false;
}

echo "</div>";

// ====================================
// TEST 4: Model Class
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 4: Model Class</h3>";

try {
    require_once __DIR__ . '/src/Models/sample-records-model.php';
    $model = new SampleStatusModel();
    echo "<p class='test-pass'>✅ Model class loaded successfully</p>";
    
    // Test getAllSamplesAdvanced
    try {
        $samples = $model->getAllSamplesAdvanced([]);
        echo "<p class='test-pass'>✅ getAllSamplesAdvanced() works - Found " . count($samples) . " samples</p>";
    } catch (Exception $e) {
        echo "<p class='test-fail'>❌ getAllSamplesAdvanced() error: " . $e->getMessage() . "</p>";
        $allTestsPassed = false;
    }
    
    // Test getStatusCounts
    try {
        $counts = $model->getStatusCounts();
        echo "<p class='test-pass'>✅ getStatusCounts() works</p>";
        echo "<ul>";
        echo "<li>Total: " . $counts['all'] . "</li>";
        echo "<li>Pending: " . $counts['Pending'] . "</li>";
        echo "<li>In Progress: " . $counts['In Progress'] . "</li>";
        echo "<li>Completed: " . $counts['Completed'] . "</li>";
        echo "<li>Cancelled: " . $counts['Cancelled'] . "</li>";
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p class='test-fail'>❌ getStatusCounts() error: " . $e->getMessage() . "</p>";
        $allTestsPassed = false;
    }
    
    // Test isValidStatus
    if ($model->isValidStatus('Pending')) {
        echo "<p class='test-pass'>✅ isValidStatus() works correctly</p>";
    } else {
        echo "<p class='test-fail'>❌ isValidStatus() not working</p>";
        $allTestsPassed = false;
    }
    
} catch (Exception $e) {
    echo "<p class='test-fail'>❌ Model class error: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
}

echo "</div>";

// ====================================
// TEST 5: Controller Endpoints
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 5: Controller Endpoints</h3>";

// Test fetchAll endpoint
$testUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/src/Controllers/sample-records-controller.php';

echo "<p><strong>Controller URL:</strong> $testUrl</p>";

// Use file_get_contents with POST data
$postData = http_build_query(['action' => 'fetchAll']);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postData
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($testUrl, false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['status']) && $data['status'] === 'success') {
        echo "<p class='test-pass'>✅ Controller 'fetchAll' endpoint works</p>";
    } else {
        echo "<p class='test-fail'>❌ Controller returned unexpected response</p>";
        echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
        $allTestsPassed = false;
    }
} else {
    echo "<p class='test-fail'>❌ Could not reach controller (Check path and permissions)</p>";
    $allTestsPassed = false;
}

echo "</div>";

// ====================================
// TEST 6: View File Exists
// ====================================
echo "<div class='test-section'>";
echo "<h3>Test 6: View File</h3>";

$viewPath = __DIR__ . '/src/Includes/sample-records-view.php';
if (file_exists($viewPath)) {
    echo "<p class='test-pass'>✅ View file exists at: $viewPath</p>";
} else {
    echo "<p class='test-fail'>❌ View file not found at: $viewPath</p>";
    $allTestsPassed = false;
}

echo "</div>";

// ====================================
// FINAL RESULT
// ====================================
echo "<div class='test-section'>";
if ($allTestsPassed) {
    echo "<h3 class='test-pass'>🎉 ALL TESTS PASSED!</h3>";
    echo "<p>Your Sample Records Management system is properly configured and ready to use.</p>";
    echo "<p><a href='index.php?page=sample-records-view' class='btn btn-primary'>Go to Sample Records Page</a></p>";
} else {
    echo "<h3 class='test-fail'>⚠️ SOME TESTS FAILED</h3>";
    echo "<p>Please review the errors above and fix them before proceeding.</p>";
    echo "<p>Refer to the INSTALLATION_GUIDE.md for troubleshooting tips.</p>";
}
echo "</div>";

echo "</div>
</body>
</html>";

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>