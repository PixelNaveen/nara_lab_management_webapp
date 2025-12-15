<?php
// Test Controller - Find the Error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Controller Setup</h1>";

// Test 1: Database connection
echo "<h3>Test 1: Database Connection</h3>";
try {
    require_once __DIR__ . '/Config/Database.php';  // ← Correct path from root
    $db = new Database();
    $conn = $db->connect();
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    die();
}

// Test 2: Functions file
echo "<h3>Test 2: Functions File</h3>";
try {
    require_once __DIR__ . '/src/Helpers/functions.php';
    echo "✅ Functions loaded successfully<br>";
    
    // Test if generateFormNumber exists
    if (function_exists('generateFormNumber')) {
        echo "&nbsp;&nbsp;&nbsp;✅ generateFormNumber() exists<br>";
    } else {
        echo "&nbsp;&nbsp;&nbsp;❌ generateFormNumber() NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
    die();
}

// Test 3: Model file
echo "<h3>Test 3: Model File</h3>";
try {
    require_once __DIR__ . '/src/Models/sample-model.php';
    $model = new SampleModel();
    echo "✅ Model loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Model error: " . $e->getMessage() . "<br>";
    die();
}

// Test 4: Check if methods exist
echo "<h3>Test 4: Model Methods</h3>";
if (method_exists($model, 'saveSample')) {
    echo "✅ saveSample method exists<br>";
} else {
    echo "❌ saveSample method NOT FOUND<br>";
}

if (method_exists($model, 'searchClients')) {
    echo "✅ searchClients method exists<br>";
} else {
    echo "❌ searchClients method NOT FOUND<br>";
}

if (method_exists($model, 'getParameters')) {
    echo "✅ getParameters method exists<br>";
} else {
    echo "❌ getParameters method NOT FOUND<br>";
}

// Test 5: Check database tables
echo "<h3>Test 5: Database Tables</h3>";
$tables = ['samples', 'sample_items', 'sample_tests', 'sample_acceptance', 'sample_acknowledgement', 'form_sequence'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
        
        // Check for report_ref column in samples
        if ($table === 'samples') {
            $cols = $conn->query("SHOW COLUMNS FROM samples LIKE 'report_ref'");
            if ($cols->num_rows > 0) {
                echo "&nbsp;&nbsp;&nbsp;✅ Column 'report_ref' exists<br>";
            } else {
                echo "&nbsp;&nbsp;&nbsp;❌ Column 'report_ref' MISSING (run migration!)<br>";
            }
        }
        
        // Check for combo_id in sample_tests
        if ($table === 'sample_tests') {
            $cols = $conn->query("SHOW COLUMNS FROM sample_tests LIKE 'combo_id'");
            if ($cols->num_rows > 0) {
                echo "&nbsp;&nbsp;&nbsp;✅ Column 'combo_id' exists<br>";
            } else {
                echo "&nbsp;&nbsp;&nbsp;❌ Column 'combo_id' MISSING (run migration!)<br>";
            }
        }
        
        // Check sample_acceptance structure
        if ($table === 'sample_acceptance') {
            $cols = $conn->query("SHOW COLUMNS FROM sample_acceptance");
            echo "&nbsp;&nbsp;&nbsp;Columns: ";
            $colNames = [];
            while ($col = $cols->fetch_assoc()) {
                $colNames[] = $col['Field'];
            }
            echo implode(', ', $colNames) . "<br>";
        }
    } else {
        echo "❌ Table '$table' NOT FOUND<br>";
    }
}

// Test 6: Test JSON response function
echo "<h3>Test 6: Functions Test</h3>";
try {
    // Test date validation
    $dateTest = validateReceivedDate(date('Y-m-d'));
    if ($dateTest['valid']) {
        echo "✅ validateReceivedDate() works<br>";
    } else {
        echo "❌ validateReceivedDate() failed: " . $dateTest['message'] . "<br>";
    }
    
    // Test phone validation (updated rule: first digit must be 0)
    if (validatePhone('0771234567')) {
        echo "✅ validatePhone() works (accepts 0771234567)<br>";
    } else {
        echo "❌ validatePhone() failed on valid phone<br>";
    }
    
    if (!validatePhone('1234567890')) {
        echo "✅ validatePhone() correctly rejects non-0 start<br>";
    } else {
        echo "❌ validatePhone() incorrectly accepts non-0 start<br>";
    }
} catch (Exception $e) {
    echo "❌ Function test error: " . $e->getMessage() . "<br>";
}

echo "<h3>✅ All Tests Complete</h3>";
echo "<p><strong>If all tests pass, the error is in sample-controller.php or JavaScript.</strong></p>";
echo "<p><strong>If any tests fail, we need to fix those first!</strong></p>";
?>