<?php
/**
 * Forms Controller - Master Router for All 3 Forms
 * Handles requests for SAF, Sample Acknowledgement, and Analyst Information forms
 * 
 * Routes:
 * - view: Display all 3 forms in carousel modal
 * - getFormData: Get form data via AJAX
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 1.0
 */

session_start();

require_once __DIR__ . '/../Models/saf-model.php';
require_once __DIR__ . '/../Models/acknowledgement-model.php';
require_once __DIR__ . '/../Models/analyst-model.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Please login'
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? 'view';

// Initialize models
try {
    $safModel = new SAFModel();
    $ackModel = new AcknowledgementModel();
    $analystModel = new AnalystModel();
} catch (Exception $e) {
    error_log("Forms Controller - Model initialization error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

// Route actions
switch ($action) {
    case 'view':
        handleViewForms($safModel, $ackModel, $analystModel);
        break;
    
    case 'getFormData':
        handleGetFormData($safModel, $ackModel, $analystModel);
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}

/**
 * Handle view all forms request
 * Fetches data from all 3 models and loads carousel modal
 */
function handleViewForms($safModel, $ackModel, $analystModel)
{
    $sampleId = intval($_GET['sample_id'] ?? 0);

    if ($sampleId === 0) {
        http_response_code(400);
        echo '<h3>Error: Sample ID required</h3>';
        exit;
    }

    // Fetch data from all models
    $safResult = $safModel->getSAFData($sampleId);
    $ackData = $ackModel->getAcknowledgementData($sampleId);
    $analystData = $analystModel->getAnalystData($sampleId);

    // Check if data exists
    if (!$safResult['success']) {
        http_response_code(404);
        echo '<h3>Error: Sample not found</h3>';
        exit;
    }

    if (!$ackData || !$analystData) {
        http_response_code(404);
        echo '<h3>Error: Form data incomplete</h3>';
        exit;
    }

    // Prepare data for modal
    $safData = $safResult['data'];
    
    // IMPORTANT: Mark SAF as inside carousel (hides its own controls)
    $safData['inside_carousel'] = true;
    
    // Load carousel modal
    include __DIR__ . '/../Includes/forms-carousel-modal.php';
}

/**
 * Handle get form data request (AJAX)
 * Returns form data as JSON
 */
function handleGetFormData($safModel, $ackModel, $analystModel)
{
    header('Content-Type: application/json');
    
    $sampleId = intval($_GET['sample_id'] ?? 0);

    if ($sampleId === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sample ID required'
        ]);
        exit;
    }

    // Fetch data from all models
    $safResult = $safModel->getSAFData($sampleId);
    $ackData = $ackModel->getAcknowledgementData($sampleId);
    $analystData = $analystModel->getAnalystData($sampleId);

    // Check if data exists
    if (!$safResult['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Sample not found'
        ]);
        exit;
    }

    if (!$ackData || !$analystData) {
        echo json_encode([
            'success' => false,
            'message' => 'Form data incomplete'
        ]);
        exit;
    }

    // Return data
    echo json_encode([
        'success' => true,
        'data' => [
            'saf' => $safResult['data'],
            'acknowledgement' => $ackData,
            'analyst' => $analystData
        ]
    ]);
}