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
     * Get all samples with optional filtering
     * @param string $statusFilter - 'all', 'Pending', 'In Progress', 'Completed', 'Cancelled'
     * @param string $searchTerm - Search in client_name or sample_code
     * @return array
     */
    public function getAllSamples($statusFilter = 'all', $searchTerm = '')
    {
        $sql = "SELECT 
                    s.sample_id,
                    s.sample_code,
                    s.status,
                    s.received_date,
                    s.tentative_date,
                    s.grand_total,
                    c.client_name,
                    s.status_updated_at,
                    s.status_updated_by
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                WHERE 1=1";

        $params = [];
        $types = '';

        // Status filter
        if ($statusFilter !== 'all') {
            $sql .= " AND s.status = ?";
            $params[] = $statusFilter;
            $types .= 's';
        }

        // Search filter
        if (!empty($searchTerm)) {
            $sql .= " AND (c.client_name LIKE ? OR s.sample_code LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'ss';
        }

        $sql .= " ORDER BY s.received_date DESC, s.sample_id DESC LIMIT 100";

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
                                        c.client_name
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
        return in_array($status, $validStatuses);
    }
}
?>