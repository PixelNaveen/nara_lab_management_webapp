<?php
/**
 * Sample Controller
 * Handles all AJAX requests for sample submission
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 1.0
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
}

require_once __DIR__ . '/../Models/sample-model.php';
require_once __DIR__ . '/../Helpers/functions.php';

header('Content-Type: application/json');

$sampleModel = new SampleModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Route to appropriate handler
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
    
    case 'searchSampleNames':
        handleSearchSampleNames($sampleModel);
        break;
    
    case 'getParameters':
        handleGetParameters($sampleModel);
        break;
    
    case 'getVariants':
        handleGetVariants($sampleModel);
        break;
    
    case 'validatePaymentRef':
        handleValidatePaymentRef($sampleModel);
        break;
    
    case 'saveSample':
        handleSaveSample($sampleModel);
        break;
    
    default:
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}

/**
 * Handle client search request
 */
function handleSearchClients($sampleModel) {
    $query = trim($_GET['query'] ?? '');
    
    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Search query too short (minimum 2 characters)'
        ]);
    }
    
    $result = $sampleModel->searchClients($query);
    sendJsonResponse($result);
}

/**
 * Handle create new client request
 */
function handleCreateClient($sampleModel) {
    // Validate required fields
    $requiredFields = ['client_name', 'phone_primary'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
            ]);
        }
    }
    
    // Sanitize input
    $data = [
        'client_name' => sanitizeInput($_POST['client_name']),
        'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary' => sanitizeInput($_POST['phone_primary']),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
        'contact_person' => sanitizeInput($_POST['contact_person'] ?? '')
    ];
    
    // Validate email if provided
    if (!empty($data['email'])) {
        $emailValidation = validateEmail($data['email']);
        if (!$emailValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => $emailValidation['message']
            ]);
        }
    }
    
    // Validate mobile if provided
    if (!empty($data['mobile'])) {
        $mobileValidation = validateMobile($data['mobile']);
        if (!$mobileValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => $mobileValidation['message']
            ]);
        }
    }
    
    $result = $sampleModel->createClient($data);
    sendJsonResponse($result);
}

/**
 * Handle update client request
 */
function handleUpdateClient($sampleModel) {
    $clientId = intval($_POST['client_id'] ?? 0);
    
    if ($clientId === 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Client ID is required'
        ]);
    }
    
    // Validate required fields
    if (empty($_POST['client_name']) || empty($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Client name and phone are required'
        ]);
    }
    
    // Sanitize input
    $data = [
        'client_id' => $clientId,
        'client_name' => sanitizeInput($_POST['client_name']),
        'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary' => sanitizeInput($_POST['phone_primary']),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
        'contact_person' => sanitizeInput($_POST['contact_person'] ?? '')
    ];
    
    $result = $sampleModel->updateClient($data);
    sendJsonResponse($result);
}

/**
 * Handle sample name search for autocomplete
 */
function handleSearchSampleNames($sampleModel) {
    $query = trim($_GET['query'] ?? $_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => true,
            'results' => []
        ]);
    }
    
    $result = $sampleModel->searchSampleNames($query);
    sendJsonResponse($result);
}

/**
 * Handle get parameters request
 */
function handleGetParameters($sampleModel) {
    $submissionType = $_GET['type'] ?? 'regular';
    
    if (!in_array($submissionType, ['regular', 'swab'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid submission type'
        ]);
    }
    
    $result = $sampleModel->getParameters($submissionType);
    sendJsonResponse($result);
}

/**
 * Handle get variants request
 */
function handleGetVariants($sampleModel) {
    $parameterId = intval($_GET['parameter_id'] ?? 0);
    
    if ($parameterId === 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Parameter ID is required'
        ]);
    }
    
    $result = $sampleModel->getVariants($parameterId);
    sendJsonResponse($result);
}

/**
 * Handle payment reference validation
 */
function handleValidatePaymentRef($sampleModel) {
    $reference = trim($_POST['reference'] ?? $_GET['reference'] ?? '');
    
    if (empty($reference)) {
        sendJsonResponse([
            'valid' => false,
            'message' => 'Payment reference is required'
        ]);
    }
    
    // Validate format
    $formatValidation = validatePaymentReference($reference);
    if (!$formatValidation['valid']) {
        sendJsonResponse($formatValidation);
    }
    
    // Check uniqueness
    $result = $sampleModel->validatePaymentReference($reference);
    sendJsonResponse($result);
}

