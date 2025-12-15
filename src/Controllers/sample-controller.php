<?php
/**
 * Sample Controller - FIXED VERSION
 * Version: 2.1 (All Bugs Fixed)
 */

// Load dependencies FIRST (before any function calls)
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';
require_once __DIR__ . '/../Models/sample-model.php';

// Then start session
session_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Unauthorized access. Please log in.'
    ]);
}

// Initialize model
try {
    $sampleModel = new SampleModel();
} catch (Exception $e) {
    logError($e->getMessage(), 'SampleModel initialization');
    sendJsonResponse([
        'success' => false,
        'message' => 'Database connection failed. Please try again.'
    ]);
}

// Get action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Route to appropriate handler
try {
    switch ($action) {
        case 'searchClients':
            handleSearchClients($sampleModel);
            break;
        
        case 'createClient':
            handleCreateClient($sampleModel);
            break;
        
        case 'updateClient':
            handleUpdateClient($sampleModel);
            break;
        
        case 'getParameters':
            handleGetParameters($sampleModel);
            break;
        
        case 'searchSampleNames':
            handleSearchSampleNames($sampleModel);
            break;
        
        case 'validatePaymentReference': // FIX: Added full name
            handleValidatePaymentReference();
            break;
        
        case 'saveSample':
            handleSaveSample($sampleModel);
            break;
        
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid action: ' . $action
            ]);
    }
} catch (Exception $e) {
    logError($e->getMessage(), 'Controller: ' . $action);
    sendJsonResponse([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Handle client search request
 */
function handleSearchClients($model)
{
    $query = trim($_GET['query'] ?? '');
    
    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Search query must be at least 2 characters'
        ]);
    }
    
    $result = $model->searchClients($query);
    sendJsonResponse($result);
}

/**
 * Handle create new client request
 */
function handleCreateClient($model)
{
    // Validate required fields
    $requiredFields = ['client_name', 'phone_primary'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required',
                'field' => $field
            ]);
        }
    }
    
    // Validate phone format
    if (!validatePhone($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Phone number must be 10 digits starting with 0',
            'field' => 'phone_primary'
        ]);
    }
    
    // Sanitize input
    $data = [
        'client_name' => sanitizeInput($_POST['client_name']),
        'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary' => sanitizeInput($_POST['phone_primary']),
        'contact_person' => sanitizeInput($_POST['contact_person'] ?? '')
    ];
    
    $result = $model->createClient($data);
    sendJsonResponse($result);
}

/**
 * Handle update client request
 */
function handleUpdateClient($model)
{
    $clientId = intval($_POST['client_id'] ?? 0);
    
    if ($clientId === 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Client ID is required',
            'field' => 'client_id'
        ]);
    }
    
    // Validate required fields
    if (empty($_POST['client_name']) || empty($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Client name and phone are required',
            'field' => empty($_POST['client_name']) ? 'client_name' : 'phone_primary'
        ]);
    }
    
    // Validate phone format
    if (!validatePhone($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Phone number must be 10 digits starting with 0',
            'field' => 'phone_primary'
        ]);
    }
    
    // Sanitize input
    $data = [
        'client_id' => $clientId,
        'client_name' => sanitizeInput($_POST['client_name']),
        'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary' => sanitizeInput($_POST['phone_primary']),
        'contact_person' => sanitizeInput($_POST['contact_person'] ?? '')
    ];
    
    $result = $model->updateClient($data);
    sendJsonResponse($result);
}

/**
 * Handle get parameters request
 */
function handleGetParameters($model)
{
    $submissionType = $_GET['type'] ?? 'regular';
    
    if (!in_array($submissionType, ['regular', 'swab'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid submission type. Must be "regular" or "swab"'
        ]);
    }
    
    $result = $model->getParameters($submissionType);
    sendJsonResponse($result);
}

/**
 * Handle sample name search for autocomplete
 */
function handleSearchSampleNames($model)
{
    $query = trim($_GET['query'] ?? $_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => true,
            'names' => [], // FIX: Changed from 'results' to 'names'
            'count' => 0
        ]);
    }
    
    $result = $model->searchSampleNames($query);
    sendJsonResponse($result);
}

/**
 * Handle payment reference validation
 * FIX: Removed model parameter, create DB connection directly
 */
function handleValidatePaymentReference()
{
    // FIX: Changed from 'reference' to 'payment_reference'
    $reference = trim($_POST['payment_reference'] ?? $_GET['payment_reference'] ?? '');
    
    if (empty($reference)) {
        sendJsonResponse([
            'success' => true,
            'is_unique' => true, // Empty is valid
            'message' => 'No reference provided'
        ]);
    }
    
    // Validate format
    $formatValidation = validatePaymentReference($reference);
    if (!$formatValidation['valid']) {
        sendJsonResponse([
            'success' => true,
            'is_unique' => false,
            'message' => $formatValidation['message']
        ]);
    }
    
    // Check uniqueness - FIX: Create DB connection directly
    try {
        $database = new Database();
        $conn = $database->connect();
        $isUnique = isPaymentReferenceUnique($conn, $reference);
        
        sendJsonResponse([
            'success' => true,
            'is_unique' => $isUnique,
            'message' => $isUnique ? 'Payment reference is available' : 'Payment reference already exists'
        ]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'handleValidatePaymentReference');
        sendJsonResponse([
            'success' => false,
            'message' => 'Database error checking payment reference'
        ]);
    }
}

