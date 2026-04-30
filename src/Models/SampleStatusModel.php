<?php

/**
 * Sample Records Model
 * Laboratory Management System
 * 
 * Handles all database operations for samples including:
 * - Sample status management
 * - Payment status management with audit trail
 * - Advanced filtering and search
 * - Statistics and counts
 * 
 * @version 2.0 - Payment System Integrated
 */

require_once __DIR__ . '/../../Config/Database.php';

class SampleStatusModel
{
    private $conn;

    // Valid status values
    private const VALID_STATUSES = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
    private const VALID_PAYMENT_STATUSES = ['Pending', 'Not Paid', 'Paid'];

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();

        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get all samples with advanced filtering
     * Includes payment information
     * 
     * @param array $filters Search, status, payment, date filters
     * @return array Array of samples with complete information
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
                    s.payment_status,
                    s.payment_date,
                    s.payment_updated_by,
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

        // Sample Status filter (Pending, In Progress, Completed, Cancelled)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        // Payment Status filter (Pending, Not Paid, Paid)
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $sql .= " AND s.payment_status = ?";
            $params[] = $filters['payment_status'];
            $types .= 's';
        }

        // Date range (received date)
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

        // Sorting by most recent first
        $sql .= " ORDER BY s.sample_id DESC LIMIT 500";
        // $sql .= " ORDER BY s.received_date DESC, s.sample_id DESC LIMIT 500";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("SQL Prepare Error: " . $this->conn->error);
            return [];
        }

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
     * 
     * @param int $sampleId Sample ID
     * @param string $newStatus New status value
     * @param string $updatedBy User who updated
     * @param string|null $notes Optional notes
     * @return bool Success status
     */
    public function updateSampleStatus($sampleId, $newStatus, $updatedBy, $notes = null)
    {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Validate status
            if (!$this->isValidStatus($newStatus)) {
                throw new Exception("Invalid status value");
            }

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

            // FINALITY: Cannot change status once it's 'Completed'
            if ($oldStatus === 'Completed') {
                throw new Exception("Cannot change status: Sample is already Completed. This action is final.");
            }

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

            // Insert into status log (if table exists)
            $stmt = $this->conn->prepare("INSERT INTO sample_status_log 
                                          (sample_id, old_status, new_status, updated_by, notes, updated_at)
                                          VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issss", $sampleId, $oldStatus, $newStatus, $updatedBy, $notes);
            $stmt->execute(); // Don't fail if log table doesn't exist

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
     * Update payment status with reference number
     * CRITICAL: Payment is FINAL - cannot revert from Paid to Not Paid
     * 
     * @param int $sampleId Sample ID
     * @param string $newPaymentStatus New payment status (Pending/Not Paid/Paid)
     * @param string|null $referenceNumber Payment reference (required for Paid)
     * @param string $updatedBy User who updated
     * @param string|null $paymentDate Custom payment date (required for Paid)
     * @return array ['success' => bool, 'message' => string]
     */
    public function updatePaymentStatus($sampleId, $newPaymentStatus, $referenceNumber, $updatedBy, $paymentDate = null)
    {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Validate payment status
            if (!$this->isValidPaymentStatus($newPaymentStatus)) {
                throw new Exception("Invalid payment status value");
            }

            // Get current payment status
            $stmt = $this->conn->prepare("SELECT payment_status, payment_reference 
                                          FROM samples 
                                          WHERE sample_id = ?");
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentData = $result->fetch_assoc();

            if (!$currentData) {
                throw new Exception("Sample not found");
            }

            $oldPaymentStatus = $currentData['payment_status'];

            // CRITICAL RULE: Cannot revert from Paid to Not Paid/Pending
            if ($oldPaymentStatus === 'Paid' && $newPaymentStatus !== 'Paid') {
                throw new Exception("Cannot change payment status from Paid to " . $newPaymentStatus . ". Payment is final.");
            }

            // Validate reference number for Paid status
            if ($newPaymentStatus === 'Paid') {
                if (empty($referenceNumber) || trim($referenceNumber) === '') {
                    throw new Exception("Reference number is required when marking as Paid");
                }

                // Sanitize reference number
                $referenceNumber = trim($referenceNumber);

                if (strlen($referenceNumber) > 100) {
                    throw new Exception("Reference number too long (max 100 characters)");
                }
            }

            // Don't update if status is the same, UNLESS it's Paid
            // For Paid, we want to allow updating the reference number or payment date
            if ($oldPaymentStatus === $newPaymentStatus && $newPaymentStatus !== 'Paid') {
                $this->conn->rollback();
                return [
                    'success' => true,
                    'message' => 'Payment status unchanged'
                ];
            }

            // Clean up fields if not paid (though Paid is final, added for safety)
            if ($newPaymentStatus !== 'Paid') {
                $referenceNumber = null;
                $paymentDate = null;
            } else if (empty($paymentDate)) {
                $tz = new DateTimeZone('Asia/Colombo');
                $now = new DateTime('now', $tz);
                $paymentDate = $now->format('Y-m-d H:i:s');
            } else {
                // Formatting date to ensure it saves cleanly (append time if it's just a date)
                if (strlen($paymentDate) === 10) {
                    $tz = new DateTimeZone('Asia/Colombo');
                    $now = new DateTime('now', $tz);
                    $paymentDate .= ' ' . $now->format('H:i:s');
                }
            }

            // Update samples table
            $stmt = $this->conn->prepare("UPDATE samples 
                                          SET payment_status = ?,
                                              payment_reference = ?,
                                              payment_date = ?,
                                              payment_updated_by = ?
                                          WHERE sample_id = ?");
            $stmt->bind_param("ssssi", $newPaymentStatus, $referenceNumber, $paymentDate, $updatedBy, $sampleId);

            if (!$stmt->execute()) {
                // Check for duplicate reference number (MySQL error 1062)
                if ($this->conn->errno === 1062) {
                    throw new Exception("This reference number already exists. Please use a unique reference number.");
                }
                throw new Exception("Failed to update payment status");
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Payment status updated successfully',
                'old_status' => $oldPaymentStatus,
                'new_status' => $newPaymentStatus
            ];
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            error_log("Payment update error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment information for a specific sample
     * Used when opening payment modal
     * 
     * @param int $sampleId Sample ID
     * @return array|null Sample payment info
     */
    public function getPaymentInfo($sampleId)
    {
        $stmt = $this->conn->prepare("SELECT 
                                        s.sample_id,
                                        s.sample_code,
                                        s.grand_total,
                                        s.payment_status,
                                        s.payment_date,
                                        s.payment_updated_by,
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
     * Get count of samples by status
     * 
     * @return array Status counts
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
     * Get count of samples by payment status
     * 
     * @return array Payment status counts
     */
    public function getPaymentCounts()
    {
        $sql = "SELECT 
                    payment_status,
                    COUNT(*) as count,
                    COALESCE(SUM(grand_total), 0) as total_amount
                FROM samples
                GROUP BY payment_status";

        $result = $this->conn->query($sql);

        $counts = [
            'all' => ['count' => 0, 'amount' => 0],
            'Pending' => ['count' => 0, 'amount' => 0],
            'Not Paid' => ['count' => 0, 'amount' => 0],
            'Paid' => ['count' => 0, 'amount' => 0]
        ];

        while ($row = $result->fetch_assoc()) {
            $status = $row['payment_status'];
            $counts[$status] = [
                'count' => (int)$row['count'],
                'amount' => (float)$row['total_amount']
            ];
            $counts['all']['count'] += (int)$row['count'];
            $counts['all']['amount'] += (float)$row['total_amount'];
        }

        return $counts;
    }

    /**
     * Get single sample details
     * 
     * @param int $sampleId Sample ID
     * @return array|null Sample details
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
     * Validate sample status value
     * 
     * @param string $status Status to validate
     * @return bool Valid or not
     */
    public function isValidStatus($status)
    {
        return in_array($status, self::VALID_STATUSES, true);
    }

    /**
     * Validate payment status value
     * 
     * @param string $paymentStatus Payment status to validate
     * @return bool Valid or not
     */
    public function isValidPaymentStatus($paymentStatus)
    {
        return in_array($paymentStatus, self::VALID_PAYMENT_STATUSES, true);
    }

    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_samples,
                    COALESCE(SUM(grand_total), 0) as total_revenue,
                    COALESCE(AVG(grand_total), 0) as avg_sample_value,
                    SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN payment_status != 'Paid' THEN grand_total ELSE 0 END) as unpaid_amount
                FROM samples";

        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Destructor - close connection
     */
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
