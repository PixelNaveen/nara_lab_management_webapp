<?php

/**
 * Signatory Controller
 * Laboratory Management System
 *
 * Handles AJAX requests for report signatory CRUD operations.
 *
 * @version 1.0
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Models/SignatoryModel.php';

$model = new SignatoryModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'fetchAll':
        try {
            $signatories = $model->getAll();
            echo json_encode(['status' => 'success', 'data' => $signatories]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch signatories']);
        }
        break;

    case 'create':
        try {
            $data = [
                'full_name'     => trim($_POST['full_name'] ?? ''),
                'title'         => trim($_POST['title'] ?? ''),
                'division'      => trim($_POST['division'] ?? ''),
                'role_type'     => trim($_POST['role_type'] ?? 'scientist'),
                'is_default'    => intval($_POST['is_default'] ?? 0),
                'display_order' => intval($_POST['display_order'] ?? 0),
                'is_active'     => 1
            ];

            if (empty($data['full_name']) || empty($data['title'])) {
                throw new Exception('Name and title are required');
            }

            if ($model->exists($data['full_name'])) {
                echo json_encode(['status' => 'error', 'code' => 'DUPLICATE', 'message' => 'A signatory with this name already exists.']);
                exit;
            }

            $id = $model->create($data);
            if ($id) {
                echo json_encode(['status' => 'success', 'message' => 'Signatory created', 'id' => $id]);
            } else {
                throw new Exception('Failed to create signatory');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $id = intval($_POST['signatory_id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid ID');

            $data = [
                'full_name'     => trim($_POST['full_name'] ?? ''),
                'title'         => trim($_POST['title'] ?? ''),
                'division'      => trim($_POST['division'] ?? ''),
                'role_type'     => trim($_POST['role_type'] ?? 'scientist'),
                'is_default'    => intval($_POST['is_default'] ?? 0),
                'display_order' => intval($_POST['display_order'] ?? 0),
                'is_active'     => intval($_POST['is_active'] ?? 1)
            ];

            if ($model->exists($data['full_name'], $id)) {
                echo json_encode(['status' => 'error', 'code' => 'DUPLICATE', 'message' => 'Another signatory with this name already exists.']);
                exit;
            }

            $existing = $model->getById($id);
            if ($existing) {
                $changed = false;
                if ($existing['full_name'] !== $data['full_name']) $changed = true;
                if ($existing['title'] !== $data['title']) $changed = true;
                if ($existing['division'] !== $data['division']) $changed = true;
                if ($existing['role_type'] !== $data['role_type']) $changed = true;
                if ($existing['is_default'] != $data['is_default']) $changed = true;
                if ($existing['is_active'] != $data['is_active']) $changed = true;

                if (!$changed) {
                    echo json_encode(['status' => 'error', 'code' => 'NO_CHANGES', 'message' => 'No changes detected.']);
                    exit;
                }
            }

            if ($model->update($id, $data)) {
                echo json_encode(['status' => 'success', 'message' => 'Signatory updated']);
            } else {
                throw new Exception('Failed to update signatory');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = intval($_POST['signatory_id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid ID');

            if ($model->softDelete($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Signatory deleted']);
            } else {
                throw new Exception('Failed to delete signatory');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
