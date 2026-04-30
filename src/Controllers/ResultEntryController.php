<?php

/**
 * Result Entry Controller
 * Laboratory Management System
 *
 * Handles AJAX requests for result entry:
 * - fetchSamples: Load samples for the results table
 * - getForm: Load the result entry form data for a sample
 * - saveResults: Validate and upsert test results
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

require_once __DIR__ . '/../Models/ResultEntryModel.php';

// CSRF Validation (Skip for read-only actions like getForm/fetchSamples if desired, but enforce for saves)
function validateCSRF()
{
    $clientToken = $_POST['csrf_token'] ?? '';
    // If you have a formal CSRF system in your session, validate it here. 
    // Example: if (!isset($_SESSION['csrf_token']) || $clientToken !== $_SESSION['csrf_token']) { throw new Exception('Invalid CSRF token'); }
}

$model = new ResultEntryModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$currentUser = $_SESSION['fullname'] ?? 'Unknown';
$currentUserId = $_SESSION['user_id'] ?? null;

switch ($action) {

    // ========== FETCH ALL SAMPLES FOR RESULTS TABLE ==========
    case 'fetchSamples':
        try {
            $filters = [
                'search'      => trim($_POST['search'] ?? ''),
                'status'      => trim($_POST['status'] ?? 'all'),
                'date_preset' => trim($_POST['date_preset'] ?? '')
            ];

            $samples = $model->getSamplesForResults($filters);

            echo json_encode([
                'status' => 'success',
                'data'   => $samples,
                'count'  => count($samples)
            ]);
        } catch (Exception $e) {
            error_log("Result Entry - fetchSamples Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch samples'
            ]);
        }
        break;

    // ========== GET FORM DATA FOR A SPECIFIC SAMPLE ==========
    case 'getForm':
        try {
            $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            $formData = $model->getFormData($sampleId);

            if (!$formData) {
                throw new Exception('Sample not found');
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $formData
            ]);
        } catch (Exception $e) {
            error_log("Result Entry - getForm Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    // ========== SAVE RESULTS ==========
    case 'saveResults':
        try {
            validateCSRF();
            $sampleId = intval($_POST['sample_id'] ?? 0);
            $resultsJson = $_POST['results'] ?? '';

            if ($sampleId <= 0) {
                throw new Exception('Invalid sample ID');
            }

            if (empty($resultsJson)) {
                throw new Exception('No results data provided');
            }

            $results = json_decode($resultsJson, true);
            if (!is_array($results) || empty($results)) {
                throw new Exception('Invalid results data format');
            }

            $savedCount = 0;
            $errors = [];

            foreach ($results as $index => $row) {
                $sampleTestId = intval($row['sample_test_id'] ?? 0);
                $sampleItemId = intval($row['sample_item_id'] ?? 0);
                $parameterId  = intval($row['parameter_id'] ?? 0);
                $variantId    = !empty($row['variant_id']) ? intval($row['variant_id']) : null;

                if ($sampleTestId <= 0 || $parameterId <= 0) {
                    $errors[] = "Row $index: Missing test or parameter ID";
                    continue;
                }

                // Verify this test belongs to the correct sample
                $testSampleId = $model->getSampleIdFromTest($sampleTestId);
                if ($testSampleId !== $sampleId) {
                    $errors[] = "Row $index: Test does not belong to this sample";
                    continue;
                }

                // Server-side: re-read result_mode and espc_applicable from test_parameters
                $paramConfig = $model->getParameterConfig($parameterId);
                if (!$paramConfig) {
                    $errors[] = "Row $index: Parameter config not found";
                    continue;
                }

                $resultMode     = $paramConfig['result_mode'] ?: 'numeric_or_ND';
                $espcApplicable = (int) ($paramConfig['espc_applicable'] ?? 0);

                // Process based on result_mode
                $resultValue    = null;
                $hasEspc        = 0;
                $resultDisplay  = '';
                $resultType     = trim($row['result_type'] ?? '');

                if ($resultMode === 'numeric_or_ND') {
                    if ($resultType === 'ND') {
                        $resultValue = 'ND';
                        $resultDisplay = 'ND';
                    } elseif ($resultType === 'numeric') {
                        $val = trim($row['result_value'] ?? '');
                        if ($val === '') {
                            $errors[] = "Row $index: Value required";
                            continue;
                        }
                        $resultValue = $val;
                        // Format display: turn ^5 into <sup>5</sup>
                        $resultDisplay = preg_replace('/\^([0-9A-Za-z-]+)/', '<sup>$1</sup>', $val);
                    } else {
                        // Skip rows with no result entered
                        continue;
                    }

                    // ESPC only if applicable
                    if ($espcApplicable && !empty($row['has_espc'])) {
                        $hasEspc = 1;
                    }
                } elseif ($resultMode === 'present_or_absent') {
                    if (in_array($resultType, ['Present', 'Absent'])) {
                        $resultValue = $resultType;
                        $resultDisplay = $resultType;
                    } else {
                        // Present/Absent is mandatory in new validation rules
                        $errors[] = "Row $index: Selection (Present/Absent) is required";
                        continue;
                    }
                }

                // Build upsert data
                $data = [
                    'sample_test_id'     => $sampleTestId,
                    'sample_item_id'     => $sampleItemId,
                    'parameter_id'       => $parameterId,
                    'variant_id'         => $variantId,
                    'result_mode'        => $resultMode,
                    'result_value'       => $resultValue,
                    'has_espc'           => $hasEspc,
                    'result_display'     => $resultDisplay,
                    'entered_by'         => $currentUserId
                ];

                if ($model->upsertResult($data)) {
                    $savedCount++;
                } else {
                    $errors[] = "Row $index: Failed to save result";
                }
            }

            // Save user-provided analysis dates (from date pickers)
            $analysisStartDate = trim($_POST['analysis_start_date'] ?? '');
            $analysisEndDate   = trim($_POST['analysis_end_date'] ?? '');
            $today = date('Y-m-d');

            // Backend Date Validation
            if (!empty($analysisStartDate) && $analysisStartDate > $today) {
                throw new Exception("Analysis start date cannot be in the future");
            }
            if (!empty($analysisEndDate) && $analysisEndDate > $today) {
                throw new Exception("Analysis end date cannot be in the future");
            }
            if (!empty($analysisStartDate) && !empty($analysisEndDate) && $analysisEndDate < $analysisStartDate) {
                throw new Exception("Analysis end date cannot be earlier than start date");
            }

            if ($analysisStartDate !== '' || $analysisEndDate !== '') {
                $model->updateAnalysisDates(
                    $sampleId,
                    $analysisStartDate ?: null,
                    $analysisEndDate ?: null
                );
            }

            // Auto-fill dates as fallback (only fills NULLs)
            $model->updateAnalysisDatesOnResultSave($sampleId);

            // Check if all results are now filled → auto-complete
            $statusChanged = false;
            $newStatus = null;

            if ($model->areAllResultsFilled($sampleId)) {
                $model->markSampleCompleted($sampleId, $currentUser);
                $statusChanged = true;
                $newStatus = 'Completed';
            }

            echo json_encode([
                'status'         => 'success',
                'message'        => "$savedCount result(s) saved successfully",
                'saved_count'    => $savedCount,
                'errors'         => $errors,
                'status_changed' => $statusChanged,
                'new_status'     => $newStatus
            ]);
        } catch (Exception $e) {
            error_log("Result Entry - saveResults Error: " . $e->getMessage());
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