/**
 * Handle main sample submission
 */
function handleSaveSample($sampleModel) {
    try {
        // Validate required fields
        $requiredFields = ['client_id', 'submission_type', 'received_date', 'tentative_date', 'payment_status'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                sendJsonResponse([
                    'success' => false,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                ]);
            }
        }
        
        // Validate payment reference if status is Paid
        if ($_POST['payment_status'] === 'Paid') {
            if (empty($_POST['payment_reference'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Payment reference is required when payment status is Paid'
                ]);
            }
            
            // Validate uniqueness
            $refValidation = $sampleModel->validatePaymentReference($_POST['payment_reference']);
            if (!$refValidation['valid']) {
                sendJsonResponse([
                    'success' => false,
                    'message' => $refValidation['message']
                ]);
            }
        }
        
        // Validate dates
        $receivedDateValidation = validateReceivedDate($_POST['received_date']);
        if (!$receivedDateValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Received date: ' . $receivedDateValidation['message']
            ]);
        }
        
        $tentativeDateValidation = validateTentativeDate($_POST['tentative_date']);
        if (!$tentativeDateValidation['valid']) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Tentative date: ' . $tentativeDateValidation['message']
            ]);
        }
        
        // Parse samples data
        if (!isset($_POST['samples']) || !is_array($_POST['samples'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'No samples provided'
            ]);
        }
        
        // Parse tests data
        $testsData = isset($_POST['tests']) ? json_decode($_POST['tests'], true) : [];
        
        if (empty($testsData)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'No tests selected'
            ]);
        }
        
        // Organize tests by sample
        $sampleTests = [];
        foreach ($testsData as $test) {
            $sampleIndex = $test['sample'] - 1; // Convert to 0-based index
            if (!isset($sampleTests[$sampleIndex])) {
                $sampleTests[$sampleIndex] = [];
            }
            $sampleTests[$sampleIndex][] = $test;
        }
        
        // Prepare complete data structure
        $samples = [];
        $testChargesTotal = 0.00;
        
        foreach ($_POST['samples'] as $index => $sampleItem) {
            // Calculate item total from tests
            $itemTotal = 0.00;
            $tests = $sampleTests[$index] ?? [];
            
            foreach ($tests as $test) {
                $itemTotal += floatval($test['charge']);
            }
            
            $testChargesTotal += $itemTotal;
            
            $samples[] = [
                'sample_name' => sanitizeInput($sampleItem['sample_name']),
                'value' => sanitizeInput($sampleItem['value']),
                'unit' => sanitizeInput($sampleItem['unit']),
                'client_sample_code' => sanitizeInput($sampleItem['client_sample_code'] ?? ''),
                'sampling_location' => sanitizeInput($sampleItem['sampling_location'] ?? ''),
                'reason_for_analysis' => sanitizeInput($sampleItem['reason_for_analysis'] ?? ''),
                'container_damage' => sanitizeInput($sampleItem['container_damage'] ?? 'No'),
                'temperature_condition' => sanitizeInput($sampleItem['temperature_condition'] ?? 'Ambient'),
                'validity_status' => sanitizeInput($sampleItem['validity_status'] ?? 'OK'),
                'item_total_charge' => $itemTotal,
                'tests' => $tests
            ];
        }
        
        $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
        $grandTotal = $testChargesTotal + $additionalCharges;
        
        // Prepare main submission data
        $submissionData = [
            'client_id' => intval($_POST['client_id']),
            'submission_type' => $_POST['submission_type'],
            'received_date' => $_POST['received_date'],
            'tentative_date' => $_POST['tentative_date'],
            'submitted_by' => $_SESSION['fullname'],
            'additional_notes' => sanitizeInput($_POST['additional_notes'] ?? ''),
            'additional_charges' => $additionalCharges,
            'test_charges_total' => $testChargesTotal,
            'grand_total' => $grandTotal,
            'payment_status' => $_POST['payment_status'],
            'payment_reference' => sanitizeInput($_POST['payment_reference'] ?? ''),
            'samples' => $samples
        ];
        
        // Save to database
        $result = $sampleModel->saveSample($submissionData);
        
        sendJsonResponse($result);
        
    } catch (Exception $e) {
        logError($e->getMessage(), 'handleSaveSample');
        sendJsonResponse([
            'success' => false,
            'message' => 'Error processing submission: ' . $e->getMessage()
        ]);
    }
}
?>