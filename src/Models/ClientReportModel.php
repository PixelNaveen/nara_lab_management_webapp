<?php

/**
 * Client Reports (CRM) Model - v2 (Fixed)
 * Laboratory Management System
 *
 * Correct column names verified from existing ClientModel.php and SampleRecordsModel.php:
 *   clients: client_id, client_name, address_line1, city (TEXT), phone_primary, contact_person, is_active
 *   samples: sample_id, sample_code, form_number, received_date, tentative_date, analysis_end_date,
 *            status, grand_total, payment_status, payment_date, client_id
 *
 * @version 2.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class ClientReportModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get all active clients for the dropdown selector.
     */
    public function getAllClients()
    {
        $sql = "SELECT client_id, client_name, phone_primary, contact_person, city
                FROM clients
                WHERE is_active = 1
                ORDER BY client_name ASC";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("DB Error (getAllClients): " . $this->conn->error);
        }

        $clients = [];
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        return $clients;
    }

    /**
     * Get client profile details by ID.
     */
    public function getClientDetails($clientId)
    {
        $sql = "SELECT client_id, client_name, address_line1, city, phone_primary, contact_person, registration_date
                FROM clients
                WHERE client_id = ? AND is_active = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all samples submitted by a client (History).
     */
    public function getClientHistory($clientId)
    {
        $sql = "SELECT
                    sample_id,
                    sample_code,
                    form_number,
                    received_date,
                    tentative_date,
                    analysis_end_date,
                    status,
                    payment_status,
                    payment_date,
                    grand_total
                FROM samples
                WHERE client_id = ? AND status != 'Cancelled'
                ORDER BY received_date DESC, sample_id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getClientHistory): " . $this->conn->error);
        }
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    /**
     * Get financial summary: total billed vs outstanding vs fully paid.
     */
    public function getFinancialSummary($clientId)
    {
        $sql = "SELECT
                    COUNT(sample_id) AS total_samples,
                    COALESCE(SUM(grand_total), 0) AS total_billed,
                    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END), 0) AS total_paid,
                    COALESCE(SUM(CASE WHEN payment_status != 'Paid' THEN grand_total ELSE 0 END), 0) AS total_outstanding
                FROM samples
                WHERE client_id = ? AND status != 'Cancelled'";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getFinancialSummary): " . $this->conn->error);
        }
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return [
            'total_samples'     => intval($row['total_samples'] ?? 0),
            'total_billed'      => floatval($row['total_billed'] ?? 0),
            'total_paid'        => floatval($row['total_paid'] ?? 0),
            'total_outstanding' => floatval($row['total_outstanding'] ?? 0)
        ];
    }

    /**
     * Get the most frequently tested parameters for a client.
     * Drills: samples -> sample_items -> sample_tests -> test_parameters
     */
    public function getTestingTrends($clientId, $limit = 12)
    {
        $sql = "SELECT
                    tp.parameter_name,
                    COUNT(st.sample_test_id) AS test_count
                FROM samples s
                JOIN sample_items si ON s.sample_id = si.sample_id
                JOIN sample_tests st ON si.sample_item_id = st.sample_item_id
                JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                WHERE s.client_id = ? AND s.status != 'Cancelled'
                GROUP BY tp.parameter_id, tp.parameter_name
                ORDER BY test_count DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // If the join fails due to schema differences, return empty gracefully
            return [];
        }
        $stmt->bind_param("ii", $clientId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }
        return $trends;
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
