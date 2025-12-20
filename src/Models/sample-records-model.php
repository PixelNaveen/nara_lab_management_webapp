<?php
require_once __DIR__ . '/../../Config/Database.php';

class SampleStatusModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Get all samples with advanced filtering
     * @param array $filters
     * @return array
     */
    public function getAllSamplesAdvanced($filters = [])
    {
        $sql = "SELECT 
                    s.sample_id,
                    s.sample_code,
                    s.form_number,
                    s.received_date,
                    s.tentative_date,
                    s.grand_total,
                    s.status,
                    s.status_updated_at,
                    s.status_updated_by,
                    c.client_name,
                    c.city,
                    c.phone_primary
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                WHERE 1=1";

        $params = [];
        $types = '';

        // Multi-field search (Sample Code OR Client Name)
        if (!empty($filters['search'])) {
            $sql .= " AND (s.sample_code LIKE ? OR c.client_name LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'ss';
        }

        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        // Date range (received)
        if (!empty($filters['date_from'])) {
            $sql .= " AND s.received_date >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND s.received_date <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        // Sorting
        $sql .= " ORDER BY s.received_date DESC, s.sample_id DESC LIMIT 500";

        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $samples = [];
        while ($row = $result->fetch_assoc()) {
            $samples[] = $row;
        }

        return $samples;
    }

    /**
     * Update sample status with audit logging
     * @param int $sampleId
     * @param string $newStatus
     * @param string $updatedBy
     * @param string|null $notes
     * @return bool
     */
    public function updateSampleStatus($sampleId, $newStatus, $updatedBy, $notes = null)
    {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get current status first
            $stmt = $this->conn->prepare("SELECT status FROM samples WHERE sample_id = ?");
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentData = $result->fetch_assoc();

            if (!$currentData) {
                throw new Exception("Sample not found");
            }

            $oldStatus = $currentData['status'];

            // Don't update if status is the same
            if ($oldStatus === $newStatus) {
                $this->conn->rollback();
                return true;
            }

            // Update samples table
            $stmt = $this->conn->prepare("UPDATE samples 
                                          SET status = ?, 
                                              status_updated_at = NOW(), 
                                              status_updated_by = ?
                                          WHERE sample_id = ?");
            $stmt->bind_param("ssi", $newStatus, $updatedBy, $sampleId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update sample status");
            }

            // Insert into status log
            $stmt = $this->conn->prepare("INSERT INTO sample_status_log 
                                          (sample_id, old_status, new_status, updated_by, notes, updated_at)
                                          VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issss", $sampleId, $oldStatus, $newStatus, $updatedBy, $notes);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to log status change");
            }

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            error_log("Status update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get count of samples by status
     * @return array
     */
    public function getStatusCounts()
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM samples
                GROUP BY status";

        $result = $this->conn->query($sql);

        $counts = [
            'all' => 0,
            'Pending' => 0,
            'In Progress' => 0,
            'Completed' => 0,
            'Cancelled' => 0
        ];

        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['all'] += (int)$row['count'];
        }

        return $counts;
    }

    /**
     * Get single sample details
     * @param int $sampleId
     * @return array|null
     */
    public function getSampleById($sampleId)
    {
        $stmt = $this->conn->prepare("SELECT 
                                        s.*,
                                        c.client_name,
                                        c.city,
                                        c.phone_primary
                                      FROM samples s
                                      INNER JOIN clients c ON s.client_id = c.client_id
                                      WHERE s.sample_id = ?");
        $stmt->bind_param("i", $sampleId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Validate status value
     * @param string $status
     * @return bool
     */
    public function isValidStatus($status)
    {
        $validStatuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
        return in_array($status, $validStatuses, true);
    }

    /**
     * Get statistics
     * @return array
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_samples,
                    COALESCE(SUM(grand_total), 0) as total_revenue,
                    COALESCE(AVG(grand_total), 0) as avg_sample_value
                FROM samples";
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
}
?>