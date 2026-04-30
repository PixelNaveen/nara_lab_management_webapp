<?php
/**
 * Revenue Model
 * Laboratory Management System
 * AAA-Grade Implementation for Financial Tracking
 * 
 * Dependencies:
 * - samples (sample_id, received_date, grand_total, payment_status, client_id)
 * - sample_items (sample_item_id, sample_id, sample_category_id)
 * - sample_tests (sample_test_id, sample_item_id, parameter_id, charge, is_combo_applied, combo_id)
 * - clients (client_id, client_name, phone_primary)
 */

require_once __DIR__ . '/../../Config/Database.php';

class RevenueModel
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
     * Get Total Financial Summary within a date range based on received_date.
     * Billed = sum of all non-cancelled grand_totals.
     * Paid = sum of grand_totals where payment_status = 'Paid'
     * Outstanding = sum of grand_totals where payment_status != 'Paid'
     */
    public function getRevenueSummary($startDate, $endDate)
    {
        $sql = "SELECT 
                    COALESCE(SUM(grand_total), 0) AS total_billed,
                    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END), 0) AS total_paid,
                    COALESCE(SUM(CASE WHEN payment_status != 'Paid' THEN grand_total ELSE 0 END), 0) AS total_outstanding,
                    COUNT(DISTINCT sample_id) AS total_invoices
                FROM samples 
                WHERE status != 'Cancelled' 
                  AND received_date >= ? 
                  AND received_date <= ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getRevenueSummary): " . $this->conn->error);
        }
        $stmt->bind_param("ss", $startDate, $endDate);
        if (!$stmt->execute()) {
            throw new Exception("Execute error (getRevenueSummary): " . $stmt->error);
        }
        
        $row = $stmt->get_result()->fetch_assoc();

        return [
            'total_billed' => floatval($row['total_billed']),
            'total_paid' => floatval($row['total_paid']),
            'total_outstanding' => floatval($row['total_outstanding']),
            'total_invoices' => intval($row['total_invoices'])
        ];
    }

    /**
     * Get Revenue Breakdown by Category (Water vs Food vs Swabs)
     * Calculates based on the atomic `charge` value in `sample_tests` to be 100% accurate,
     * considering only samples that are not cancelled.
     * We map category_id 1 to Water, 2 to Food, and is_swab=1 to Swabs.
     * We calculate BOTH Billed and Paid amounts.
     */
    public function getRevenueByCategory($startDate, $endDate)
    {
        $sql = "SELECT 
                    CASE
                        WHEN st.is_swab = 1 THEN 'Swab'
                        WHEN si.sample_category_id = 1 THEN 'Water'
                        WHEN si.sample_category_id = 2 THEN 'Food'
                        ELSE 'Other'
                    END AS category_name,
                    COALESCE(SUM(st.charge), 0) AS billed_revenue,
                    COALESCE(SUM(CASE WHEN s.payment_status = 'Paid' THEN st.charge ELSE 0 END), 0) AS paid_revenue
                FROM sample_tests st
                JOIN sample_items si ON st.sample_item_id = si.sample_item_id
                JOIN samples s ON si.sample_id = s.sample_id
                WHERE s.status != 'Cancelled'
                  AND s.received_date >= ? 
                  AND s.received_date <= ?
                GROUP BY category_name
                ORDER BY billed_revenue DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getRevenueByCategory): " . $this->conn->error);
        }
        $stmt->bind_param("ss", $startDate, $endDate);
        if (!$stmt->execute()) {
            throw new Exception("Execute error (getRevenueByCategory): " . $stmt->error);
        }
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'category_name' => $row['category_name'],
                'billed_revenue' => floatval($row['billed_revenue']),
                'paid_revenue' => floatval($row['paid_revenue'])
            ];
        }

        return $categories;
    }

    /**
     * Get the list of clients who owe money within the date range,
     * including aging based on how many days outstanding.
     */
    public function getDebtorsList($startDate, $endDate)
    {
        $sql = "SELECT 
                    s.sample_code,
                    s.received_date,
                    c.client_name,
                    c.phone_primary,
                    s.grand_total AS outstanding_amount,
                    DATEDIFF(CURRENT_DATE(), s.received_date) AS days_outstanding
                FROM samples s
                JOIN clients c ON s.client_id = c.client_id
                WHERE s.status != 'Cancelled'
                  AND s.payment_status != 'Paid'
                  AND s.received_date >= ? 
                  AND s.received_date <= ?
                ORDER BY s.received_date ASC, s.grand_total DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getDebtorsList): " . $this->conn->error);
        }
        $stmt->bind_param("ss", $startDate, $endDate);
        if (!$stmt->execute()) {
             throw new Exception("Execute error (getDebtorsList): " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $debtors = [];
        
        while ($row = $result->fetch_assoc()) {
            $debtors[] = [
                'sample_code' => $row['sample_code'],
                'received_date' => $row['received_date'],
                'client_name' => $row['client_name'],
                'phone_primary' => $row['phone_primary'],
                'outstanding_amount' => floatval($row['outstanding_amount']),
                'days_outstanding' => intval($row['days_outstanding'])
            ];
        }

        return $debtors;
    }

    /**
     * Get Daily Revenue Trend (Billed vs Paid) for the given date range.
     */
    public function getRevenueTrend($startDate, $endDate)
    {
        $sql = "SELECT 
                    received_date AS report_date,
                    COALESCE(SUM(grand_total), 0) AS total_billed,
                    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END), 0) AS total_paid
                FROM samples
                WHERE status != 'Cancelled'
                  AND received_date >= ? 
                  AND received_date <= ?
                GROUP BY received_date
                ORDER BY received_date ASC";
                
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error (getRevenueTrend): " . $this->conn->error);
        }
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $trend = [];
        while ($row = $result->fetch_assoc()) {
            $trend[] = [
                'date' => $row['report_date'],
                'billed' => floatval($row['total_billed']),
                'paid' => floatval($row['total_paid'])
            ];
        }

        return $trend;
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
