<?php
/**
 * Sample Acknowledgement Form (SAcF) Model
 * Fetches data for Sample Acknowledgement Form
 * 
 * CORRECTED:
 * - Methods separator: COMMA + SPACE (not /)
 * - Returns ALL parameters dynamically
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 2.0 - Corrected
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/consolidated-params-model.php';
require_once __DIR__ . '/print-history-model.php';

class AcknowledgementModel
{
    private $conn;
    private $paramsModel;
    private $printHistory;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
        $this->paramsModel = new ConsolidatedParamsModel();
        $this->printHistory = new PrintHistoryModel();
    }

    /**
     * Get all data for Sample Acknowledgement Form
     * 
     * @param int $sampleId Sample ID
     * @return array|null Form data or null if not found
     */
    public function getAcknowledgementData($sampleId)
    {
        try {
            // Main query
            $sql = "SELECT 
                        s.sample_id,
                        s.form_number,
                        s.tentative_date,
                        s.payment_status,
                        s.payment_reference as receipt_no,
                        
                        c.client_name,
                        
                        sa.report_ref,
                        sa.received_by,
                        DATE(sa.created_at) as received_date,
                        
                        sack.test_charges,
                        sack.additional_charges,
                        sack.total_charges
                        
                    FROM samples s
                    LEFT JOIN clients c ON s.client_id = c.client_id
                    LEFT JOIN sample_acceptance sa ON s.sample_id = sa.sample_id
                    LEFT JOIN sample_acknowledgement sack ON s.sample_id = sack.sample_id
                    WHERE s.sample_id = ?
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            $row = $result->fetch_assoc();

            // Get sample information (names of all samples)
            $sampleNames = $this->getSampleNames($sampleId);

            // Get ALL consolidated parameters (dynamic, not limited)
            $parameters = $this->paramsModel->getAllConsolidatedParams();

            // Get last print info (for "Issued By" field)
            $lastPrint = $this->printHistory->getLastPrintInfo($sampleId, 'ACKNOWLEDGEMENT');

            return [
                'sample_id' => $row['sample_id'],
                'form_number' => $row['form_number'],
                'report_ref' => $row['report_ref'],
                'client_name' => $row['client_name'],
                'received_by' => $row['received_by'] ?? '',
                'received_date' => $row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '',
                'tentative_date' => $row['tentative_date'] ? date('d/m/Y', strtotime($row['tentative_date'])) : '',
                'test_charges' => floatval($row['test_charges'] ?? 0),
                'additional_charges' => floatval($row['additional_charges'] ?? 0),
                'total_charges' => floatval($row['total_charges'] ?? 0),
                'payment_status' => $row['payment_status'] ?? 'Pending',
                'receipt_no' => $this->formatReceiptNumber($row),
                'sample_information' => $sampleNames,
                'parameters' => $parameters, // ALL parameters (dynamic)
                'issued_by' => $lastPrint ? $lastPrint['printed_by'] : null,
                'issued_at' => $lastPrint ? $lastPrint['printed_at'] : null
            ];
            
        } catch (Exception $e) {
            error_log("Acknowledgement Data Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all sample names for this submission
     * 
     * @param int $sampleId Sample ID
     * @return string Comma-separated sample names
     */
    private function getSampleNames($sampleId)
    {
        try {
            $sql = "SELECT sample_name 
                    FROM sample_items 
                    WHERE sample_id = ? 
                    ORDER BY sequence_number ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();

            $names = [];
            while ($row = $result->fetch_assoc()) {
                $names[] = $row['sample_name'];
            }

            return implode(', ', $names);
            
        } catch (Exception $e) {
            error_log("Get Sample Names Error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Format receipt number or return "NOT PAID"
     * 
     * @param array $row Database row with payment info
     * @return string Receipt number or "NOT PAID"
     */
    private function formatReceiptNumber($row)
    {
        // Get values and normalize (case-insensitive)
        $paymentStatus = strtolower(trim($row['payment_status'] ?? 'pending'));
        $receiptNo = trim($row['receipt_no'] ?? '');

        // Debug log
        error_log("Payment Check - Status: '$paymentStatus', Receipt: '$receiptNo'");

        // Case-insensitive check for paid status
        if ($paymentStatus === 'paid' || $paymentStatus === 'completed') {
            return !empty($receiptNo) ? $receiptNo : 'PAID (No Receipt)';
        }

        return 'NOT PAID';
    }

    /**
     * Format test charges display
     * 
     * @param float $testCharges Base test charges
     * @param float $additionalCharges Additional charges
     * @return string Formatted charges display
     */
    public function formatChargesDisplay($testCharges, $additionalCharges)
    {
        $test = number_format($testCharges, 2);
        $additional = floatval($additionalCharges);

        if ($additional > 0) {
            return $test . ' + ' . number_format($additional, 2);
        }

        return $test;
    }
}