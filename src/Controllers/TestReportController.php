<?php

/**
 * Test Report Controller
 * Laboratory Management System
 *
 * Handles AJAX requests for test report generation:
 * - fetchCompletedSamples: List samples ready for reports
 * - preview: Get report data for preview
 * - generate: Create and save a report
 * - getReport: Retrieve a saved report
 * - printReport: Serve print-ready HTML
 * - getSignatories: List available signatories
 *
 * @version 1.0
 */

require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Models/TestReportModel.php';

$model = new TestReportModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$currentUser = $_SESSION['fullname'] ?? 'Unknown';
$currentUserId = $_SESSION['user_id'] ?? null;

switch ($action) {

    // ========== FETCH COMPLETED SAMPLES ==========
    case 'fetchCompletedSamples':
        try {
            $filters = [
                'search'      => trim($_POST['search'] ?? $_GET['search'] ?? ''),
                'date_preset' => trim($_POST['date_preset'] ?? $_GET['date_preset'] ?? '')
            ];

            $samples = $model->getCompletedSamples($filters);

            echo json_encode([
                'status' => 'success',
                'data'   => $samples,
                'count'  => count($samples)
            ]);
        } catch (Exception $e) {
            error_log("TestReport - fetchCompletedSamples Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch samples'
            ]);
        }
        break;

    // ========== PREVIEW REPORT DATA ==========
    case 'preview':
        try {
            $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            $data = $model->getReportData($sampleId);

            if (!$data) {
                throw new Exception('Sample not found or not completed');
            }

            // Add signatories
            $data['signatories'] = [
                'all'      => $model->getSignatories(),
                'defaults' => $model->getDefaultSignatories()
            ];

            echo json_encode([
                'status' => 'success',
                'data'   => $data
            ]);
        } catch (Exception $e) {
            error_log("TestReport - preview Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GENERATE REPORT ==========
    case 'generate':
        try {
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $signatoryLeftId = intval($_POST['signatory_left_id'] ?? 0);
            $signatoryRightId = intval($_POST['signatory_right_id'] ?? 0);
            $layoutType = trim($_POST['layout_type'] ?? 'single');
            $analysisStart = trim($_POST['analysis_start_date'] ?? '');
            $analysisEnd = trim($_POST['analysis_end_date'] ?? '');
            $isDrawnByNara = intval($_POST['is_drawn_by_nara'] ?? 0);

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }
            if ($signatoryLeftId <= 0 || $signatoryRightId <= 0) {
                throw new Exception('Both signatories must be selected');
            }

            // Get report data to determine type
            $reportData = $model->getReportData($sampleId);
            if (!$reportData) {
                throw new Exception('Sample not found or not completed');
            }

            // Parse item positions
            $itemPositions = [];
            $positionsJson = $_POST['item_positions'] ?? '[]';
            $positions = json_decode($positionsJson, true);
            if (is_array($positions)) {
                $itemPositions = $positions;
            } else {
                // Default: each item gets its own position
                foreach ($reportData['items'] as $idx => $item) {
                    $itemPositions[] = [
                        'sample_item_id' => $item['sample_item_id'],
                        'page_number'    => 1,
                        'column_position' => $idx + 1
                    ];
                }
            }

            $reportIds = $model->generateReport([
                'sample_id'           => $sampleId,
                'report_type'         => $reportData['report_type'],
                'layout_type'         => $layoutType,
                'signatory_left_id'   => $signatoryLeftId,
                'signatory_right_id'  => $signatoryRightId,
                'item_positions'      => $itemPositions,
                'analysis_start_date' => $analysisStart ?: null,
                'analysis_end_date'   => $analysisEnd ?: null,
                'is_drawn_by_nara'    => $isDrawnByNara,
                'generated_by'        => $currentUserId
            ]);

            if ($reportIds && is_array($reportIds) && count($reportIds) > 0) {
                echo json_encode([
                    'status'     => 'success',
                    'message'    => 'Report generated successfully',
                    'report_ids' => $reportIds,
                    'report_id'  => $reportIds[0] // Backward compatible
                ]);
            } else {
                throw new Exception('Failed to generate report');
            }
        } catch (Exception $e) {
            error_log("TestReport - generate Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== GET SAVED REPORT ==========
    case 'getReport':
        try {
            $reportId = intval($_POST['report_id'] ?? $_GET['report_id'] ?? 0);

            if ($reportId <= 0) {
                throw new Exception('Invalid report ID');
            }

            $report = $model->getSavedReport($reportId);
            if (!$report) {
                throw new Exception('Report not found');
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $report
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
            $roleType = trim($_POST['role_type'] ?? $_GET['role_type'] ?? '');
            $signatories = $model->getSignatories($roleType ?: null);
            $defaults = $model->getDefaultSignatories();

            echo json_encode([
                'status'     => 'success',
                'signatories' => $signatories,
                'defaults'   => $defaults
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch signatories'
            ]);
        }
        break;

    // ========== INCREMENT PRINT COUNT ==========
    case 'recordPrint':
        try {
            $reportId = intval($_POST['report_id'] ?? 0);
            if ($reportId <= 0) {
                throw new Exception('Invalid report ID');
            }
            $model->incrementPrintCount($reportId);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== UNKNOWN ACTION ==========
    default:
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid action: ' . htmlspecialchars($action)
        ]);
        break;
}
