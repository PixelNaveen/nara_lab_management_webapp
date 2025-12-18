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

    // ========== FETCH ALL SAMPLES ==========
    case 'fetchAll':
        $statusFilter = $_POST['statusFilter'] ?? $_GET['statusFilter'] ?? 'all';
        $searchTerm = trim($_POST['searchTerm'] ?? $_GET['searchTerm'] ?? '');

        $samples = $model->getAllSamples($statusFilter, $searchTerm);
        $counts = $model->getStatusCounts();

        echo json_encode([
            'status' => 'success',
            'data' => $samples,
            'counts' => $counts
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

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>