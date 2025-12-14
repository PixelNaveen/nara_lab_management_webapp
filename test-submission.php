<?php
/**
 * EXTREME DEBUG - Find the exact error
 */

// 1. Turn on ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>EXTREME DEBUG TEST</h1>";
echo "<pre>";

// 2. Test file paths
echo "\n=== FILE PATH TESTS ===\n";
echo "Current file: " . __FILE__ . "\n";
echo "Current dir: " . __DIR__ . "\n";

$files_to_check = [
    'Config/Database.php',
    'src/Helpers/functions.php',
    'src/Models/sample-model.php',
    'src/Controllers/sample-controller.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ EXISTS: $file\n";
    } else {
        echo "❌ MISSING: $file (looking at: $full_path)\n";
    }
}

// 3. Test Database.php loading
echo "\n=== LOADING Config/Database.php ===\n";
try {
    require_once __DIR__ . '/Config/Database.php';
    echo "✅ Database.php loaded successfully\n";
} catch (Exception $e) {
    echo "❌ ERROR loading Database.php: " . $e->getMessage() . "\n";
    die();
}

// 4. Test functions.php loading
echo "\n=== LOADING src/Helpers/functions.php ===\n";
try {
    require_once __DIR__ . '/src/Helpers/functions.php';
    echo "✅ functions.php loaded successfully\n";
    
    // Test if functions exist
    if (function_exists('generateFormNumber')) {
        echo "✅ generateFormNumber() exists\n";
    } else {
        echo "❌ generateFormNumber() NOT FOUND\n";
    }
    
    if (function_exists('sendJsonResponse')) {
        echo "✅ sendJsonResponse() exists\n";
    } else {
        echo "❌ sendJsonResponse() NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR loading functions.php: " . $e->getMessage() . "\n";
    die();
}

// 5. Test Database connection
echo "\n=== TESTING DATABASE CONNECTION ===\n";
try {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        echo "✅ Database connected successfully\n";
        echo "Connection type: " . get_class($conn) . "\n";
    } else {
        echo "❌ Database connection returned null\n";
    }
} catch (Exception $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
    die();
}

// 6. Test model loading
echo "\n=== LOADING src/Models/sample-model.php ===\n";
try {
    require_once __DIR__ . '/src/Models/sample-model.php';
    echo "✅ sample-model.php loaded successfully\n";
    
    $model = new SampleModel();
    echo "✅ SampleModel instantiated successfully\n";
    
    // Test if methods exist
    $methods = ['searchClients', 'createClient', 'getParameters', 'searchSampleNames', 'saveSample'];
    foreach ($methods as $method) {
        if (method_exists($model, $method)) {
            echo "✅ Method $method() exists\n";
        } else {
            echo "❌ Method $method() NOT FOUND\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR with SampleModel: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    die();
}

// 7. Test actual API call
echo "\n=== TESTING API CALL (searchSampleNames) ===\n";
try {
    $result = $model->searchSampleNames('water');
    echo "✅ searchSampleNames() executed\n";
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ ERROR in searchSampleNames(): " . $e->getMessage() . "\n";
}

// 8. Test JSON response
echo "\n=== TESTING JSON RESPONSE ===\n";
ob_start();
sendJsonResponse(['success' => true, 'test' => 'This is a test']);
$output = ob_get_clean();
echo "JSON output: $output\n";

echo "\n=== ALL TESTS COMPLETE ===\n";
echo "</pre>";