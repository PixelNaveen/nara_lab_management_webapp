<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Please log in.']);
    exit;
}

require_once __DIR__ . '/../Models/ClientModel.php';
require_once __DIR__ . '/../Models/CityModel.php';
header('Content-Type: application/json');

$model = new ClientModel();
$cityModel = new CityModel();
$action = $_POST['action'] ?? '';

// Regex patterns to match frontend
$nameRegex = '/^[A-Za-z0-9\s.\-&\/()]{3,}$/';
$phoneRegex = '/^0[0-9]{9}$/';
$addressRegex = '/^[a-zA-Z0-9\s,.\-\/#():]{5,}$/';
$cityRegex = '/^[a-zA-Z0-9\s\-]{2,}$/';

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    echo json_encode(['status' => 'error', 'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"]);
    exit;
});

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action !== 'fetchAll') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

switch ($action) {

    // ========== FETCH ALL CLIENTS ==========
    case 'fetchAll':
        $clients = $model->getAllClients();
        echo json_encode(['status' => 'success', 'data' => $clients]);
        break;

    // ========== INSERT CLIENT ==========
    case 'insert':
        $name = trim($_POST['client_name'] ?? '');
        $address = trim($_POST['address_line1'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone_primary'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');

        // Strict Server-Side Validation
        if ($name === '' || $address === '' || $city === '' || $phone === '' || $contact === '') {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }
        if (!preg_match($nameRegex, $name)) {
            echo json_encode(['status' => 'error', 'message' => 'Client name contains invalid characters or is too short.']);
            exit;
        }
        if (!preg_match($addressRegex, $address)) {
            echo json_encode(['status' => 'error', 'message' => 'Address must be at least 5 characters long and contain valid characters.']);
            exit;
        }
        if (!preg_match($nameRegex, $contact)) {
            echo json_encode(['status' => 'error', 'message' => 'Contact person name contains invalid characters or is too short.']);
            exit;
        }
        if (!preg_match($phoneRegex, $phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Phone number must be exactly 10 digits starting with 0.']);
            exit;
        }
        if (!preg_match($cityRegex, $city)) {
            echo json_encode(['status' => 'error', 'message' => 'City must contain only letters, numbers, spaces, or hyphens.']);
            exit;
        }

        // Prevent duplicates (More granular checks)
        $duplicate = $model->getDuplicateDetails($name, $phone);
        if ($duplicate) {
            if (strtolower($duplicate['client_name']) === strtolower($name)) {
                echo json_encode(['status' => 'error', 'message' => 'A client with this name is already registered!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'This phone number is already assigned to another client!']);
            }
            exit;
        }

        // Auto-save new city if it doesn't exist
        if (!$cityModel->cityExists($city)) {
            $creator = $_SESSION['user_name'] ?? ('user_' . ($_SESSION['user_id'] ?? 'unknown'));
            $cityModel->insertCity($city, 0, $creator);
        }

        if ($model->insertClient($name, $address, $city, $phone, $contact)) {
            echo json_encode(['status' => 'success', 'message' => 'Client added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert client into database.']);
        }
        break;

    // ========== UPDATE CLIENT ==========
    case 'update':
        $id = intval($_POST['client_id'] ?? 0);
        $name = trim($_POST['client_name'] ?? '');
        $address = trim($_POST['address_line1'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone_primary'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid client ID.']);
            exit;
        }

        // Strict Server-Side Validation
        if ($name === '' || $address === '' || $city === '' || $phone === '' || $contact === '') {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }
        if (!preg_match($nameRegex, $name)) {
            echo json_encode(['status' => 'error', 'message' => 'Client name contains invalid characters or is too short.']);
            exit;
        }
        if (!preg_match($addressRegex, $address)) {
            echo json_encode(['status' => 'error', 'message' => 'Address must be at least 5 characters long and contain valid characters.']);
            exit;
        }
        if (!preg_match($nameRegex, $contact)) {
            echo json_encode(['status' => 'error', 'message' => 'Contact person name contains invalid characters or is too short.']);
            exit;
        }
        if (!preg_match($phoneRegex, $phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Phone number must be exactly 10 digits starting with 0.']);
            exit;
        }
        if (!preg_match($cityRegex, $city)) {
            echo json_encode(['status' => 'error', 'message' => 'City must contain only letters, numbers, spaces, or hyphens.']);
            exit;
        }

        // Prevent duplicates for update
        $duplicate = $model->getDuplicateDetailsForUpdate($id, $name, $phone);
        if ($duplicate) {
            if (strtolower($duplicate['client_name']) === strtolower($name)) {
                echo json_encode(['status' => 'error', 'message' => 'A client with this name is already registered!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'This phone number is already assigned to another client!']);
            }
            exit;
        }

        // Auto-save new city if it doesn't exist
        if (!$cityModel->cityExists($city)) {
            $creator = $_SESSION['user_name'] ?? ('user_' . ($_SESSION['user_id'] ?? 'unknown'));
            $cityModel->insertCity($city, 0, $creator);
        }

        if ($model->updateClient($id, $name, $address, $city, $phone, $contact)) {
            echo json_encode(['status' => 'success', 'message' => 'Client updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update client.']);
        }
        break;

    // ========== SOFT DELETE ==========
    case 'delete':
        $id = intval($_POST['client_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid client ID.']);
            exit;
        }

        if ($model->softDeleteClient($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Client deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete client.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
        break;
}
