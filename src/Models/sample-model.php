<?php
/**
 * Sample Model
 * Handles all database operations for sample submission
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';

class SampleModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Search clients by name, phone, or email
     * 
     * @param string $query Search query
     * @return array Array of matching clients
     */
    public function searchClients($query) {
        try {
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT client_id, client_name, address_line1, city, 
                           phone_primary, email, mobile, contact_person
                    FROM clients 
                    WHERE is_Active = 1 
                    AND (client_name LIKE ? 
                         OR phone_primary LIKE ? 
                         OR email LIKE ? 
                         OR mobile LIKE ?)
                    ORDER BY client_name ASC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $clients = [];
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            
            return [
                'success' => true,
                'clients' => $clients,
                'count' => count($clients)
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::searchClients');
            return [
                'success' => false,
                'message' => 'Error searching clients: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new client
     * 
     * @param array $data Client data
     * @return array Result with client_id
     */
    public function createClient($data) {
        try {
            $sql = "INSERT INTO clients (
                        client_name, address_line1, city, phone_primary, 
                        email, mobile, contact_person, registration_date, is_Active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 1)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssss",
                $data['client_name'],
                $data['address_line1'],
                $data['city'],
                $data['phone_primary'],
                $data['email'],
                $data['mobile'],
                $data['contact_person']
            );
            
            if ($stmt->execute()) {
                $clientId = $this->conn->insert_id;
                
                return [
                    'success' => true,
                    'client_id' => $clientId,
                    'message' => 'Client created successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create client'
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::createClient');
            return [
                'success' => false,
                'message' => 'Error creating client: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update existing client information
     * 
     * @param array $data Client data including client_id
     * @return array Result
     */
    public function updateClient($data) {
        try {
            $sql = "UPDATE clients 
                    SET client_name = ?,
                        address_line1 = ?,
                        city = ?,
                        phone_primary = ?,
                        email = ?,
                        mobile = ?,
                        contact_person = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE client_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssi",
                $data['client_name'],
                $data['address_line1'],
                $data['city'],
                $data['phone_primary'],
                $data['email'],
                $data['mobile'],
                $data['contact_person'],
                $data['client_id']
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Client updated successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update client'
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::updateClient');
            return [
                'success' => false,
                'message' => 'Error updating client: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search sample names for autocomplete
     * 
     * @param string $query Search query
     * @return array Array of matching sample names
     */
    public function searchSampleNames($query) {
        try {
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT sample_name_id, sample_name, usage_count
                    FROM sample_names 
                    WHERE sample_name LIKE ?
                    ORDER BY usage_count DESC, sample_name ASC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $names = [];
            while ($row = $result->fetch_assoc()) {
                $names[] = $row;
            }
            
            return [
                'success' => true,
                'results' => $names
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::searchSampleNames');
            return [
                'success' => false,
                'message' => 'Error searching sample names'
            ];
        }
    }

    /**
     * Get all parameters filtered by submission type
     * 
     * @param string $submissionType 'regular' or 'swab'
     * @return array Array of parameters with pricing
     */
    public function getParameters($submissionType) {
        try {
            // For swab, only show parameters where swab_enabled = 1
            $swabFilter = ($submissionType === 'swab') ? "AND tp.swab_enabled = 1" : "";
            
            $sql = "SELECT 
                        tp.parameter_id,
                        tp.parameter_code,
                        tp.parameter_name,
                        tp.base_unit,
                        tp.has_variants,
                        tp.swab_enabled,
                        pp.test_charge as price,
                        sp.swab_price
                    FROM test_parameters tp
                    LEFT JOIN parameter_pricing pp ON tp.parameter_id = pp.parameter_id 
                        AND pp.is_active = 1 AND pp.is_deleted = 0
                    LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id 
                        AND sp.is_active = 1 AND sp.is_deleted = 0
                    WHERE tp.is_active = 1 AND tp.is_deleted = 0
                    {$swabFilter}
                    ORDER BY tp.parameter_code ASC";
            
            $result = $this->conn->query($sql);
            
            $parameters = [];
            while ($row = $result->fetch_assoc()) {
                $parameters[] = $row;
            }
            
            return [
                'success' => true,
                'parameters' => $parameters,
                'count' => count($parameters)
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::getParameters');
            return [
                'success' => false,
                'message' => 'Error loading parameters'
            ];
        }
    }

    /**
     * Get variants for a specific parameter
     * 
     * @param int $parameterId Parameter ID
     * @return array Array of variants
     */
    public function getVariants($parameterId) {
        try {
            $sql = "SELECT variant_id, parameter_id, variant_name, full_display_name
                    FROM parameter_variants
                    WHERE parameter_id = ? AND is_active = 1 AND is_deleted = 0
                    ORDER BY sequence_order ASC, variant_id ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $parameterId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $variants = [];
            while ($row = $result->fetch_assoc()) {
                $variants[] = $row;
            }
            
            return [
                'success' => true,
                'variants' => $variants
            ];
            
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::getVariants');
            return [
                'success' => false,
                'message' => 'Error loading variants'
            ];
        }
    }

    /**
     * Validate if payment reference is unique
     * 
     * @param string $reference Payment reference
     * @param int|null $excludeSampleId Sample ID to exclude
     * @return array Validation result
     */
    public function validatePaymentReference($reference, $excludeSampleId = null) {
        $exists = paymentReferenceExists($this->conn, $reference, $excludeSampleId);
        
        if ($exists) {
            return [
                'valid' => false,
                'message' => 'This payment reference already exists'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Payment reference is available'
        ];
    }

    /**
     * Save complete sample submission
     * Main method that saves everything
     * 
     * @param array $data Complete submission data
     * @return array Result with sample_id and form_number
     */
    public function saveSample($data) {
        try {
            // Start transaction
            $this->conn->begin_transaction();
            
            // 1. Generate form number
            $sampleCount = count($data['samples']);
            $formNumberData = generateFormNumber($this->conn, $sampleCount);
            
            if (!$formNumberData['success']) {
                throw new Exception($formNumberData['message']);
            }
            
            $baseFormNumber = $formNumberData['base_form_number']; // 25/0001
            $fullFormNumber = $formNumberData['form_number'];      // 25/0001/03
            
            // 2. Insert main sample record
            $sampleId = $this->insertSampleRecord($data, $baseFormNumber, $fullFormNumber);
            
            // 3. Insert sample items and tests
            $sampleCodes = generateSampleCodes($baseFormNumber, $sampleCount);
            
            foreach ($data['samples'] as $index => $sampleItem) {
                $sampleCode = $sampleCodes[$index];
                $sampleItemId = $this->insertSampleItem($sampleId, $sampleItem, $sampleCode, $index + 1);
                
                // Insert tests for this sample item
                if (isset($sampleItem['tests']) && !empty($sampleItem['tests'])) {
                    $this->insertSampleTests($sampleItemId, $sampleItem['tests'], $data['submission_type']);
                }
            }
            
            // 4. Commit transaction
            $this->conn->commit();
            
            return [
                'success' => true,
                'sample_id' => $sampleId,
                'form_number' => $fullFormNumber,
                'base_form_number' => $baseFormNumber,
                'message' => 'Sample submitted successfully'
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            logError($e->getMessage(), 'SampleModel::saveSample');
            
            return [
                'success' => false,
                'message' => 'Error saving sample: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Insert main sample record
     * 
     * @param array $data Sample data
     * @param string $baseFormNumber Base form number
     * @param string $fullFormNumber Full form number
     * @return int Sample ID
     */
    private function insertSampleRecord($data, $baseFormNumber, $fullFormNumber) {
        $sql = "INSERT INTO samples (
                    client_id, sample_code, form_number, submission_type,
                    received_date, tentative_date, submitted_by, additional_notes,
                    additional_charges, test_charges_total, grand_total,
                    payment_status, payment_reference, payment_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Set payment date if paid
        $paymentDate = ($data['payment_status'] === 'Paid') ? date('Y-m-d H:i:s') : null;
        $paymentRef = ($data['payment_status'] === 'Paid') ? $data['payment_reference'] : null;
        
        $stmt->bind_param("isssssssdddsss",
            $data['client_id'],
            $fullFormNumber,           // sample_code stores full form number
            $baseFormNumber,           // form_number stores base form number
            $data['submission_type'],
            $data['received_date'],
            $data['tentative_date'],
            $data['submitted_by'],
            $data['additional_notes'],
            $data['additional_charges'],
            $data['test_charges_total'],
            $data['grand_total'],
            $data['payment_status'],
            $paymentRef,
            $paymentDate
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert sample record');
        }
        
        return $this->conn->insert_id;
    }

    /**
     * Insert sample item record
     * 
     * @param int $sampleId Parent sample ID
     * @param array $item Sample item data
     * @param string $sampleCode Sample code
     * @param int $sequence Sequence number
     * @return int Sample item ID
     */
    private function insertSampleItem($sampleId, $item, $sampleCode, $sequence) {
        $sql = "INSERT INTO sample_items (
                    sample_id, sample_name, value, unit, client_sample_code,
                    sampling_location, reason_for_analysis, container_damage,
                    temperature_condition, validity_status, sequence_number,
                    item_total_charge
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bind_param("isssssssssid",
            $sampleId,
            $item['sample_name'],
            $item['value'],
            $item['unit'],
            $item['client_sample_code'],
            $item['sampling_location'],
            $item['reason_for_analysis'],
            $item['container_damage'],
            $item['temperature_condition'],
            $item['validity_status'],
            $sequence,
            $item['item_total_charge']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert sample item');
        }
        
        return $this->conn->insert_id;
    }

    /**
     * Insert sample tests for a sample item
     * 
     * @param int $sampleItemId Sample item ID
     * @param array $tests Array of test data
     * @param string $submissionType Submission type
     * @return void
     */
    private function insertSampleTests($sampleItemId, $tests, $submissionType) {
        $sql = "INSERT INTO sample_tests (
                    sample_item_id, parameter_id, variant_id, 
                    test_method_id, charge, is_swab
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $isSwab = ($submissionType === 'swab') ? 1 : 0;
        
        foreach ($tests as $test) {
            // Get default method for this parameter
            $methodId = getDefaultMethod($this->conn, $test['parameter_id']);
            
            if (!$methodId) {
                logError("No default method found for parameter {$test['parameter_id']}", 'insertSampleTests');
                continue; // Skip this test
            }
            
            $variantId = isset($test['variant_id']) ? $test['variant_id'] : null;
            
            $stmt->bind_param("iiiidi",
                $sampleItemId,
                $test['parameter_id'],
                $variantId,
                $methodId,
                $test['charge'],
                $isSwab
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert sample test');
            }
        }
    }
}
?>