<?php
/**
 * Analyst Information Form (AIF) Model
 * Fetches data for Analyst Information Form
 * 
 * CORRECTED:
 * - Methods separator: COMMA + SPACE (not /)
 * - Report Submission Date: Empty field (NOT pre-filled)
 * - Returns ALL parameters dynamically
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 2.0 - Corrected
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/consolidated-params-model.php';
require_once __DIR__ . '/print-history-model.php';

class AnalystModel
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
     * Get all data for Analyst Information Form
     * 
     * @param int $sampleId Sample ID
     * @return array|null Form data or null if not found
     */
    public function getAnalystData($sampleId)
    {
        try {
            // Main query
            $sql = "SELECT 
                        s.sample_id,
                        s.form_number,
                        s.tentative_date,
                        
                        sa.received_by,
                        DATE(sa.created_at) as received_date
                        
                    FROM samples s
                    LEFT JOIN sample_acceptance sa ON s.sample_id = sa.sample_id
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

            // Get sample items for description and volume/weight
            $sampleItems = $this->getSampleItems($sampleId);

            // Get ALL consolidated parameters with highlighting
            $parameters = $this->paramsModel->getAllConsolidatedParams();
            $selectedParams = $this->paramsModel->getSelectedParamsForSample($sampleId);

            // Add highlighting flag to parameters
            foreach ($parameters as &$param) {
                $param['is_selected'] = in_array($param['parameter_name'], $selectedParams);
            }

            // Get last print info
            $lastPrint = $this->printHistory->getLastPrintInfo($sampleId, 'ANALYST');

            return [
                'sample_id' => $row['sample_id'],
                'form_number' => $row['form_number'],
                'received_by' => $row['received_by'] ?? '',
                'received_date' => $row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '',
                // CORRECTED: Report submission date is EMPTY (not pre-filled)
                'report_submission_date' => '', // Empty field for manual entry
                'sample_description' => $this->formatSampleDescription($sampleItems),
                'sample_numbers' => $this->formatSampleNumbers($row['form_number'], count($sampleItems)),
                'volume_weight' => $this->formatVolumeWeight($sampleItems),
                'parameters' => $parameters, // ALL parameters (dynamic)
                'issued_by' => $lastPrint ? $lastPrint['printed_by'] : null,
                'issued_at' => $lastPrint ? $lastPrint['printed_at'] : null
            ];
            
        } catch (Exception $e) {
            error_log("Analyst Data Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all sample items
     * 
     * @param int $sampleId Sample ID
     * @return array Array of sample items
     */
    private function getSampleItems($sampleId)
    {
        try {
            $sql = "SELECT sample_name, value, unit, sequence_number
                    FROM sample_items 
                    WHERE sample_id = ? 
                    ORDER BY sequence_number ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();

            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            return $items;
            
        } catch (Exception $e) {
            error_log("Get Sample Items Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Format sample description (comma-separated names)
     * 
     * @param array $items Array of sample items
     * @return string Comma-separated sample names
     */
    private function formatSampleDescription($items)
    {
        $names = array_map(function($item) {
            return $item['sample_name'];
        }, $items);

        return implode(', ', $names);
    }

    /**
     * Format sample numbers
     * ≤10: "26/001/001, 26/001/002, 26/001/003"
     * >10: "26/001/001 - 26/001/015"
     * 
     * @param string $formNumber Form number (e.g., "26/001/01")
     * @param int $count Number of samples
     * @return string Formatted sample numbers
     */
    private function formatSampleNumbers($formNumber, $count)
    {
        // Extract base: "26/001/01" → "26/001"
        $parts = explode('/', $formNumber);
        if (count($parts) < 2) {
            return '';
        }
        $base = $parts[0] . '/' . $parts[1];

        if ($count <= 10) {
            // Display all
            $numbers = [];
            for ($i = 1; $i <= $count; $i++) {
                $numbers[] = $base . '/' . str_pad($i, 3, '0', STR_PAD_LEFT);
            }
            return implode(', ', $numbers);
        } else {
            // Display range
            $start = $base . '/001';
            $end = $base . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            return $start . ' - ' . $end;
        }
    }

    /**
     * Format volume/weight
     * All same: "500 ml"
     * Different: "500 ml, 1000 ml, 200 g"
     * 
     * @param array $items Array of sample items
     * @return string Formatted volume/weight
     */
    private function formatVolumeWeight($items)
    {
        $values = [];
        foreach ($items as $item) {
            $valueUnit = trim($item['value'] . ' ' . $item['unit']);
            if (!in_array($valueUnit, $values) && !empty($valueUnit)) {
                $values[] = $valueUnit;
            }
        }

        return implode(', ', $values);
    }
}