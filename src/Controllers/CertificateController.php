<?php
// src/Controllers/CertificateController.php - FINAL COMPLETE VERSION

session_start();

require_once __DIR__ . '/../Models/CertificateModel.php';
header('Content-Type: application/json');

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getStatistics'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new CertificateModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ========== FETCH ALL CERTIFICATES ==========
        case 'fetchAll':
            $filters = [];
            
            if (isset($_POST['status']) && $_POST['status'] !== '') {
                $filters['status'] = $_POST['status'];
            }
            
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }
            
            if (isset($_POST['sort']) && $_POST['sort'] !== '') {
                $filters['sort'] = $_POST['sort'];
            }
            
            // Auto-update expired certificates
            $model->updateExpiredStatus();
            
            $result = $model->getAllCertificates($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'total' => $result['total']
            ]);
            break;

        // ========== GET STATISTICS ==========
        case 'getStatistics':
            // Auto-update expired certificates before getting stats
            $model->updateExpiredStatus();
            
            $stats = $model->getStatistics();
            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
            break;

        // ========== INSERT CERTIFICATE ==========
        case 'insert':
            $certificateCode = strtoupper(trim($_POST['certificate_code'] ?? ''));
            $certificateName = trim($_POST['certificate_name'] ?? '');
            $validFrom = trim($_POST['valid_from'] ?? '');
            $validTo = trim($_POST['valid_to'] ?? '');
            $issuedDate = trim($_POST['issued_date'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $scopeDescription = trim($_POST['scope_description'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $isCurrent = isset($_POST['is_current']) ? 1 : 0;
            
            $createdBy = $_SESSION['user_name'] ?? $_SESSION['fullname'] ?? 'admin';
            
            // Validation
            if ($certificateCode === '') {
                throw new Exception('Certificate code is required');
            }
            if ($certificateName === '') {
                throw new Exception('Certificate name is required');
            }
            if ($validFrom === '') {
                throw new Exception('Valid from date is required');
            }
            if ($validTo === '') {
                throw new Exception('Valid to date is required');
            }
            
            // Date validation
            if (strtotime($validTo) <= strtotime($validFrom)) {
                throw new Exception('Expiry date must be after start date');
            }
            
            // Check for deleted record
            $deletedRecord = $model->findDeletedByCode($certificateCode);
            
            if ($deletedRecord) {
                // Reactivate
                $data = [
                    'certificate_name' => $certificateName,
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo,
                    'issued_date' => $issuedDate ?: null,
                    'status' => $status,
                    'scope_description' => $scopeDescription,
                    'notes' => $notes
                ];
                
                if ($model->reactivateCertificate($deletedRecord['certificate_id'], $data)) {
                    // Set as current if requested
                    if ($isCurrent) {
                        $model->setAsCurrent($deletedRecord['certificate_id']);
                    }
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Certificate reactivated successfully'
                    ]);
                } else {
                    throw new Exception('Failed to reactivate certificate');
                }
            } else {
                // Check duplicate
                if ($model->certificateExists($certificateCode)) {
                    throw new Exception('Certificate with this code already exists');
                }
                
                // Insert new
                $data = [
                    'certificate_code' => $certificateCode,
                    'certificate_name' => $certificateName,
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo,
                    'issued_date' => $issuedDate ?: null,
                    'status' => $status,
                    'scope_description' => $scopeDescription,
                    'notes' => $notes,
                    'created_by' => $createdBy
                ];
                
                $certificateId = $model->insertCertificate($data);
                
                if ($certificateId) {
                    // Set as current if requested
                    if ($isCurrent) {
                        $model->setAsCurrent($certificateId);
                    }
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Certificate added successfully',
                        'certificate_id' => $certificateId
                    ]);
                } else {
                    throw new Exception('Failed to add certificate');
                }
            }
            break;

        // ========== UPDATE CERTIFICATE ==========
        case 'update':
            $certificateId = intval($_POST['certificate_id'] ?? 0);
            $certificateCode = strtoupper(trim($_POST['certificate_code'] ?? ''));
            $certificateName = trim($_POST['certificate_name'] ?? '');
            $validFrom = trim($_POST['valid_from'] ?? '');
            $validTo = trim($_POST['valid_to'] ?? '');
            $issuedDate = trim($_POST['issued_date'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $scopeDescription = trim($_POST['scope_description'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            if ($certificateId <= 0) {
                throw new Exception('Invalid certificate ID');
            }
            
            if ($certificateCode === '') {
                throw new Exception('Certificate code is required');
            }
            if ($certificateName === '') {
                throw new Exception('Certificate name is required');
            }
            if ($validFrom === '') {
                throw new Exception('Valid from date is required');
            }
            if ($validTo === '') {
                throw new Exception('Valid to date is required');
            }
            
            // Date validation
            if (strtotime($validTo) <= strtotime($validFrom)) {
                throw new Exception('Expiry date must be after start date');
            }
            
            // Check duplicate (excluding current certificate)
            if ($model->certificateExists($certificateCode, $certificateId)) {
                throw new Exception('Certificate with this code already exists');
            }
            
            $data = [
                'certificate_code' => $certificateCode,
                'certificate_name' => $certificateName,
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'issued_date' => $issuedDate ?: null,
                'status' => $status,
                'scope_description' => $scopeDescription,
                'notes' => $notes
            ];
            
            if ($model->updateCertificate($certificateId, $data)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Certificate updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update certificate');
            }
            break;

        // ========== SET AS CURRENT ==========
        case 'setAsCurrent':
            $certificateId = intval($_POST['certificate_id'] ?? 0);
            
            if ($certificateId <= 0) {
                throw new Exception('Invalid certificate ID');
            }
            
            if ($model->setAsCurrent($certificateId)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Certificate set as current successfully'
                ]);
            } else {
                throw new Exception('Failed to set certificate as current');
            }
            break;

        // ========== DELETE CERTIFICATE ==========
        case 'delete':
            $certificateId = intval($_POST['certificate_id'] ?? 0);
            
            if ($certificateId <= 0) {
                throw new Exception('Invalid certificate ID');
            }
            
            if ($model->softDeleteCertificate($certificateId)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Certificate deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete certificate');
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("CertificateController Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>