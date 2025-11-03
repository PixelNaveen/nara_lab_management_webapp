<?php
require_once __DIR__ . '/../Models/client-model.php';
header('Content-Type: application/json');

$model = new ClientModel();
$action = $_POST['action'] ?? '';

switch ($action) {

    // ========== FETCH ALL CLIENTS ==========
    case 'fetchAll':
        $clients = $model->getAllClients();
        echo json_encode(['status' => 'success', 'data' => $clients]);
        break;

    // ========== INSERT CLIENT ==========
    case 'insert':
        $name = trim($_POST['client_name']);
        $address = trim($_POST['address_line1']);
        $city = trim($_POST['city']);
        $phone = trim($_POST['phone_primary']);
        $contact = trim($_POST['contact_person']);

        if ($name === '' || $phone === '') {
            echo json_encode(['status' => 'error', 'message' => 'Client name and phone are required.']);
            exit;
        }

        // Prevent duplicates
        if ($model->isDuplicate($name, $phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Client already exists!']);
            exit;
        }

        if ($model->insertClient($name, $address, $city, $phone, $contact)) {
            echo json_encode(['status' => 'success', 'message' => 'Client added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
        }
        break;

    // ========== UPDATE CLIENT ==========
    case 'update':
        $id = intval($_POST['client_id']);
        $name = trim($_POST['client_name']);
        $address = trim($_POST['address_line1']);
        $city = trim($_POST['city']);
        $phone = trim($_POST['phone_primary']);
        $contact = trim($_POST['contact_person']);

        if ($name === '' || $phone === '') {
            echo json_encode(['status' => 'error', 'message' => 'Client name and phone are required.']);
            exit;
        }

        if ($model->updateClient($id, $name, $address, $city, $phone, $contact)) {
            echo json_encode(['status' => 'success', 'message' => 'Client updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
        break;

    // ========== SOFT DELETE ==========
    case 'delete':
        $id = intval($_POST['client_id']);
        if ($model->softDeleteClient($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Client deleted successfully (soft delete).']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
