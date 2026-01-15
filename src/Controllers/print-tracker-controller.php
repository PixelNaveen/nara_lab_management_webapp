<?php
/**
 * Print Tracker Controller
 * Handles silent print logging (called via AJAX after PDF generation)
 * 
 * NO UI FEEDBACK - Always returns success
 * Logs print actions silently in background
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../Models/print-history-model.php';
header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['fullname'])) {
    // Silent fail - don't break user experience
    echo json_encode(['status' => 'success']); // Return success anyway
    exit;
}

$model = new PrintHistoryModel();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'logPrint':
        try {
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $formType = trim($_POST['form_type'] ?? '');
            $printFormat = trim($_POST['print_format'] ?? 'PDF');
            $formsIncluded = $_POST['forms_included'] ?? [];

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            if (!in_array($formType, ['SAF', 'ACKNOWLEDGEMENT', 'ANALYST'])) {
                throw new Exception('Invalid form type');
            }

            // Parse forms_included if it's a string
            if (is_string($formsIncluded)) {
                $formsIncluded = explode(',', $formsIncluded);
                $formsIncluded = array_map('trim', $formsIncluded);
            }

            // Log silently
            $model->logPrint(
                $sampleId,
                $formType,
                $_SESSION['fullname'],
                $_SESSION['user_id'],
                $printFormat,
                $formsIncluded
            );

            // ALWAYS return success (even if logging fails)
            echo json_encode(['status' => 'success']);
            
        } catch (Exception $e) {
            // Silent fail - log error but return success to user
            error_log("Print Tracker Error: " . $e->getMessage());
            echo json_encode(['status' => 'success']);
        }
        break;

    default:
        // Silent fail for invalid actions
        echo json_encode(['status' => 'success']);
}