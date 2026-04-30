<?php
/**
 * ULTRA DEBUG - Catches EVERYTHING including fatal errors
 */

// Log ALL errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/ultra-debug.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Create logs directory
$logDir = __DIR__ . '/../../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $log = "[" . date('Y-m-d H:i:s') . "] PHP Error: $errstr in $errfile on line $errline\n";
    file_put_contents(__DIR__ . '/../../logs/ultra-debug.log', $log, FILE_APPEND);
    return false;
});

// Fatal error handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $log = "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: {$error['message']} in {$error['file']} on line {$error['line']}\n";
        file_put_contents(__DIR__ . '/../../logs/ultra-debug.log', $log, FILE_APPEND);
        
        // Send error as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal PHP error occurred',
            'error' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

// Start output buffering
ob_start();

$debug_log = [];

try {
    $debug_log[] = "[" . date('H:i:s') . "] Step 1: Loading files";
    
    require_once __DIR__ . '/../../Config/Database.php';
    $debug_log[] = "[" . date('H:i:s') . "] ✓ Database.php loaded";
    
    require_once __DIR__ . '/../Helpers/Functions.php';
    $debug_log[] = "[" . date('H:i:s') . "] ✓ Functions.php loaded";
    
    require_once __DIR__ . '/../Models/SampleModel.php';
    $debug_log[] = "[" . date('H:i:s') . "] ✓ SampleModel.php loaded";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $debug_log[] = "[" . date('H:i:s') . "] ✓ Session started";
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }
    $debug_log[] = "[" . date('H:i:s') . "] ✓ User authenticated: " . $_SESSION['user_id'];
    
    $sampleModel = new SampleModel();
    $debug_log[] = "[" . date('H:i:s') . "] ✓ Model created";
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $debug_log[] = "[" . date('H:i:s') . "] Action: $action";
    
    if ($action !== 'saveSample') {
        throw new Exception("This ultra-debug version only handles saveSample. Got: $action");
    }
    
    // Log all POST data
    $debug_log[] = "[" . date('H:i:s') . "] === POST DATA ===";
    foreach ($_POST as $key => $value) {
        if (strlen($value) > 100) {
            $debug_log[] = "$key: " . substr($value, 0, 100) . "... (" . strlen($value) . " chars)";
        } else {
            $debug_log[] = "$key: $value";
        }
    }
    
    // Step-by-step validation
    $debug_log[] = "[" . date('H:i:s') . "] === VALIDATION START ===";
    
    $clientId = intval($_POST['client_id'] ?? 0);
    $debug_log[] = "1. Client ID: $clientId";
    if ($clientId <= 0) throw new Exception('Invalid client ID');
    
    $submissionType = $_POST['submission_type'] ?? '';
    $debug_log[] = "2. Submission type: $submissionType";
    if (!in_array($submissionType, ['regular', 'swab'])) {
        throw new Exception('Invalid submission type');
    }
    
    $receivedDate = $_POST['received_date'] ?? '';
    $tentativeDate = $_POST['tentative_date'] ?? '';
    $debug_log[] = "3. Dates: $receivedDate / $tentativeDate";
    
    $receivedValidation = validateReceivedDate($receivedDate);
    if (!$receivedValidation['valid']) {
        throw new Exception('Received date: ' . $receivedValidation['message']);
    }
    $debug_log[] = "4. Received date valid";
    
    $tentativeValidation = validateTentativeDate($tentativeDate);
    if (!$tentativeValidation['valid']) {
        throw new Exception('Tentative date: ' . $tentativeValidation['message']);
    }
    $debug_log[] = "5. Tentative date valid";
    
    $paymentStatus = $_POST['payment_status'] ?? '';
    $paymentReference = $_POST['payment_reference'] ?? '';
    $debug_log[] = "6. Payment: $paymentStatus ($paymentReference)";
    
    if ($paymentStatus === 'Paid' && empty($paymentReference)) {
        throw new Exception('Payment reference required');
    }
    
    if ($paymentStatus === 'Paid') {
        $database = new Database();
        $conn = $database->connect();
        $isUnique = isPaymentReferenceUnique($conn, $paymentReference);
        $debug_log[] = "7. Payment ref unique: " . ($isUnique ? 'YES' : 'NO');
        if (!$isUnique) {
            throw new Exception('Payment reference already exists');
        }
    }
    
    // Parse JSON data
    $debug_log[] = "[" . date('H:i:s') . "] === PARSING JSON ===";
    
    $samplesJson = $_POST['samples'] ?? '';
    $debug_log[] = "8. Samples JSON length: " . strlen($samplesJson);
    
    $samplesData = json_decode($samplesJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Samples JSON error: ' . json_last_error_msg());
    }
    $debug_log[] = "9. Samples parsed: " . count($samplesData) . " items";
    
    $testsJson = $_POST['tests'] ?? '';
    $debug_log[] = "10. Tests JSON length: " . strlen($testsJson);
    
    $testsData = json_decode($testsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Tests JSON error: ' . json_last_error_msg());
    }
    $debug_log[] = "11. Tests parsed: " . count($testsData) . " items";
    
    // Calculate totals
    $testChargesTotal = 0.00;
    foreach ($testsData as $test) {
        $testChargesTotal += (float)$test['charge'];
    }
    $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
    $grandTotal = $testChargesTotal + $additionalCharges;
    $debug_log[] = "12. Totals: $testChargesTotal + $additionalCharges = $grandTotal";
    
    // Prepare submission data
    $submissionData = [
        'client_id' => $clientId,
        'submission_type' => $submissionType,
        'received_date' => $receivedDate,
        'tentative_date' => $tentativeDate,
        'submitted_by' => $_SESSION['fullname'] ?? 'Unknown',
        'additional_notes' => $_POST['additional_notes'] ?? '',
        'additional_charges' => $additionalCharges,
        'test_charges_total' => $testChargesTotal,
        'grand_total' => $grandTotal,
        'payment_status' => $paymentStatus,
        'payment_reference' => $paymentReference,
        'samples' => $samplesData,
        'tests' => $testsData
    ];
    
    $debug_log[] = "[" . date('H:i:s') . "] === CALLING saveSample() ===";
    
    // Call saveSample with error catching
    try {
        $result = $sampleModel->saveSample($submissionData);
        $debug_log[] = "[" . date('H:i:s') . "] saveSample() completed";
        $debug_log[] = "Result: " . json_encode($result);
        
        $response = array_merge($result, ['debug' => $debug_log]);
    } catch (Exception $saveEx) {
        $debug_log[] = "[" . date('H:i:s') . "] ERROR IN saveSample(): " . $saveEx->getMessage();
        $debug_log[] = "File: " . $saveEx->getFile() . " Line: " . $saveEx->getLine();
        $debug_log[] = "Trace: " . $saveEx->getTraceAsString();
        throw $saveEx;
    }
    
} catch (Exception $e) {
    $debug_log[] = "[" . date('H:i:s') . "] EXCEPTION: " . $e->getMessage();
    $debug_log[] = "File: " . $e->getFile() . " Line: " . $e->getLine();
    
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debug_log,
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    ];
}

// Check for unwanted output
$unwantedOutput = ob_get_clean();
if (!empty($unwantedOutput)) {
    $debug_log[] = "WARNING: Unwanted output (" . strlen($unwantedOutput) . " bytes)";
    $response['unwanted_output'] = substr($unwantedOutput, 0, 1000);
}

// Write debug log to file
$logContent = "[" . date('Y-m-d H:i:s') . "] === NEW REQUEST ===\n";
$logContent .= implode("\n", $debug_log) . "\n\n";
file_put_contents(__DIR__ . '/../../logs/ultra-debug.log', $logContent, FILE_APPEND);

// Send JSON response
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;