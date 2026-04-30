<?php

/**
 * Invoice Controller
 * Laboratory Management System
 *
 * Handles AJAX requests for invoice generation:
 * - preview: Get invoice data for preview before saving
 * - generate: Create and save an invoice snapshot to DB
 * - getInvoice: Retrieve a saved invoice
 * - getSignatories: List available signatories
 *
 * @version 1.0
 */

session_start();
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Models/InvoiceModel.php';

$model = new InvoiceModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? null;

switch ($action) {

    // ========== FETCH SAMPLES FOR INVOICE ==========
    case 'fetchSamples':
        try {
            $filters = [
                'search' => trim($_POST['search'] ?? $_GET['search'] ?? '')
            ];
            $samples = $model->getSamplesForInvoice($filters);
            echo json_encode([
                'status' => 'success',
                'data'   => $samples
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ========== PREVIEW INVOICE DATA ==========
    case 'preview':
        try {
            $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);
            $requestDate = $_POST['request_date'] ?? $_GET['request_date'] ?? null;

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            $data = $model->getInvoiceRawData($sampleId, $requestDate);

            if (!$data) {
                throw new Exception('Sample data could not be retrieved.');
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $data
            ]);
        } catch (Exception $e) {
            error_log("Invoice - preview Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GENERATE / SAVE INVOICE ==========
    case 'generate':
        try {
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $signatoryId = intval($_POST['signatory_id'] ?? 0);
            $requestDate = $_POST['request_date'] ?? null;

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }
            if ($signatoryId <= 0) {
                throw new Exception('A signatory must be selected.');
            }

            $invoiceId = $model->saveAndFreezeInvoice($sampleId, $signatoryId, $currentUserId, $requestDate);

            echo json_encode([
                'status'     => 'success',
                'message'    => 'Invoice generated successfully',
                'invoice_id' => $invoiceId
            ]);
        } catch (Exception $e) {
            error_log("Invoice - generate Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET SAVED INVOICE ==========
    case 'getInvoice':
        try {
            $invoiceId = intval($_POST['invoice_id'] ?? $_GET['invoice_id'] ?? 0);

            if ($invoiceId <= 0) {
                throw new Exception('Invalid invoice ID');
            }

            $invoice = $model->getInvoiceById($invoiceId);
            if (!$invoice) {
                throw new Exception('Invoice not found');
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $invoice
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET SIGNATORIES ==========
    case 'getSignatories':
        try {
            $signatories = $model->getActiveSignatories();

            echo json_encode([
                'status'      => 'success',
                'signatories' => $signatories
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch signatories'
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid action: ' . htmlspecialchars($action)
        ]);
        break;
}
