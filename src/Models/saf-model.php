<?php

/**
 * SAF Model - Sample Acceptance Form Data Fetcher
 * Version: 3.0 - ULTRA-FINAL (Database-Matched)
 * 
 * CORRECTED TO MATCH ACTUAL DATABASE:
 * - form_number format: YY/NNN/SSS (e.g., "25/0001/01")
 * - Base extraction: "25/001" (3-digit sequence)
 * - Sample code generation: "25/0001/001", "25/0001/002", "25/0001/003" (3-digit)
 * 
 * DATABASE SCHEMA VERIFIED:
 * - samples.form_number: VARCHAR(20) - stores "25/0001/01"
 * - sample_items.sequence_number: INT - stores 1, 2, 3, ...
 * - Generated codes: "25/0001/001", "25/0001/002", "25/0001/003"
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';

class SAFModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get complete SAF data with multi-page support
     * 
     * @param int $sampleId Sample ID
     * @return array Complete SAF data with pages
     */
    public function getSAFData($sampleId)
    {
        try {
            // Main query - fetch ALL samples (no limit for multi-page support)
            $sql = "SELECT 
                        s.sample_id,
                        s.form_number,
                        s.tentative_date,
                        
                        c.client_name,
                        c.address_line1,
                        c.city,
                        c.phone_primary,
                        
                        si.sample_item_id,
                        si.sample_name,
                        si.client_sample_code,
                        si.value,
                        si.unit,
                        si.container_damage,
                        si.temperature_condition,
                        si.validity_status,
                        si.sequence_number,
                        
                        sa.report_ref,
                        sa.received_by,
                        DATE(sa.created_at) as acceptance_date,
                        sa.remarks,
                        sa.validity_ok,
                        
                        sack.test_charges,
                        sack.total_charges
                        
                    FROM samples s
                    LEFT JOIN clients c ON s.client_id = c.client_id
                    LEFT JOIN sample_items si ON s.sample_id = si.sample_id
                    LEFT JOIN sample_acceptance sa ON s.sample_id = sa.sample_id
                    LEFT JOIN sample_acknowledgement sack ON s.sample_id = sack.sample_id
                    
                    WHERE s.sample_id = ?
                    ORDER BY si.sequence_number ASC";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Sample not found'
                ];
            }

            // Process results
            $safData = [
                'sample' => null,
                'client' => null,
                'acceptance' => null,
                'acknowledgement' => null,
                'items' => []
            ];

            while ($row = $result->fetch_assoc()) {
                // First row: extract main data
                if ($safData['sample'] === null) {
                    $safData['sample'] = [
                        'sample_id' => $row['sample_id'],
                        'form_number' => $row['form_number'],
                        'tentative_date' => $row['tentative_date']
                    ];

                    $safData['client'] = [
                        'name' => $row['client_name'],
                        'address' => $row['address_line1'],
                        'city' => $row['city'],
                        'phone' => $row['phone_primary'],
                        'full_address' => trim(
                            $row['client_name'] . ', ' .
                                $row['address_line1'] . ', ' .
                                $row['city']
                        )
                    ];

                    $safData['acceptance'] = [
                        'report_ref' => $row['report_ref'],
                        'received_by' => $row['received_by'] ?? '',
                        'date' => $row['acceptance_date'] ? date('d/m/Y', strtotime($row['acceptance_date'])) : '',
                        'remarks' => $row['remarks'] ?? '',
                        'validity_ok' => $row['validity_ok']
                    ];

                    $testCharges  = (float)($row['test_charges'] ?? 0);
                    $totalCharges = (float)($row['total_charges'] ?? 0);

                    $safData['acknowledgement'] = [
                        'test_charges' => $testCharges,
                        'additional_charges' => max(0, $totalCharges - $testCharges),
                        'total_charges' => $totalCharges
                    ];


                    // Format tentative date for display
                    $safData['acceptance']['tentative_date'] = $row['tentative_date']
                        ? date('d/m/Y', strtotime($row['tentative_date']))
                        : '';
                }

                // Collect ALL sample items (not limited to 10)
                if ($row['sample_item_id']) {
                    $sequenceNumber = (int)$row['sequence_number'];

                    // Generate sample code from form_number + sequence
                    // CORRECTED: Extracts base from "25/0001/01" → "25/001"
                    // Then adds 3-digit sequence: "25/0001/001", "25/0001/002", etc.
                    $sampleCode = $this->generateSampleCode($row['form_number'], $sequenceNumber);

                    $safData['items'][] = [
                        'sample_item_id' => $row['sample_item_id'],
                        'sample_name' => $row['sample_name'],
                        'sample_code' => $sampleCode,  // GENERATED from form_number
                        'client_sample_code' => $row['client_sample_code'] ?? '',  // Client's own code (if any)
                        'weight_volume' => trim($row['value'] . ' ' . $row['unit']),
                        'container_damage' => $row['container_damage'] ?? '',
                        'temperature' => $row['temperature_condition'] ?? '',
                        'validity' => $row['validity_status'] ?? '',
                        'sequence_number' => $sequenceNumber
                    ];
                }
            }

            // Split samples into pages of 10
            $pages = $this->chunkSamplesIntoPages($safData['items']);
            $safData['pages'] = $pages;
            $safData['total_pages'] = count($pages);
            $safData['total_samples'] = count($safData['items']);

            return [
                'success' => true,
                'data' => $safData
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SAFModel::getSAFData');
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Split samples into pages of 10
     * Each page has exactly 10 rows (filled or empty)
     * 
     * @param array $allSamples All sample items
     * @return array Array of pages, each with 10 samples
     */
    private function chunkSamplesIntoPages($allSamples)
    {
        $pages = [];
        $pageSize = 10;

        // If no samples, return one empty page
        if (empty($allSamples)) {
            $emptyPage = [];
            for ($i = 1; $i <= $pageSize; $i++) {
                $emptyPage[] = [
                    'sample_name' => '',
                    'sample_code' => '',
                    'weight_volume' => '',
                    'container_damage' => '',
                    'temperature' => '',
                    'validity' => '',
                    'sequence_number' => $i,
                    'display_number' => $i
                ];
            }
            $pages[] = $emptyPage;
            return $pages;
        }

        // Split samples into chunks of 10
        $totalSamples = count($allSamples);
        $pageCount = (int)ceil($totalSamples / $pageSize);

        for ($page = 0; $page < $pageCount; $page++) {
            $startIndex = $page * $pageSize;
            $pageItems = [];

            for ($i = 0; $i < $pageSize; $i++) {
                $sampleIndex = $startIndex + $i;

                if ($sampleIndex < $totalSamples) {
                    // Real sample
                    $sample = $allSamples[$sampleIndex];
                    $sample['display_number'] = $sample['sequence_number'];
                    $pageItems[] = $sample;
                } else {
                    // Empty row
                    $pageItems[] = [
                        'sample_name' => '',
                        'sample_code' => '',
                        'weight_volume' => '',
                        'container_damage' => '',
                        'temperature' => '',
                        'validity' => '',
                        'sequence_number' => 0,
                        'display_number' => $startIndex + $i + 1
                    ];
                }
            }

            $pages[] = $pageItems;
        }

        return $pages;
    }

    /**
     * Quick check if SAF data exists for a sample
     * 
     * @param int $sampleId Sample ID
     * @return bool True if SAF exists
     */
    public function safExists($sampleId)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM sample_acceptance 
                    WHERE sample_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['count'] > 0;
        } catch (Exception $e) {
            logError($e->getMessage(), 'SAFModel::safExists');
            return false;
        }
    }

    /**
     * Generate sample code from form number
     * 
     * DATABASE FORMAT: YY/NNNN/SS
     * Example: "25/0001/01" (year/4-digit-sequence/2-digit-count)
     * 
     * SAMPLE CODE FORMAT: YY/NNNN/SSS
     * Example: "25/0001/001", "25/0001/002", "25/0001/003" (3-digit sample number)
     * 
     * PROCESS:
     * 1. Extract base from form_number: "25/0001/01" → "25/0001"
     * 2. Append 3-digit padded sequence: "25/0001" + "/001" → "25/0001/001"
     * 
     * @param string $formNumber Full form number (e.g., "25/0001/01")
     * @param int $sequenceNumber Sample sequence (1, 2, 3, ...)
     * @return string Sample code (e.g., "25/0001/001")
     */
    private function generateSampleCode($formNumber, $sequenceNumber)
    {
        // Validate and split form number
        $parts = explode('/', $formNumber);

        if (count($parts) !== 3) {
            logError("Invalid form_number format: $formNumber (expected YY/NNNN/SS)", 'SAFModel::generateSampleCode');
            return ''; // Invalid format, return empty
        }

        // Validate year part (2-3 digits)
        if (strlen($parts[0]) < 2 || strlen($parts[0]) > 3 || !is_numeric($parts[0])) {
            logError("Invalid year in form_number: $formNumber (expected 2-3 digits)", 'SAFModel::generateSampleCode');
        }

        // Validate sequence part (4 digits as per database format)
        if (strlen($parts[1]) !== 4 || !is_numeric($parts[1])) {
            logError("Invalid sequence in form_number: $formNumber (expected 4 digits)", 'SAFModel::generateSampleCode');
        }

        // Validate count part (2 digits as per database format)
        if ((strlen($parts[2]) < 2 || strlen($parts[2]) > 3) || !is_numeric($parts[2])) {
            logError("Invalid count in form_number: $formNumber (expected 2-3 digits)", 'SAFModel::generateSampleCode');
        }

        // Extract base: "25/0001" (year + sequence)
        $base = $parts[0] . '/' . $parts[1];

        // CRITICAL: 3-DIGIT padding for sample codes
        // Matches user's paper form format: 001, 002, 003
        // 1-99: 001, 002, ..., 099
        // 100-999: 100, 101, ..., 999
        // 1000+: 1000, 1001 (automatically overflows if needed)
        $paddedSequence = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);

        $sampleCode = $base . '/' . $paddedSequence;

        return $sampleCode;
    }
}