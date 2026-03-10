<?php
// src/Models/CertificateModel.php - FINAL COMPLETE VERSION

require_once __DIR__ . '/../../Config/Database.php';

class CertificateModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Get all certificates with filters
     */
    public function getAllCertificates($filters = [])
    {
        try {
            $sql = "SELECT 
                        certificate_id,
                        certificate_code,
                        certificate_name,
                        valid_from,
                        valid_to,
                        issued_date,
                        is_current,
                        status,
                        scope_description,
                        notes,
                        created_at,
                        created_by,
                        DATEDIFF(valid_to, CURDATE()) AS days_until_expiry,
                        CASE
                            WHEN valid_to < CURDATE() THEN 'expired'
                            WHEN DATEDIFF(valid_to, CURDATE()) <= 30 THEN 'critical'
                            WHEN DATEDIFF(valid_to, CURDATE()) <= 90 THEN 'warning'
                            ELSE 'valid'
                        END AS expiry_status
                    FROM accreditation_certificates
                    WHERE is_deleted = 0";
            
            // Apply status filter
            if (isset($filters['status']) && $filters['status'] !== 'all') {
                $status = $this->conn->real_escape_string($filters['status']);
                $sql .= " AND status = '{$status}'";
            }
            
            // Search filter
            if (isset($filters['search']) && trim($filters['search']) !== '') {
                $search = $this->conn->real_escape_string($filters['search']);
                $sql .= " AND (certificate_code LIKE '%{$search}%' 
                         OR certificate_name LIKE '%{$search}%')";
            }
            
            // Sorting
            $sortBy = $filters['sort'] ?? 'expiry';
            switch ($sortBy) {
                case 'code':
                    $sql .= " ORDER BY certificate_code ASC";
                    break;
                case 'name':
                    $sql .= " ORDER BY certificate_name ASC";
                    break;
                case 'date':
                    $sql .= " ORDER BY created_at DESC";
                    break;
                default: // expiry
                    $sql .= " ORDER BY is_current DESC, valid_to ASC";
            }
            
            $result = $this->conn->query($sql);
            $certificates = [];
            
            while ($row = $result->fetch_assoc()) {
                $certificates[] = $row;
            }
            
            return [
                'data' => $certificates,
                'total' => count($certificates)
            ];
        } catch (Exception $e) {
            error_log("CertificateModel::getAllCertificates Error: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Get certificate by ID
     */
    public function getCertificateById($certificateId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM accreditation_certificates 
                 WHERE certificate_id = ? AND is_deleted = 0"
            );
            $stmt->bind_param("i", $certificateId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("CertificateModel::getCertificateById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if certificate code exists (case-insensitive)
     */
    public function certificateExists($certificateCode, $excludeId = null)
    {
        try {
            $sql = "SELECT certificate_id FROM accreditation_certificates 
                    WHERE LOWER(certificate_code) = LOWER(?) 
                      AND is_deleted = 0";
            
            if ($excludeId) {
                $sql .= " AND certificate_id != ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("si", $certificateCode, $excludeId);
            } else {
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $certificateCode);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("CertificateModel::certificateExists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert new certificate
     */
    public function insertCertificate($data)
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO accreditation_certificates 
                (certificate_code, certificate_name, valid_from, valid_to, 
                 issued_date, status, scope_description, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param("sssssssss",
                $data['certificate_code'],
                $data['certificate_name'],
                $data['valid_from'],
                $data['valid_to'],
                $data['issued_date'],
                $data['status'],
                $data['scope_description'],
                $data['notes'],
                $data['created_by']
            );
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("CertificateModel::insertCertificate Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update certificate
     */
    public function updateCertificate($certificateId, $data)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE accreditation_certificates
                 SET certificate_code = ?,
                     certificate_name = ?,
                     valid_from = ?,
                     valid_to = ?,
                     issued_date = ?,
                     status = ?,
                     scope_description = ?,
                     notes = ?
                 WHERE certificate_id = ? AND is_deleted = 0"
            );
            
            $stmt->bind_param("ssssssssi",
                $data['certificate_code'],
                $data['certificate_name'],
                $data['valid_from'],
                $data['valid_to'],
                $data['issued_date'],
                $data['status'],
                $data['scope_description'],
                $data['notes'],
                $certificateId
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CertificateModel::updateCertificate Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set certificate as current (unset all others first)
     */
    public function setAsCurrent($certificateId)
    {
        try {
            $this->conn->begin_transaction();
            
            // Unset all current flags
            $this->conn->query("UPDATE accreditation_certificates SET is_current = 0");
            
            // Set this one as current and activate it
            $stmt = $this->conn->prepare(
                "UPDATE accreditation_certificates 
                 SET is_current = 1, status = 'active'
                 WHERE certificate_id = ?"
            );
            $stmt->bind_param("i", $certificateId);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("CertificateModel::setAsCurrent Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete certificate
     */
    public function softDeleteCertificate($certificateId)
    {
        try {
            // Don't allow deleting current certificate
            $cert = $this->getCertificateById($certificateId);
            if ($cert && $cert['is_current'] == 1) {
                throw new Exception('Cannot delete current certificate. Set another certificate as current first.');
            }
            
            $stmt = $this->conn->prepare(
                "UPDATE accreditation_certificates 
                 SET is_deleted = 1, is_current = 0
                 WHERE certificate_id = ?"
            );
            $stmt->bind_param("i", $certificateId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CertificateModel::softDeleteCertificate Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => 0,
                'active' => 0,
                'expiring_soon' => 0,
                'expired' => 0
            ];
            
            $result = $this->conn->query(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN DATEDIFF(valid_to, CURDATE()) <= 90 
                             AND DATEDIFF(valid_to, CURDATE()) >= 0 
                             THEN 1 ELSE 0 END) as expiring_soon,
                    SUM(CASE WHEN valid_to < CURDATE() THEN 1 ELSE 0 END) as expired
                 FROM accreditation_certificates 
                 WHERE is_deleted = 0"
            );
            
            if ($row = $result->fetch_assoc()) {
                $stats = $row;
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("CertificateModel::getStatistics Error: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'expiring_soon' => 0, 'expired' => 0];
        }
    }

    /**
     * Check if deleted record exists
     */
    public function findDeletedByCode($certificateCode)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT certificate_id FROM accreditation_certificates 
                 WHERE LOWER(certificate_code) = LOWER(?) AND is_deleted = 1"
            );
            $stmt->bind_param("s", $certificateCode);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Reactivate deleted certificate
     */
    public function reactivateCertificate($certificateId, $data)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE accreditation_certificates
                 SET is_deleted = 0,
                     certificate_name = ?,
                     valid_from = ?,
                     valid_to = ?,
                     issued_date = ?,
                     status = ?,
                     scope_description = ?,
                     notes = ?
                 WHERE certificate_id = ?"
            );
            
            $stmt->bind_param("sssssssi",
                $data['certificate_name'],
                $data['valid_from'],
                $data['valid_to'],
                $data['issued_date'],
                $data['status'],
                $data['scope_description'],
                $data['notes'],
                $certificateId
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CertificateModel::reactivateCertificate Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Auto-update expired certificates
     */
    public function updateExpiredStatus()
    {
        try {
            $this->conn->query(
                "UPDATE accreditation_certificates 
                 SET status = 'expired' 
                 WHERE valid_to < CURDATE() 
                   AND status != 'expired' 
                   AND is_deleted = 0"
            );
            return true;
        } catch (Exception $e) {
            error_log("CertificateModel::updateExpiredStatus Error: " . $e->getMessage());
            return false;
        }
    }
}
?>