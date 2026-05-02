<?php

/**
 * Sample Records Controller
 * Laboratory Management System
 * 
 * Handles all AJAX requests for sample records including:
 * - Fetching samples with filters
 * - Updating sample status
 * - Updating payment status with reference numbers
 * - Getting counts and statistics
 * 
 * @version 2.0 - Payment System Integrated
 */

require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
require_once __DIR__ . '/../Models/SampleStatusModel.php';
header('Content-Type: application/json');

// ==================== AUTHENTICATION CHECK ====================

if (!isset($_SESSION['user_id']) || !isset($_SESSION['fullname'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in.'
    ]);
    exit;
}

// ==================== INITIALIZE ====================

$model = new SampleStatusModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$currentUser = $_SESSION['fullname'];

// ==================== ACTIONS ====================

switch ($action) {

    // ========== FETCH ALL SAMPLES WITH FILTERS ==========
    case 'fetchAll':
        try {
            $filters = [
                'search' => trim($_POST['search'] ?? $_GET['search'] ?? ''),
                'status' => trim($_POST['status'] ?? $_GET['status'] ?? 'all'),
                'payment_status' => trim($_POST['payment_status'] ?? $_GET['payment_status'] ?? 'all'),
                'date_from' => trim($_POST['date_from'] ?? $_GET['date_from'] ?? ''),
                'date_to' => trim($_POST['date_to'] ?? $_GET['date_to'] ?? '')
            ];

            $samples = $model->getAllSamplesAdvanced($filters);
            $counts = $model->getStatusCounts();
            $paymentCounts = $model->getPaymentCounts();

            // Calculate grand total for filtered results
            $grandTotal = 0;
            $paidTotal = 0;
            $unpaidTotal = 0;

            foreach ($samples as $sample) {
                $amount = floatval($sample['grand_total']);
                $grandTotal += $amount;

                if ($sample['payment_status'] === 'Paid') {
                    $paidTotal += $amount;
                } else {
                    $unpaidTotal += $amount;
                }
            }

            echo json_encode([
                'status' => 'success',
                'data' => $samples,
                'counts' => $counts,
                'payment_counts' => $paymentCounts,
                'totals' => [
                    'grand_total' => $grandTotal,
                    'paid_total' => $paidTotal,
                    'unpaid_total' => $unpaidTotal
                ]
            ]);
        } catch (Exception $e) {
            error_log("Fetch All Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch samples'
            ]);
        }
        break;

    // ========== UPDATE SAMPLE STATUS ==========
    case 'updateStatus':
        try {
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $newStatus = trim($_POST['new_status'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            // Validation
            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            if (empty($newStatus)) {
                throw new Exception('Status is required');
            }

            if (!$model->isValidStatus($newStatus)) {
                throw new Exception('Invalid status value');
            }

            // Check if sample exists
            $sample = $model->getSampleById($sampleId);
            if (!$sample) {
                throw new Exception('Sample not found');
            }

            // Update status
            if ($model->updateSampleStatus($sampleId, $newStatus, $currentUser, $notes)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Status updated successfully',
                    'data' => [
                        'sample_id' => $sampleId,
                        'new_status' => $newStatus,
                        'updated_by' => $currentUser,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                throw new Exception('Failed to update status');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== UPDATE PAYMENT STATUS (NEW) ==========
    case 'updatePayment':
        try {
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $newPaymentStatus = trim($_POST['payment_status'] ?? '');
            $referenceNumber = trim($_POST['reference_number'] ?? '');
            $paymentDate = trim($_POST['payment_date'] ?? '');

            // Validation
            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            if (empty($newPaymentStatus)) {
                throw new Exception('Payment status is required');
            }

            if (!$model->isValidPaymentStatus($newPaymentStatus)) {
                throw new Exception('Invalid payment status value');
            }

            // Check if sample exists
            $sample = $model->getSampleById($sampleId);
            if (!$sample) {
                throw new Exception('Sample not found');
            }

            // Validate reference number for Paid status
            if ($newPaymentStatus === 'Paid') {
                if (empty($referenceNumber)) {
                    throw new Exception('Reference number is required when marking as Paid');
                }

                if (strlen($referenceNumber) > 100) {
                    throw new Exception('Reference number too long (maximum 100 characters)');
                }

                // Validate reference number format: digits + allowed symbols only
                if (!preg_match('/^[0-9_\-\/\.,]+$/', $referenceNumber)) {
                    throw new Exception('Reference number can only contain digits and symbols: _ / - , .');
                }

                if (empty($paymentDate)) {
                    throw new Exception('Payment date is required when marking as Paid');
                }

                // Validate payment date is not in the future
                $tz = new DateTimeZone('Asia/Colombo');
                $today = new DateTime('now', $tz);
                $today->setTime(23, 59, 59); // Allow the full today
                $submittedDate = new DateTime($paymentDate, $tz);
                if ($submittedDate > $today) {
                    throw new Exception('Payment date cannot be in the future');
                }
            }

            // Update payment status
            $result = $model->updatePaymentStatus(
                $sampleId,
                $newPaymentStatus,
                $referenceNumber,
                $currentUser,
                $paymentDate
            );

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $result['message'],
                    'data' => [
                        'sample_id' => $sampleId,
                        'old_payment_status' => $result['old_status'] ?? null,
                        'new_payment_status' => $newPaymentStatus,
                        'updated_by' => $currentUser,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET PAYMENT INFO (NEW) ==========
    case 'getPaymentInfo':
        try {
            $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            $paymentInfo = $model->getPaymentInfo($sampleId);

            if ($paymentInfo) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $paymentInfo
                ]);
            } else {
                throw new Exception('Sample not found');
            }
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET STATUS COUNTS ==========
    case 'getCounts':
        try {
            $counts = $model->getStatusCounts();
            echo json_encode([
                'status' => 'success',
                'counts' => $counts
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch counts'
            ]);
        }
        break;

    // ========== GET PAYMENT COUNTS (NEW) ==========
    case 'getPaymentCounts':
        try {
            $paymentCounts = $model->getPaymentCounts();
            echo json_encode([
                'status' => 'success',
                'payment_counts' => $paymentCounts
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch payment counts'
            ]);
        }
        break;

    // ========== GET SINGLE SAMPLE ==========
    case 'getSample':
        try {
            $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            $sample = $model->getSampleById($sampleId);

            if ($sample) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $sample
                ]);
            } else {
                throw new Exception('Sample not found');
            }
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET STATISTICS ==========
    case 'getStats':
        try {
            $stats = $model->getStatistics();
            echo json_encode([
                'status' => 'success',
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch statistics'
            ]);
        }
        break;

    // ========== INVALID ACTION ==========
    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action specified'
        ]);
        break;
}
