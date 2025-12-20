<?php
session_start();
require_once __DIR__ . '/../Models/sample-records-model.php';
header('Content-Type: application/json');

// Check if user is logged in (adjust based on your auth system)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['fullname'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$model = new SampleStatusModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ========== FETCH ALL SAMPLES WITH FILTERS ==========
    case 'fetchAll':
        $filters = [
            'search' => trim($_POST['search'] ?? $_GET['search'] ?? ''),
            'status' => trim($_POST['status'] ?? $_GET['status'] ?? 'all'),
            'date_from' => trim($_POST['date_from'] ?? $_GET['date_from'] ?? ''),
            'date_to' => trim($_POST['date_to'] ?? $_GET['date_to'] ?? '')
        ];

        $samples = $model->getAllSamplesAdvanced($filters);
        $counts = $model->getStatusCounts();
        
        // Calculate grand total for filtered results
        $grandTotal = 0;
        foreach ($samples as $sample) {
            $grandTotal += floatval($sample['grand_total']);
        }

        echo json_encode([
            'status' => 'success',
            'data' => $samples,
            'counts' => $counts,
            'grand_total' => $grandTotal
        ]);
        break;

    // ========== UPDATE STATUS ==========
    case 'updateStatus':
        $sampleId = intval($_POST['sample_id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $updatedBy = $_SESSION['fullname'];

        // Validation
        if ($sampleId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid sample ID']);
            exit;
        }

        if (empty($newStatus)) {
            echo json_encode(['status' => 'error', 'message' => 'Status is required']);
            exit;
        }

        if (!$model->isValidStatus($newStatus)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status value']);
            exit;
        }

        // Check if sample exists
        $sample = $model->getSampleById($sampleId);
        if (!$sample) {
            echo json_encode(['status' => 'error', 'message' => 'Sample not found']);
            exit;
        }

        // Update status
        if ($model->updateSampleStatus($sampleId, $newStatus, $updatedBy, $notes)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Status updated successfully',
                'data' => [
                    'sample_id' => $sampleId,
                    'new_status' => $newStatus,
                    'updated_by' => $updatedBy,
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
        break;

    // ========== GET STATUS COUNTS ==========
    case 'getCounts':
        $counts = $model->getStatusCounts();
        echo json_encode([
            'status' => 'success',
            'counts' => $counts
        ]);
        break;

    // ========== GET SINGLE SAMPLE ==========
    case 'getSample':
        $sampleId = intval($_POST['sample_id'] ?? $_GET['sample_id'] ?? 0);

        if ($sampleId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid sample ID']);
            exit;
        }

        $sample = $model->getSampleById($sampleId);

        if ($sample) {
            echo json_encode([
                'status' => 'success',
                'data' => $sample
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sample not found']);
        }
        break;

    // ========== GET STATISTICS ==========
    case 'getStats':
        $stats = $model->getStatistics();
        echo json_encode([
            'status' => 'success',
            'stats' => $stats
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>