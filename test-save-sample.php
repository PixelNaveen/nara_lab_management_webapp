<?php
/**
 * SUPER SIMPLE TEST - No dependencies
 */

// Turn on ALL error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Super Simple Test</h1><pre>";

// Test 1: Can we load Database?
echo "\n=== Test 1: Database.php ===\n";
try {
    require_once __DIR__ . '/Config/Database.php';
    echo "✅ Loaded\n";
    
    $db = new Database();
    $conn = $db->connect();
    echo "✅ Connected: " . get_class($conn) . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    die();
}

// Test 2: Can we load functions?
echo "\n=== Test 2: functions.php ===\n";
try {
    require_once __DIR__ . '/src/Helpers/functions.php';
    echo "✅ Loaded\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    die();
}

// Test 3: Can we load model?
echo "\n=== Test 3: sample-model.php ===\n";
try {
    require_once __DIR__ . '/src/Models/sample-model.php';
    echo "✅ Loaded\n";
    
    $model = new SampleModel();
    echo "✅ Model created\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    die();
}

// Test 4: Can we prepare test data?
echo "\n=== Test 4: Prepare Test Data ===\n";
$testData = [
    'client_id' => 4,
    'submission_type' => 'regular',
    'received_date' => '2025-12-14',
    'tentative_date' => '2025-12-22',
    'submitted_by' => 'Test User',
    'additional_notes' => 'Test submission',
    'additional_charges' => 100.00,
    'test_charges_total' => 2500.00,
    'grand_total' => 2600.00,
    'payment_status' => 'Paid',
    'payment_reference' => 'TEST-' . time(),
    'samples' => [
        [
            'sample_name' => 'Test Water',
            'value' => '250',
            'unit' => 'ml',
            'client_sample_code' => 'TEST-001',
            'sampling_location' => 'Test Location',
            'reason_for_analysis' => 'Testing',
            'container_damage' => 'No',
            'temperature_condition' => 'Ambient',
            'validity_status' => 'OK'
        ]
    ],
    'tests' => [
        [
            'sample' => 1,
            'parameter_id' => 1,
            'variant_id' => 3,
            'charge' => 1250.00
        ],
        [
            'sample' => 1,
            'parameter_id' => 1,
            'variant_id' => 2,
            'charge' => 1250.00
        ]
    ]
];

echo "✅ Test data prepared\n";
echo "Client ID: " . $testData['client_id'] . "\n";
echo "Samples: " . count($testData['samples']) . "\n";
echo "Tests: " . count($testData['tests']) . "\n";

// Test 5: Can we call saveSample?
echo "\n=== Test 5: Call saveSample() ===\n";
try {
    echo "Calling saveSample()...\n";
    $result = $model->saveSample($testData);
    
    echo "✅ saveSample() completed\n";
    echo "Result:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n🎉 SUCCESS!\n";
        echo "Form Number: " . $result['form_number'] . "\n";
        echo "Sample ID: " . $result['sample_id'] . "\n";
        echo "AC Reference: " . $result['ac_reference'] . "\n";
    } else {
        echo "\n❌ FAILED: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "</pre>";