<?php

/**
 * Sample Names Controller
 * Routes AJAX requests for sample name management
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 1.0
 */

session_start();

require_once __DIR__ . '/../Models/sample-names-model.php';
require_once __DIR__ . '/../Helpers/functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized']);
}

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid security token']);
    }
}

// Initialize model
try {
    $model = new SampleNamesModel();
} catch (Exception $e) {
    logError($e->getMessage(), 'SampleNamesController');
    sendJsonResponse(['success' => false, 'message' => 'Database connection error']);
}

// Route action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'getAll':
        handleGetAll($model);
        break;
    case 'getCategories':
        handleGetCategories($model);
        break;
    case 'getCategoryStats':
        handleGetCategoryStats($model);
        break;
    case 'insert':
        handleInsert($model);
        break;
    case 'update':
        handleUpdate($model);
        break;
    case 'delete':
        handleDelete($model);
        break;
    case 'getById':
        handleGetById($model);
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Invalid action']);
}

// =================== HANDLERS ===================

function handleGetAll($model)
{
    try {
        $names = $model->getAllSampleNames();
        sendJsonResponse(['success' => true, 'data' => $names]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::getAll');
        sendJsonResponse(['success' => false, 'message' => 'Failed to load sample names']);
    }
}

function handleGetCategories($model)
{
    try {
        $categories = $model->getCategories();
        sendJsonResponse(['success' => true, 'data' => $categories]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::getCategories');
        sendJsonResponse(['success' => false, 'message' => 'Failed to load categories']);
    }
}

function handleGetCategoryStats($model)
{
    try {
        $stats = $model->getCategoryStats();
        sendJsonResponse(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::getCategoryStats');
        sendJsonResponse(['success' => false, 'message' => 'Failed to load category stats']);
    }
}

function handleInsert($model)
{
    try {
        $name = trim($_POST['sample_name'] ?? '');
        $categoryId = intval($_POST['category_id'] ?? 0);
        $isSlabAccredited = isset($_POST['is_slab_accredited']) ? intval($_POST['is_slab_accredited']) : 0;

        if (empty($name)) {
            sendJsonResponse(['success' => false, 'message' => 'Sample name is required']);
        }
        if ($categoryId <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Category is required']);
        }

        // Check duplicate
        if ($model->isDuplicate($name)) {
            sendJsonResponse(['success' => false, 'message' => 'A sample name with this name already exists']);
        }

        $id = $model->insertSampleName($name, $categoryId, $isSlabAccredited);
        sendJsonResponse(['success' => true, 'message' => 'Sample name added successfully', 'id' => $id]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::insert');
        sendJsonResponse(['success' => false, 'message' => 'Failed to add sample name']);
    }
}

function handleUpdate($model)
{
    try {
        $id = intval($_POST['sample_name_id'] ?? 0);
        $name = trim($_POST['sample_name'] ?? '');
        $categoryId = intval($_POST['category_id'] ?? 0);
        $isSlabAccredited = isset($_POST['is_slab_accredited']) ? intval($_POST['is_slab_accredited']) : 0;

        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Invalid sample name ID']);
        }
        if (empty($name)) {
            sendJsonResponse(['success' => false, 'message' => 'Sample name is required']);
        }
        if ($categoryId <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Category is required']);
        }

        // Check duplicate (exclude self)
        if ($model->isDuplicate($name, $id)) {
            sendJsonResponse(['success' => false, 'message' => 'A sample name with this name already exists']);
        }

        $model->updateSampleName($id, $name, $categoryId, $isSlabAccredited);
        sendJsonResponse(['success' => true, 'message' => 'Sample name updated successfully']);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::update');
        sendJsonResponse(['success' => false, 'message' => 'Failed to update sample name']);
    }
}

function handleDelete($model)
{
    try {
        $id = intval($_POST['sample_name_id'] ?? 0);

        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Invalid sample name ID']);
        }

        $result = $model->deleteSampleName($id);
        sendJsonResponse($result);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::delete');
        sendJsonResponse(['success' => false, 'message' => 'Failed to delete sample name']);
    }
}

function handleGetById($model)
{
    try {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
        }

        $data = $model->getSampleNameById($id);
        if (!$data) {
            sendJsonResponse(['success' => false, 'message' => 'Sample name not found']);
        }

        sendJsonResponse(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        logError($e->getMessage(), 'SampleNames::getById');
        sendJsonResponse(['success' => false, 'message' => 'Failed to load sample name']);
    }
}
