<?php

/**
 * Sample Controller - COMPLETE FINAL VERSION
 * Version: 5.0 - With Combo Detection & Smart UI Support
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';
require_once __DIR__ . '/../Models/sample-model.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Unauthorized access. Please log in.'
    ]);
}

try {
    $sampleModel = new SampleModel();
} catch (Exception $e) {
    logError($e->getMessage(), 'SampleModel initialization');
    sendJsonResponse([
        'success' => false,
        'message' => 'Database connection failed. Please try again.'
    ]);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

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

        case 'getCombos':
            handleGetCombos($sampleModel);
            break;

        case 'searchSampleNames':
            handleSearchSampleNames($sampleModel);
            break;

        case 'searchCities':
            handleSearchCities($sampleModel);
            break;

        case 'findCityByName':
            handleFindCityByName($sampleModel);
            break;

        case 'trackCityUsage':
            handleTrackCityUsage($sampleModel);
            break;

        case 'validatePaymentReference':
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

function handleCreateClient($model)
{
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

    if (!validatePhone($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Phone number must be 10 digits starting with 0',
            'field' => 'phone_primary'
        ]);
    }

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

    if (empty($_POST['client_name']) || empty($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Client name and phone are required',
            'field' => empty($_POST['client_name']) ? 'client_name' : 'phone_primary'
        ]);
    }

    if (!validatePhone($_POST['phone_primary'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Phone number must be 10 digits starting with 0',
            'field' => 'phone_primary'
        ]);
    }

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
 * NEW: Get all active combos for frontend detection
 */
function handleGetCombos($model)
{
    $result = $model->getCombos();
    sendJsonResponse($result);
}

function handleSearchSampleNames($model)
{
    $query = trim($_GET['query'] ?? $_GET['q'] ?? '');

    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => true,
            'names' => [],
            'count' => 0
        ]);
    }

    $result = $model->searchSampleNames($query);
    sendJsonResponse($result);
}

function handleValidatePaymentReference()
{
    $reference = trim($_POST['payment_reference'] ?? $_GET['payment_reference'] ?? '');

    if (empty($reference)) {
        sendJsonResponse([
            'success' => true,
            'is_unique' => true,
            'message' => 'No reference provided'
        ]);
    }

    $formatValidation = validatePaymentReference($reference);
    if (!$formatValidation['valid']) {
        sendJsonResponse([
            'success' => true,
            'is_unique' => false,
            'message' => $formatValidation['message']
        ]);
    }

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
 * MAIN SUBMISSION HANDLER - WITH COMBO PRICING FIX
 */
function handleSaveSample($model)
{
    try {
        // VALIDATION
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

        $clientId = intval($_POST['client_id']);
        if ($clientId <= 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid client selected',
                'field' => 'client_id'
            ]);
        }

        if (!in_array($_POST['submission_type'], ['regular', 'swab'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid submission type',
                'field' => 'submission_type'
            ]);
        }

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

        // PARSE DATA
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

        // Validate max 10 tests per sample
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

        // COMBO PRICING FIX - Calculate ACTUAL charges with combo detection
        try {
            $database = new Database();
            $conn = $database->connect();

            $calculationResult = calculateTestChargesWithCombos(
                $testsData,
                $conn,
                $_POST['submission_type']
            );

            if (!$calculationResult['success']) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Failed to calculate test charges: ' . $calculationResult['message'],
                    'field' => 'tests'
                ]);
            }

            // Use CORRECT total (with combo pricing applied)
            $testChargesTotal = $calculationResult['total'];
            $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
            $grandTotal = $testChargesTotal + $additionalCharges;

            // Log combo application
            if ($calculationResult['combos_count'] > 0) {
                $comboNames = array_column($calculationResult['combos_detected'], 'combo_name');
                logError(
                    "✓ COMBOS APPLIED: {$calculationResult['combos_count']} combos (" .
                        implode(', ', $comboNames) . ") | " .
                        "Individual: Rs. {$calculationResult['individual_total']} | " .
                        "Combo: Rs. {$testChargesTotal} | " .
                        "Savings: Rs. {$calculationResult['savings']} ({$calculationResult['discount_percentage']}%)",
                    'ComboSuccess'
                );
            }

            // Validate frontend didn't send inflated total
            $frontendTotal = floatval($_POST['test_charges_total'] ?? 0);
            $difference = abs($frontendTotal - $testChargesTotal);

            if ($difference > 0.01) {
                logError(
                    "Total mismatch - Frontend: Rs. $frontendTotal, Backend: Rs. $testChargesTotal, " .
                        "Diff: Rs. $difference, Combos: {$calculationResult['combos_count']}",
                    'TotalValidation'
                );

                // If frontend total EXCEEDS max possible, reject
                $maxPossibleTotal = 0;
                foreach ($testsData as $test) {
                    $maxPossibleTotal += (float)$test['charge'];
                }

                if ($frontendTotal > $maxPossibleTotal + 0.01) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Invalid total submitted. Price tampering detected.',
                        'field' => 'test_charges_total'
                    ]);
                }
            }
        } catch (Exception $e) {
            logError($e->getMessage(), 'ComboCalculation');
            sendJsonResponse([
                'success' => false,
                'message' => 'Error calculating charges: ' . $e->getMessage()
            ]);
        }

        // PREPARE DATA FOR MODEL
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
            'tests' => $testsData,
            'combo_calculation' => $calculationResult
        ];

        // SAVE TO DATABASE
        $result = $model->saveSample($submissionData);

        // Add combo details to response for UI
        if ($result['success'] && isset($calculationResult['combos_detected'])) {
            $result['pricing_details'] = [
                'individual_total' => $calculationResult['individual_total'],
                'combo_total' => $calculationResult['total'],
                'discount' => $calculationResult['savings'],
                'discount_percentage' => $calculationResult['discount_percentage'],
                'combos_applied' => $calculationResult['combos_detected']
            ];
        }

        sendJsonResponse($result);
    } catch (Exception $e) {
        logError($e->getMessage(), 'handleSaveSample');
        sendJsonResponse([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * ==========================================
 * CITY AUTOCOMPLETE HANDLERS
 * ==========================================
 */

function handleSearchCities($model)
{
    $query = trim($_GET['query'] ?? $_GET['q'] ?? '');

    if (strlen($query) < 2) {
        sendJsonResponse([
            'success' => true,
            'cities' => [],
            'count' => 0,
            'message' => 'Query too short - need at least 2 characters'
        ]);
    }

    $result = $model->searchCities($query);
    sendJsonResponse($result);
}

function handleFindCityByName($model)
{
    $cityName = trim($_GET['city_name'] ?? $_POST['city_name'] ?? '');

    if (empty($cityName)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'City name is required'
        ]);
    }

    $result = $model->findCityByName($cityName);
    
    if ($result === null) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Error finding city'
        ]);
    }

    sendJsonResponse($result);
}

function handleTrackCityUsage($model)
{
    $cityId = intval($_POST['city_id'] ?? 0);

    if ($cityId <= 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid city ID'
        ]);
    }

    $success = $model->incrementCityUsage($cityId);
    
    sendJsonResponse([
        'success' => $success,
        'message' => $success ? 'Usage tracked' : 'Failed to track usage'
    ]);
}

/**
 * ==========================================
 * END CITY AUTOCOMPLETE HANDLERS
 * ==========================================
 */