/**
 * Handle main sample submission
 */
function handleSaveSample($model)
{
    try {
        // 1. Validate required fields
        $requiredFields = [
            'client_id' => 'Client',
            'submission_type' => 'Submission type',
            'received_date' => 'Received date',
            'tentative_date' => 'Tentative date',
            'payment_status' => 'Payment status'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                sendJsonResponse([
                    'success' => false,
                    'message' => "$label is required",
                    'field' => $field
                ]);
            }
        }
        
        // 2. Validate client_id
        $clientId = intval($_POST['client_id']);
        if ($clientId <= 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid client selected',
                'field' => 'client_id'
            ]);
        }
        
        // 3. Validate submission type
        if (!in_array($_POST['submission_type'], ['regular', 'swab'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid submission type',
                'field' => 'submission_type'
            ]);
        }
        
        // 4. Validate dates
        $receivedDateValidation = validateReceivedDate($_POST['received_date']);
        if (!$receivedDateValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Received date: ' . $receivedDateValidation['message'],
                'field' => 'received_date'
            ]);
        }
        
        $tentativeDateValidation = validateTentativeDate($_POST['tentative_date']);
        if (!$tentativeDateValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Tentative date: ' . $tentativeDateValidation['message'],
                'field' => 'tentative_date'
            ]);
        }
        
        // 5. Validate payment
        $paymentStatus = $_POST['payment_status'];
        if (!in_array($paymentStatus, ['Paid', 'Not Paid', 'Pending'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid payment status',
                'field' => 'payment_status'
            ]);
        }
        
        if ($paymentStatus === 'Paid') {
            if (empty($_POST['payment_reference'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Payment reference is required when payment status is Paid',
                    'field' => 'payment_reference'
                ]);
            }
            
            // Validate uniqueness
            $database = new Database();
            $conn = $database->connect();
            if (!isPaymentReferenceUnique($conn, $_POST['payment_reference'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'This payment reference already exists',
                    'field' => 'payment_reference'
                ]);
            }
        }
        
        // 6. Parse samples
        if (empty($_POST['samples'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'No samples provided',
                'field' => 'samples'
            ]);
        }
        
        $samplesData = is_string($_POST['samples']) 
            ? json_decode($_POST['samples'], true) 
            : $_POST['samples'];
        
        if (!is_array($samplesData) || count($samplesData) === 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid samples data',
                'field' => 'samples'
            ]);
        }
        
        // 7. Parse tests
        if (empty($_POST['tests'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'No tests selected',
                'field' => 'tests'
            ]);
        }
        
        $testsData = is_string($_POST['tests']) 
            ? json_decode($_POST['tests'], true) 
            : $_POST['tests'];
        
        if (!is_array($testsData) || count($testsData) === 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid tests data',
                'field' => 'tests'
            ]);
        }
        
        // 8. Validate max 10 tests per sample
        $testsBySample = [];
        foreach ($testsData as $test) {
            $sampleIndex = $test['sample'];
            if (!isset($testsBySample[$sampleIndex])) {
                $testsBySample[$sampleIndex] = 0;
            }
            $testsBySample[$sampleIndex]++;
        }
        
        foreach ($testsBySample as $sampleIndex => $testCount) {
            if ($testCount > 10) {
                sendJsonResponse([
                    'success' => false,
                    'message' => "Sample $sampleIndex has $testCount tests. Maximum 10 tests allowed",
                    'field' => 'tests'
                ]);
            }
        }
        
        // 9. Calculate totals
        $testChargesTotal = 0.00;
        foreach ($testsData as $test) {
            $testChargesTotal += (float)$test['charge'];
        }
        
        $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
        $grandTotal = $testChargesTotal + $additionalCharges;
        
        // 10. Prepare data
        $submissionData = [
            'client_id' => $clientId,
            'submission_type' => sanitizeInput($_POST['submission_type']),
            'received_date' => sanitizeInput($_POST['received_date']),
            'tentative_date' => sanitizeInput($_POST['tentative_date']),
            'submitted_by' => sanitizeInput($_SESSION['fullname']),
            'additional_notes' => sanitizeInput($_POST['additional_notes'] ?? ''),
            'additional_charges' => $additionalCharges,
            'test_charges_total' => $testChargesTotal,
            'grand_total' => $grandTotal,
            'payment_status' => sanitizeInput($_POST['payment_status']),
            'payment_reference' => sanitizeInput($_POST['payment_reference'] ?? ''),
            'samples' => $samplesData,
            'tests' => $testsData
        ];
        
        // 11. Save
        $result = $model->saveSample($submissionData);
        sendJsonResponse($result);
        
    } catch (Exception $e) {
        logError($e->getMessage(), 'handleSaveSample');
        sendJsonResponse([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}