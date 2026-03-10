<?php

/**
 * Sample Model - COMPLETE VERSION 3.0
 * Version: 3.0 - 100% Production Ready with Server Time
 * Date: February 5, 2026
 * CRITICAL FIX: bind_param now includes received_time
 */

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../Helpers/functions.php';

class SampleModel
{
    public $conn;

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
                    'message' => 'Client created successfully',
                    'client_id' => $this->conn->insert_id
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

    public function getParameters($submissionType = 'regular')
    {
        try {
            $sql = "SELECT 
                        tp.parameter_id, 
                        tp.parameter_code,
                        tp.parameter_name, 
                        tp.has_variants,
                        tp.swab_enabled,
                        tp.base_unit,
                        tp.parameter_category,
                        tp.display_format,
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

            if ($submissionType === 'swab') {
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

                    if ($submissionType === 'swab' && $row['swab_enabled'] == 1) {
                        $basePrice += (float)$row['swab_price'];
                    }

                    $parameters[$paramId] = [
                        'parameter_id' => $paramId,
                        'parameter_code' => $row['parameter_code'],
                        'parameter_name' => $row['parameter_name'],
                        'base_unit' => $row['base_unit'],
                        'category' => $row['parameter_category'],
                        'display_format' => $row['display_format'] ?? 'normal',
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

    public function getCombos()
    {
        try {
            $sql = "SELECT 
                        pc.combo_id,
                        pc.combo_name,
                        cp.test_charge AS combo_price,
                        GROUP_CONCAT(ci.parameter_id ORDER BY ci.parameter_id) AS parameter_ids
                    FROM parameter_combinations pc
                    JOIN combination_pricing cp ON pc.combo_id = cp.combo_id
                    JOIN combination_items ci ON pc.combo_id = ci.combo_id
                    WHERE pc.is_active = 1 
                      AND pc.is_deleted = 0
                      AND cp.is_active = 1
                      AND cp.is_deleted = 0
                    GROUP BY pc.combo_id
                    ORDER BY COUNT(ci.parameter_id) DESC";

            $result = $this->conn->query($sql);
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }

            $combos = [];
            while ($row = $result->fetch_assoc()) {
                $paramIds = array_map('intval', explode(',', $row['parameter_ids']));

                $individualTotal = 0;
                foreach ($paramIds as $paramId) {
                    $priceResult = $this->conn->query(
                        "SELECT test_charge FROM parameter_pricing 
                         WHERE parameter_id = $paramId AND is_active = 1 AND is_deleted = 0"
                    );
                    if ($priceRow = $priceResult->fetch_assoc()) {
                        $individualTotal += (float)$priceRow['test_charge'];
                    }
                }

                $combos[] = [
                    'combo_id' => (int)$row['combo_id'],
                    'combo_name' => $row['combo_name'],
                    'parameter_ids' => $paramIds,
                    'combo_price' => (float)$row['combo_price'],
                    'individual_total' => $individualTotal,
                    'savings' => $individualTotal - (float)$row['combo_price']
                ];
            }

            return [
                'success' => true,
                'combos' => $combos,
                'count' => count($combos)
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::getCombos');
            return [
                'success' => false,
                'message' => 'Failed to load combos',
                'combos' => []
            ];
        }
    }

    public function searchSampleNames($query, $submissionType = 'regular')
    {
        try {
            $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";

            // ✅ FIX: Filter by category based on submission type
            // Regular submission: Water (1) + Food (2) samples
            // Swab submission: Swab (3) samples only
            $categoryFilter = '';
            if ($submissionType === 'swab') {
                $categoryFilter = "AND sn.category_id = 3"; // Only Swab samples
            } else {
                $categoryFilter = "AND sn.category_id IN (1, 2)"; // Water + Food samples
            }

            $sql = "SELECT 
                        sn.sample_name, 
                        sn.usage_count,
                        sn.category_id,
                        COALESCE(stc.category_name, 'Other') AS category_name,
                        CASE 
                            WHEN LOWER(sn.sample_name) = LOWER(?) THEN 3
                            WHEN LOWER(sn.sample_name) LIKE LOWER(CONCAT(?, '%')) THEN 2
                            ELSE 1
                        END as relevance
                    FROM sample_names sn
                    LEFT JOIN sample_type_categories stc ON sn.category_id = stc.category_id
                    WHERE LOWER(sn.sample_name) LIKE LOWER(?)
                    $categoryFilter
                    ORDER BY relevance DESC, sn.usage_count DESC, sn.sample_name ASC 
                    LIMIT 10";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $exactMatch = $query;
            $startsWithMatch = $query;

            $stmt->bind_param("sss", $exactMatch, $startsWithMatch, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            $names = [];
            while ($row = $result->fetch_assoc()) {
                $names[] = [
                    'sample_name' => $row['sample_name'],
                    'usage_count' => $row['usage_count'],
                    'category_id' => $row['category_id'] ? (int)$row['category_id'] : null,
                    'category_name' => $row['category_name']
                ];
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

    public function searchCities($query)
    {
        try {
            $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";

            $sql = "SELECT city_id, city_name 
                    FROM cities 
                    WHERE is_active = 1 
                      AND is_deleted = 0
                      AND LOWER(city_name) LIKE LOWER(?) 
                    ORDER BY usage_count DESC, city_name ASC 
                    LIMIT 20";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            $cities = [];
            while ($row = $result->fetch_assoc()) {
                $cities[] = [
                    'city_id' => (int)$row['city_id'],
                    'city_name' => $row['city_name']
                ];
            }

            return [
                'success' => true,
                'cities' => $cities,
                'count' => count($cities)
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::searchCities');
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'cities' => [],
                'count' => 0
            ];
        }
    }

    public function findCityByName($cityName)
    {
        try {
            if (empty($cityName)) {
                return null;
            }

            $sql = "SELECT city_id, city_name 
                    FROM cities 
                    WHERE LOWER(city_name) = LOWER(?) 
                      AND is_active = 1 
                      AND is_deleted = 0 
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("s", $cityName);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                return [
                    'success' => true,
                    'city_id' => (int)$row['city_id'],
                    'city_name' => $row['city_name']
                ];
            }

            return [
                'success' => false,
                'message' => 'City not found',
                'city_id' => null,
                'city_name' => $cityName
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::findCityByName');
            return null;
        }
    }

    public function incrementCityUsage($cityId)
    {
        try {
            $sql = "UPDATE cities 
                    SET usage_count = usage_count + 1 
                    WHERE city_id = ? 
                      AND is_active = 1 
                      AND is_deleted = 0";

            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("i", $cityId);
            return $stmt->execute();
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::incrementCityUsage');
            return false;
        }
    }

    public function saveSample($data)
    {
        $this->conn->begin_transaction();

        try {
            $samplesData = is_array($data['samples'])
                ? $data['samples']
                : json_decode($data['samples'], true);

            $sampleCount = count($samplesData);

            $formGen = generateFormNumber($this->conn, $sampleCount);

            if (!$formGen['success']) {
                throw new Exception($formGen['message']);
            }

            $formNumber = $formGen['form_number'];
            $reportRef = $formGen['base_number'];
            $acReference = generateQCReference($formNumber);

            $sampleId = $this->insertSample($data, $formNumber, $reportRef);

            $sampleItemIds = [];
            foreach ($samplesData as $index => $item) {
                $itemId = $this->insertSampleItem($sampleId, $item, $index + 1);
                $sampleItemIds[] = $itemId;
            }

            $testsData = is_array($data['tests'])
                ? $data['tests']
                : json_decode($data['tests'], true);

            $comboCalc = $data['combo_calculation'] ?? null;
            $this->insertSampleTests($sampleItemIds, $testsData, $data['submission_type'], $comboCalc);

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

            // Insert extra items if any
            $extraItemsData = $data['extra_items'] ?? [];
            if (!empty($extraItemsData)) {
                $this->insertSampleExtraItems($sampleId, $extraItemsData);
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Sample submitted successfully',
                'form_number' => $formNumber,
                'sample_id' => $sampleId,
                'report_ref' => $reportRef,
                'ac_reference' => $acReference
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            logError($e->getMessage(), 'SampleModel::saveSample');
            return [
                'success' => false,
                'message' => 'Failed to save sample: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ✅ CRITICAL FIX: bind_param now includes received_time
     */
    private function insertSample($data, $formNumber, $reportRef)
    {
        $sql = "INSERT INTO samples (
                    client_id, sample_code, form_number, report_ref, city_id, submission_type,
                    received_date, received_time, tentative_date,
                    analysis_start_date,
                    sample_collected_date, sample_collected_time,
                    submitted_by,
                    additional_charges, test_charges_total, grand_total,
                    payment_status, payment_reference, status, status_updated_at, status_updated_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (samples): " . $this->conn->error);
        }

        $clientId = $data['client_id'];
        $sampleCode = $formNumber;
        $cityId = $data['city_id'] ?? null;
        $submissionType = $data['submission_type'];
        $receivedDate = $data['received_date'];
        $receivedTime = $data['received_time'] ?? '00:00:00';
        $tentativeDate = $data['tentative_date'];
        $analysisStartDate = $data['received_date']; // Sync Analysis Start Date with Received Date
        $collectedDate = !empty($data['sample_collected_date']) ? $data['sample_collected_date'] : null;
        $collectedTime = !empty($data['sample_collected_time']) ? $data['sample_collected_time'] : null;
        $submittedBy = $data['submitted_by'];
        $additionalCharges = $data['additional_charges'];
        $testChargesTotal = $data['test_charges_total'];
        $grandTotal = $data['grand_total'];
        $paymentStatus = $data['payment_status'];
        $paymentReference = ($paymentStatus === 'Paid' && !empty($data['payment_reference']))
            ? $data['payment_reference']
            : null;
        $statusUpdatedBy = $submittedBy;

        $stmt->bind_param(
            "isssissssssssdddsss",
            $clientId,
            $sampleCode,
            $formNumber,
            $reportRef,
            $cityId,
            $submissionType,
            $receivedDate,
            $receivedTime,
            $tentativeDate,
            $analysisStartDate,
            $collectedDate,
            $collectedTime,
            $submittedBy,
            $additionalCharges,
            $testChargesTotal,
            $grandTotal,
            $paymentStatus,
            $paymentReference,
            $statusUpdatedBy
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
                    temperature_condition, temperature_value, container_item_id,
                    sample_category_id, validity_status, sequence_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_items): " . $this->conn->error);
        }

        $sampleName = $item['sample_name'];
        $value = $item['value'] ?? null;
        $unit = $item['unit'] ?? null;
        $clientSampleCode = $item['client_sample_code'] ?? null;
        $samplingLocation = $item['sampling_location'] ?? null;
        $reasonForAnalysis = $item['reason_for_analysis'] ?? null;
        $containerDamage = $item['container_damage'] ?? 'No';
        $temperatureCondition = $item['temperature_condition'] ?? 'Ambient';
        $temperatureValue = isset($item['temperature_value']) && $item['temperature_value'] !== ''
            ? (float)$item['temperature_value'] : null;
        $containerItemId = !empty($item['container_item_id']) ? (int)$item['container_item_id'] : null;
        $sampleCategoryId = !empty($item['sample_category_id']) ? (int)$item['sample_category_id'] : null;
        $validityStatus = $item['validity_status'] ?? 'OK';

        $stmt->bind_param(
            "issssssssdiisi",
            $sampleId,
            $sampleName,
            $value,
            $unit,
            $clientSampleCode,
            $samplingLocation,
            $reasonForAnalysis,
            $containerDamage,
            $temperatureCondition,
            $temperatureValue,
            $containerItemId,
            $sampleCategoryId,
            $validityStatus,
            $sequenceNumber
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed (sample_items): " . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    private function insertSampleTests($sampleItemIds, $testsData, $submissionType, $comboCalc = null)
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

            $parameterToCombo = [];
            foreach ($detectedCombos as $combo) {
                foreach ($combo['parameter_ids'] as $paramId) {
                    $parameterToCombo[$paramId] = $combo;
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
                $comboId = null;
                $isComboApplied = 0;

                if (isset($parameterToCombo[$test['parameter_id']])) {
                    $combo = $parameterToCombo[$test['parameter_id']];
                    $comboId = $combo['combo_id'];
                    $isComboApplied = 1;
                    $charge = $combo['combo_price'] / $combo['param_count'];
                } else {
                    $charge = (float)$test['charge'];
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

    /**
     * ✅ UPDATED: Now includes received_time
     */
    private function insertSampleAcceptance($sampleId, $acReference, $firstSample, $data)
    {
        $sql = "INSERT INTO sample_acceptance (
                    sample_id, report_ref, received_by, received_time, container_damage,
                    temperature_condition, validity_ok, tentative_date, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_acceptance): " . $this->conn->error);
        }

        $receivedBy = $data['submitted_by'];
        $receivedTime = $data['received_time'] ?? '00:00:00'; // ✅ ADD received_time
        $containerDamage = $firstSample['container_damage'] ?? 'No';
        $temperatureCondition = $firstSample['temperature_condition'] ?? 'Ambient';
        $validityOk = ($firstSample['validity_status'] ?? 'OK') === 'OK' ? 'OK' : 'Not OK';
        $tentativeDate = $data['tentative_date'];
        $remarks = null;

        $stmt->bind_param(
            "issssssss",
            $sampleId,
            $acReference,
            $receivedBy,
            $receivedTime,        // ✅ ADDED
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
        $paymentReference = ($paymentStatus === 'Paid' && !empty($data['payment_reference']))
            ? $data['payment_reference']
            : null;
        $acknowledgedBy = $data['submitted_by'];
        $notes = $data['additional_notes'] ?? null;

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

    /**
     * Insert extra items purchased with the submission
     */
    private function insertSampleExtraItems($sampleId, $extraItems)
    {
        if (empty($extraItems)) return;

        $sql = "INSERT INTO sample_extra_items (sample_id, item_id, quantity, unit_price, line_total)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (sample_extra_items): " . $this->conn->error);
        }

        foreach ($extraItems as $extra) {
            $itemId = (int)$extra['item_id'];
            $quantity = (int)$extra['quantity'];
            $unitPrice = (float)$extra['unit_price'];
            $lineTotal = $unitPrice * $quantity;

            $stmt->bind_param("iiidd", $sampleId, $itemId, $quantity, $unitPrice, $lineTotal);
            if (!$stmt->execute()) {
                throw new Exception("Insert failed (sample_extra_items): " . $stmt->error);
            }
        }
    }

    /**
     * Get all active extra items for the submission form
     */
    public function getExtraItems()
    {
        try {
            $sql = "SELECT item_id, item_name, item_value, item_unit, item_price, item_description
                    FROM extra_items
                    WHERE is_active = 1 AND is_deleted = 0
                    ORDER BY display_order ASC, item_name ASC";

            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new Exception("Query failed: " . $this->conn->error);
            }

            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = [
                    'item_id' => (int)$row['item_id'],
                    'item_name' => $row['item_name'],
                    'item_value' => $row['item_value'],
                    'item_unit' => $row['item_unit'],
                    'item_price' => (float)$row['item_price'],
                    'item_description' => $row['item_description']
                ];
            }

            return [
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::getExtraItems');
            return [
                'success' => false,
                'message' => 'Error fetching extra items',
                'items' => []
            ];
        }
    }

    /**
     * Bulk create new sample names with categories (from interceptor modal)
     */
    public function bulkCreateSampleNames($names)
    {
        try {
            $created = [];

            $checkStmt = $this->conn->prepare(
                "SELECT sample_name_id FROM sample_names WHERE LOWER(sample_name) = LOWER(?)"
            );

            $insertStmt = $this->conn->prepare(
                "INSERT INTO sample_names (sample_name, category_id, is_slab_accredited, usage_count, created_at) VALUES (?, ?, ?, 0, NOW())"
            );

            foreach ($names as $entry) {
                $name = trim($entry['name'] ?? '');
                $categoryId = !empty($entry['category_id']) ? (int)$entry['category_id'] : 4;
                $isSlabAccredited = isset($entry['is_slab_accredited']) ? (int)$entry['is_slab_accredited'] : 0;

                if (empty($name)) continue;

                // Check if exists
                $checkStmt->bind_param("s", $name);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows === 0) {
                    $insertStmt->bind_param("sii", $name, $categoryId, $isSlabAccredited);
                    if ($insertStmt->execute()) {
                        $created[] = [
                            'name' => $name,
                            'category_id' => $categoryId,
                            'is_slab_accredited' => $isSlabAccredited,
                            'id' => $this->conn->insert_id
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'created' => $created,
                'count' => count($created),
                'message' => count($created) . ' sample name(s) created'
            ];
        } catch (Exception $e) {
            logError($e->getMessage(), 'SampleModel::bulkCreateSampleNames');
            return [
                'success' => false,
                'message' => 'Error creating sample names: ' . $e->getMessage()
            ];
        }
    }
}
