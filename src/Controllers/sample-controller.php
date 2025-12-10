<?php
require_once __DIR__ . '/../Models/sample-model.php';
require_once __DIR__ . '/../Helpers/functions.php';

// Instantiate the model
 $sampleModel = new SampleModel();

// Get the action from the request
 $action = $_GET['action'] ?? $_POST['action'] ?? '';

// Route the request to the appropriate handler
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
    case 'saveSample':
        handleSaveSample($sampleModel);
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Invalid action specified.']);
        break;
}

// --- Handler Functions ---

function handleSearchClients($model)
{
    $query = trim($_GET['query'] ?? '');
    if (strlen($query) < 2) {
        sendJsonResponse(['success' => false, 'message' => 'Search query must be at least 2 characters.']);
    }
    $result = $model->searchClients($query);
    sendJsonResponse($result);
}

function handleCreateClient($model)
{
    $requiredFields = ['client_name', 'phone_primary'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.']);
        }
    }

    $data = [
        'client_name'     => sanitizeInput($_POST['client_name']),
        'address_line1'   => sanitizeInput($_POST['address_line1'] ?? ''),
        'city'            => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary'   => sanitizeInput($_POST['phone_primary']),
        'contact_person'  => sanitizeInput($_POST['contact_person'] ?? '')
    ];

    if (!validatePhone($data['phone_primary'])) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid phone number format.']);
    }

    $result = $model->createClient($data);
    sendJsonResponse($result);
}

function handleUpdateClient($model)
{
    $clientId = intval($_POST['client_id'] ?? 0);
    if ($clientId <= 0) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid client ID.']);
    }

    $data = [
        'client_id'       => $clientId,
        'client_name'     => sanitizeInput($_POST['client_name'] ?? ''),
        'address_line1'   => sanitizeInput($_POST['address_line1'] ?? ''),
        'city'            => sanitizeInput($_POST['city'] ?? ''),
        'phone_primary'   => sanitizeInput($_POST['phone_primary'] ?? ''),
        'contact_person'  => sanitizeInput($_POST['contact_person'] ?? '')
    ];

    if (empty($data['client_name']) || empty($data['phone_primary'])) {
        sendJsonResponse(['success' => false, 'message' => 'Client name and phone are required.']);
    }

    if (!validatePhone($data['phone_primary'])) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid phone number format.']);
    }

    $result = $model->updateClient($data);
    sendJsonResponse($result);
}

function handleGetParameters($model)
{
    $type = $_GET['type'] ?? 'regular';
    $result = $model->getParameters($type);
    sendJsonResponse($result);
}

function handleSearchSampleNames($model)
{
    $query = trim($_GET['query'] ?? '');
    if (strlen($query) < 2) {
        sendJsonResponse(['success' => false, 'message' => 'Search query must be at least 2 characters.']);
    }
    $result = $model->searchSampleNames($query);
    sendJsonResponse($result);
}

function handleSaveSample($model)
{
    // Basic validation
    if (empty($_POST['client_id']) || empty($_POST['submission_type'])) {
        sendJsonResponse(['success' => false, 'message' => 'Client and submission type are required.']);
    }

    // Calculate totals from the form submission
    $testChargesTotal = 0;
    $tests = json_decode($_POST['tests'] ?? '[]', true);
    foreach ($tests as $test) {
        $testChargesTotal += (float)($test['charge'] ?? 0);
    }
    $additionalCharges = (float)($_POST['additional_charges'] ?? 0);
    $grandTotal = $testChargesTotal + $additionalCharges;

    $data = [
        'client_id'            => sanitizeInput($_POST['client_id']),
        'sample_code'          => 'TEMP-' . time(), // Generate a temporary unique code
        'submission_type'      => sanitizeInput($_POST['submission_type']),
        'received_date'        => sanitizeInput($_POST['received_date']),
        'tentative_date'       => sanitizeInput($_POST['tentative_date']),
        'submitted_by'         => sanitizeInput($_POST['submitted_by']),
        'additional_notes'     => sanitizeInput($_POST['additional_notes'] ?? ''),
        'additional_charges'   => $additionalCharges,
        'test_charges_total'   => $testChargesTotal,
        'grand_total'          => $grandTotal,
        'payment_status'       => sanitizeInput($_POST['payment_status']),
        'payment_reference'    => sanitizeInput($_POST['payment_reference'] ?? ''),
        'samples'              => $_POST['samples'],
        'tests'                => $_POST['tests']
    ];
    
    $result = $model->saveSample($data);
    sendJsonResponse($result);
}