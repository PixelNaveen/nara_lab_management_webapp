<?php

/**
 * Sample Model - FINAL PERFECT VERSION 3.1
 * 
 * ALL FIXES:
 * 1. PHP 8.2 bind_param compatibility
 * 2. Payment reference NULL instead of empty string
 * 3. Sample code uses form_number
 * 4. Form sequence ONLY increments on successful commit
 * 
 * CRITICAL: Form number generation is now part of the main transaction
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';

class SampleModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function searchClients($query)
    {
        try {
            $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";

            $sql = "SELECT client_id, client_name, address_line1, city, 
                           phone_primary, contact_person
                    FROM clients
                    WHERE is_Active = 1
                      AND (LOWER(client_name) LIKE LOWER(?) 
                           OR LOWER(phone_primary) LIKE LOWER(?) 
                           OR LOWER(contact_person) LIKE LOWER(?))
                    ORDER BY client_name ASC
                    LIMIT 10";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
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
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function createClient($data)
    {
        try {
            $sql = "INSERT INTO clients (
                        client_name, address_line1, city, 
                        phone_primary, contact_person
                    ) VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $clientName = $data['client_name'];
            $addressLine1 = $data['address_line1'];
            $city = $data['city'];
            $phonePrimary = $data['phone_primary'];
            $contactPerson = $data['contact_person'];

            $stmt->bind_param(
                "sssss",
                $clientName,
                $addressLine1,
                $city,
                $phonePrimary,
                $contactPerson
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'client_id' => $this->conn->insert_id,
                    'message' => 'Client created successfully'
                ];
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::createClient');
            return [
                'success' => false,
                'message' => 'Failed to create client: ' . $e->getMessage()
            ];
        }
    }

    public function updateClient($data)
    {
        try {
            $sql = "UPDATE clients 
                    SET client_name = ?, 
                        address_line1 = ?, 
                        city = ?, 
                        phone_primary = ?, 
                        contact_person = ?
                    WHERE client_id = ?";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $clientName = $data['client_name'];
            $addressLine1 = $data['address_line1'];
            $city = $data['city'];
            $phonePrimary = $data['phone_primary'];
            $contactPerson = $data['contact_person'];
            $clientId = $data['client_id'];

            $stmt->bind_param(
                "sssssi",
                $clientName,
                $addressLine1,
                $city,
                $phonePrimary,
                $contactPerson,
                $clientId
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Client updated successfully'
                ];
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::updateClient');
            return [
                'success' => false,
                'message' => 'Failed to update client: ' . $e->getMessage()
            ];
        }
    }

    public function getParameters($type = 'regular')
    {
        try {
            $sql = "SELECT 
                        tp.parameter_id, 
                        tp.parameter_code,
                        tp.parameter_name, 
                        tp.has_variants,
                        tp.swab_enabled,
                        tp.base_unit,
                        pp.test_charge AS parameter_price,
                        COALESCE(sp.swab_price, 0) AS swab_price,
                        pv.variant_id, 
                        pv.variant_name, 
                        pv.full_display_name
                    FROM test_parameters tp
                    LEFT JOIN parameter_pricing pp ON tp.parameter_id = pp.parameter_id 
                        AND pp.is_active = 1 AND pp.is_deleted = 0
                    LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id
                        AND sp.is_active = 1 AND sp.is_deleted = 0
                    LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id 
                        AND pv.is_active = 1 AND pv.is_deleted = 0
                    WHERE tp.is_active = 1 AND tp.is_deleted = 0";

            if ($type === 'swab') {
                $sql .= " AND tp.swab_enabled = 1";
            }

            $sql .= " ORDER BY tp.parameter_code, pv.variant_name";

            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new Exception("Query failed: " . $this->conn->error);
            }

            $parameters = [];

            while ($row = $result->fetch_assoc()) {
                $paramId = $row['parameter_id'];

                if (!isset($parameters[$paramId])) {
                    $basePrice = (float)($row['parameter_price'] ?? 0);

                    if ($type === 'swab' && $row['swab_enabled'] == 1) {
                        $basePrice += (float)$row['swab_price'];
                    }

                    $parameters[$paramId] = [
                        'parameter_id' => $paramId,
                        'parameter_code' => $row['parameter_code'],
                        'parameter_name' => $row['parameter_name'],
                        'base_unit' => $row['base_unit'],
                        'price' => $basePrice,
                        'has_variants' => (bool)$row['has_variants'],
                        'swab_enabled' => (bool)$row['swab_enabled'],
                        'variants' => []
                    ];
                }

                if ($row['variant_id']) {
                    $parameters[$paramId]['variants'][] = [
                        'variant_id' => $row['variant_id'],
                        'variant_name' => $row['variant_name'],
                        'full_display_name' => $row['full_display_name'],
                        'price' => $parameters[$paramId]['price']
                    ];
                }
            }

            return [
                'success' => true,
                'parameters' => array_values($parameters),
                'count' => count($parameters)
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::getParameters');
            return [
                'success' => false,
                'message' => 'Error fetching parameters: ' . $e->getMessage()
            ];
        }
    }

    public function searchSampleNames($query)
    {
        try {
            $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";

            $sql = "SELECT sample_name, usage_count 
                    FROM sample_names 
                    WHERE sample_name LIKE ? 
                    ORDER BY usage_count DESC, sample_name ASC 
                    LIMIT 10";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            $names = [];
            while ($row = $result->fetch_assoc()) {
                $names[] = $row;
            }

            return [
                'success' => true,
                'names' => $names,
                'count' => count($names)
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
     * Save complete sample submission
     * CRITICAL: Form number generation is INSIDE this transaction
     * If anything fails, form_sequence is rolled back (number not wasted)
     */
    public function saveSample($data)
    {
        // CRITICAL: Start transaction FIRST
        $this->conn->begin_transaction();

        try {
            $samplesData = is_array($data['samples'])
                ? $data['samples']
                : json_decode($data['samples'], true);

            $sampleCount = count($samplesData);

            // CRITICAL: Generate form number WITHIN transaction
            // If submission fails later, this will be rolled back
            $formGen = generateFormNumber($this->conn, $sampleCount);

            if (!$formGen['success']) {
                throw new Exception($formGen['message']);
            }

            $formNumber = $formGen['form_number'];
            $reportRef = $formGen['base_number'];
            $acReference = generateACReference($formNumber);

            $sampleId = $this->insertSample($data, $formNumber, $reportRef);

            $sampleItemIds = [];
            foreach ($samplesData as $index => $item) {
                $itemId = $this->insertSampleItem($sampleId, $item, $index + 1);
                $sampleItemIds[] = $itemId;
            }

            $testsData = is_array($data['tests'])
                ? $data['tests']
                : json_decode($data['tests'], true);

            $this->insertSampleTests($sampleItemIds, $testsData, $data['submission_type']);

            $this->insertSampleAcceptance(
                $sampleId,
                $acReference,
                $samplesData[0],
                $data
            );

            $this->insertSampleAcknowledgement(
                $sampleId,
                $acReference,
                $data
            );

            // CRITICAL: Only commit if everything succeeded
            // This COMMITS the form_sequence increment
            $this->conn->commit();

            return [
                'success' => true,
                'form_number' => $formNumber,
                'sample_id' => $sampleId,
                'ac_reference' => $acReference,
                'message' => 'Sample submitted successfully'
            ];
        } catch (Exception $e) {
            // CRITICAL: Rollback on ANY error
            // This REVERTS the form_sequence increment
            $this->conn->rollback();
            logError($e->getMessage(), 'SampleModel::saveSample');
            return [
                'success' => false,
                'message' => 'Failed to save sample: ' . $e->getMessage()
            ];
        }
    }

    private function insertSample($data, $formNumber, $reportRef)
    {
        $sql = "INSERT INTO samples (
                    client_id, sample_code, form_number, report_ref, submission_type,
                    received_date, tentative_date, submitted_by, additional_notes,
                    additional_charges, test_charges_total, grand_total,
                    payment_status, payment_reference
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (samples): " . $this->conn->error);
        }

        $clientId           = (int)$data['client_id'];
        $sampleCode         = $formNumber;
        $submissionType     = $data['submission_type'];
        $receivedDate       = $data['received_date'];
        $tentativeDate      = $data['tentative_date'];
        $submittedBy        = $data['submitted_by'];
        $additionalNotes    = $data['additional_notes'] ?? '';
        $additionalCharges  = (float)$data['additional_charges'];
        $testChargesTotal   = (float)$data['test_charges_total'];
        $grandTotal         = (float)$data['grand_total'];
        $paymentStatus      = $data['payment_status'];
        $paymentReference   = !empty($data['payment_reference']) ? $data['payment_reference'] : null;

        $stmt->bind_param(
            "issssssssdddss",
            $clientId,
            $sampleCode,
            $formNumber,
            $reportRef,
            $submissionType,
            $receivedDate,
            $tentativeDate,
            $submittedBy,
            $additionalNotes,
            $additionalCharges,
            $testChargesTotal,
            $grandTotal,
            $paymentStatus,
            $paymentReference
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed (samples): " . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    private function insertSampleItem($sampleId, $item, $sequenceNumber)
    {
        $sql = "INSERT INTO sample_items (
                    sample_id, sample_name, value, unit, client_sample_code,
                    sampling_location, reason_for_analysis, container_damage,
                    temperature_condition, validity_status, sequence_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_items): " . $this->conn->error);
        }

        $sampleName           = $item['sample_name'] ?? '';
        $value                = $item['value'] ?? '';
        $unit                 = $item['unit'] ?? '';
        $clientSampleCode     = $item['client_sample_code'] ?? '';
        $samplingLocation     = $item['sampling_location'] ?? '';
        $reasonForAnalysis    = $item['reason_for_analysis'] ?? '';
        $containerDamage      = $item['container_damage'] ?? 'No';
        $temperatureCondition = $item['temperature_condition'] ?? 'Ambient';
        $validityStatus       = $item['validity_status'] ?? 'OK';

        $stmt->bind_param(
            "isssssssssi",
            $sampleId,
            $sampleName,
            $value,
            $unit,
            $clientSampleCode,
            $samplingLocation,
            $reasonForAnalysis,
            $containerDamage,
            $temperatureCondition,
            $validityStatus,
            $sequenceNumber
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed (sample_items): " . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    private function insertSampleTests($sampleItemIds, $testsData, $submissionType)
    {
        $sql = "INSERT INTO sample_tests (
                    sample_item_id, parameter_id, variant_id, test_method_id,
                    charge, is_swab, combo_id, is_combo_applied
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_tests): " . $this->conn->error);
        }

        $isSwab = ($submissionType === 'swab') ? 1 : 0;

        $testsBySample = [];
        foreach ($testsData as $test) {
            $sampleIndex = (int)$test['sample'] - 1;
            if (!isset($testsBySample[$sampleIndex])) {
                $testsBySample[$sampleIndex] = [];
            }
            $testsBySample[$sampleIndex][] = $test;
        }

        foreach ($testsBySample as $sampleIndex => $sampleTests) {
            if (!isset($sampleItemIds[$sampleIndex])) {
                continue;
            }

            $sampleItemId = $sampleItemIds[$sampleIndex];

            $detectedCombos = detectCombos($sampleTests, $this->conn, $submissionType);

            $usedInCombo = [];
            foreach ($detectedCombos as $combo) {
                foreach ($combo['parameter_ids'] as $paramId) {
                    $usedInCombo[$paramId] = $combo;
                }
            }

            foreach ($sampleTests as $test) {
                $methodId = getDefaultMethod($this->conn, $test['parameter_id']);
                if (!$methodId) {
                    logError("No method found for parameter {$test['parameter_id']}", 'insertSampleTests');
                    continue;
                }

                $parameterId = $test['parameter_id'];
                $variantId = !empty($test['variant_id']) ? (int)$test['variant_id'] : null;
                $charge = (float)$test['charge'];
                $comboId = null;
                $isComboApplied = 0;

                if (isset($usedInCombo[$test['parameter_id']])) {
                    $combo = $usedInCombo[$test['parameter_id']];
                    $comboId = $combo['combo_id'];
                    $isComboApplied = 1;
                    $charge = $combo['combo_price'] / $combo['param_count'];
                }

                $stmt->bind_param(
                    "iiididii",
                    $sampleItemId,
                    $parameterId,
                    $variantId,
                    $methodId,
                    $charge,
                    $isSwab,
                    $comboId,
                    $isComboApplied
                );

                if (!$stmt->execute()) {
                    throw new Exception("Insert failed (sample_tests): " . $stmt->error);
                }
            }
        }
    }

    private function insertSampleAcceptance($sampleId, $acReference, $firstSample, $data)
    {
        $sql = "INSERT INTO sample_acceptance (
                    sample_id, report_ref, received_by, container_damage,
                    temperature_condition, validity_ok, tentative_date, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_acceptance): " . $this->conn->error);
        }

        $containerDamage = $firstSample['container_damage'] ?? 'No';
        $temperatureCondition = $firstSample['temperature_condition'] ?? 'Ambient';
        $validityStatus = $firstSample['validity_status'] ?? 'OK';
        $validityOk = ($validityStatus === 'OK') ? 'OK' : 'Not OK';
        $remarks = null;
        $receivedBy = $data['submitted_by'];
        $tentativeDate = $data['tentative_date'];

        $stmt->bind_param(
            "isssssss",
            $sampleId,
            $acReference,
            $receivedBy,
            $containerDamage,
            $temperatureCondition,
            $validityOk,
            $tentativeDate,
            $remarks
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed (sample_acceptance): " . $stmt->error);
        }
    }

    private function insertSampleAcknowledgement($sampleId, $acReference, $data)
    {
        $sql = "INSERT INTO sample_acknowledgement (
                    sample_id, report_ref, test_charges, additional_charges,
                    total_charges, payment_status, payment_reference,
                    acknowledged_by, acknowledged_at, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_acknowledgement): " . $this->conn->error);
        }

        $testCharges = $data['test_charges_total'];
        $additionalCharges = $data['additional_charges'];
        $totalCharges = $data['grand_total'];
        $paymentStatus = $data['payment_status'];
        $paymentReference = !empty($data['payment_reference']) ? $data['payment_reference'] : null;
        $acknowledgedBy = $data['submitted_by'];
        $notes = $data['additional_notes'] ?? '';

        $stmt->bind_param(
            "isdddssss",
            $sampleId,
            $acReference,
            $testCharges,
            $additionalCharges,
            $totalCharges,
            $paymentStatus,
            $paymentReference,
            $acknowledgedBy,
            $notes
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed (sample_acknowledgement): " . $stmt->error);
        }
    }
}
