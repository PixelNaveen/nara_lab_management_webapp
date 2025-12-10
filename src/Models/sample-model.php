<?php
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

   /**
 * Searches for clients based on a query (case-insensitive).
 */
public function searchClients($query)
{
    // Sanitize the input
    $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";

    // The key change is using LOWER() on both the column and the search term
    // This makes the search case-insensitive (e.g., 'abc' finds 'ABC')
    $sql = "SELECT client_id, client_name, address_line1, city, phone_primary, contact_person
            FROM clients
            WHERE is_Active = 1
            AND (LOWER(client_name) LIKE LOWER(?) OR LOWER(phone_primary) LIKE LOWER(?) OR LOWER(contact_person) LIKE LOWER(?))
            ORDER BY client_name ASC
            LIMIT 10";
    
    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) {
        logError($this->conn->error, 'SampleModel::searchClients - Prepare failed');
        return ['success' => false, 'message' => 'Database error preparing query.'];
    }

    // Bind the same sanitized search term to all three placeholders
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $clients = $result->fetch_all(MYSQLI_ASSOC);
        return ['success' => true, 'clients' => $clients];
    } else {
        logError($stmt->error, 'SampleModel::searchClients - Execute failed');
        return ['success' => false, 'message' => 'Database error executing query.'];
    }
}

    /**
     * Creates a new client.
     */
    public function createClient($data)
    {
        $sql = "INSERT INTO clients (client_name, address_line1, city, phone_primary, contact_person) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            logError($this->conn->error, 'SampleModel::createClient - Prepare failed');
            return ['success' => false, 'message' => 'Database error preparing query.'];
        }

        $stmt->bind_param("sssss", 
            $data['client_name'], 
            $data['address_line1'], 
            $data['city'], 
            $data['phone_primary'], 
            $data['contact_person']
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'client_id' => $this->conn->insert_id];
        } else {
            logError($stmt->error, 'SampleModel::createClient - Execute failed');
            return ['success' => false, 'message' => 'Failed to create client.'];
        }
    }

    /**
     * Updates an existing client.
     */
    public function updateClient($data)
    {
        $sql = "UPDATE clients SET client_name = ?, address_line1 = ?, city = ?, phone_primary = ?, contact_person = ? WHERE client_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            logError($this->conn->error, 'SampleModel::updateClient - Prepare failed');
            return ['success' => false, 'message' => 'Database error preparing query.'];
        }

        $stmt->bind_param("sssssi", 
            $data['client_name'], 
            $data['address_line1'], 
            $data['city'], 
            $data['phone_primary'], 
            $data['contact_person'], 
            $data['client_id']
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Client updated successfully.'];
        } else {
            logError($stmt->error, 'SampleModel::updateClient - Execute failed');
            return ['success' => false, 'message' => 'No changes made or client not found.'];
        }
    }

    /**
     * Fetches all test parameters, including pricing and variants.
     */
    public function getParameters($type = 'regular')
    {
        $sql = "SELECT tp.parameter_id, tp.parameter_name, tp.has_variants,
                   pp.test_charge AS price,
                   pv.variant_id, pv.variant_name, pv.full_display_name
            FROM test_parameters tp
            LEFT JOIN parameter_pricing pp ON tp.parameter_id = pp.parameter_id AND pp.is_active = 1
            LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id AND pv.is_active = 1
            WHERE tp.is_active = 1 AND tp.is_deleted = 0";
        
        if ($type === 'swab') {
            $sql .= " AND tp.swab_enabled = 1";
        }
        
        $sql .= " ORDER BY tp.parameter_name, pv.variant_name";

        $result = $this->conn->query($sql);
        if ($result === false) {
            logError($this->conn->error, 'SampleModel::getParameters - Query failed');
            return ['success' => false, 'message' => 'Error fetching parameters.'];
        }

        $parameters = [];
        while ($row = $result->fetch_assoc()) {
            $paramId = $row['parameter_id'];
            if (!isset($parameters[$paramId])) {
                $parameters[$paramId] = [
                    'id' => $paramId,
                    'name' => $row['parameter_name'],
                    'price' => (float)$row['price'],
                    'has_variants' => (bool)$row['has_variants'],
                    'variants' => []
                ];
            }
            if ($row['variant_id']) {
                $parameters[$paramId]['variants'][] = [
                    'id' => $row['variant_id'],
                    'name' => $row['variant_name'],
                    'full_name' => $row['full_display_name']
                ];
            }
        }
        return ['success' => true, 'parameters' => array_values($parameters)];
    }

    /**
     * Searches for sample names based on a query.
     */
    public function searchSampleNames($query)
    {
        $searchTerm = "%" . $this->conn->real_escape_string($query) . "%";
        $sql = "SELECT sample_name, usage_count FROM sample_names WHERE sample_name LIKE ? ORDER BY usage_count DESC, sample_name ASC LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            logError($this->conn->error, 'SampleModel::searchSampleNames - Prepare failed');
            return ['success' => false, 'message' => 'Database error preparing query.'];
        }

        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            return ['success' => true, 'names' => $result->fetch_all(MYSQLI_ASSOC)];
        } else {
            logError($stmt->error, 'SampleModel::searchSampleNames - Execute failed');
            return ['success' => false, 'message' => 'Error searching sample names.'];
        }
    }

    /**
     * Saves the entire sample submission (samples, items, tests) in a transaction.
     */
    public function saveSample($data)
    {
        $this->conn->begin_transaction();
        try {
            // 1. Get Form Number
            $year = date('Y');
            $this->conn->query("LOCK TABLES form_sequence WRITE");
            $res = $this->conn->query("SELECT current_number FROM form_sequence WHERE year = {$year} FOR UPDATE");
            $row = $res->fetch_assoc();
            $newNumber = ($row['current_number'] ?? 0) + 1;
            $formNumber = "SIF-{$year}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            
            $updateSql = "INSERT INTO form_sequence (year, current_number) VALUES (?, ?) ON DUPLICATE KEY UPDATE current_number = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("iii", $year, $newNumber, $newNumber);
            $stmt->execute();
            $this->conn->query("UNLOCK TABLES");

            // 2. Insert into `samples` table
            $sql = "INSERT INTO samples (client_id, sample_code, form_number, submission_type, received_date, tentative_date, submitted_by, additional_notes, additional_charges, test_charges_total, grand_total, payment_status, payment_reference) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) throw new Exception("Prepare failed for samples insert: " . $this->conn->error);
            
            $stmt->bind_param("isssssssddsss", 
                $data['client_id'], 
                $data['sample_code'], 
                $formNumber,
                $data['submission_type'],
                $data['received_date'],
                $data['tentative_date'],
                $data['submitted_by'],
                $data['additional_notes'],
                $data['additional_charges'],
                $data['test_charges_total'],
                $data['grand_total'],
                $data['payment_status'],
                $data['payment_reference']
            );
            $stmt->execute();
            $sampleId = $this->conn->insert_id;

            // 3. Insert into `sample_items` table
            $sampleItemsData = json_decode($data['samples'], true);
            $itemStmt = $this->conn->prepare("INSERT INTO sample_items (sample_id, sample_name, value, unit, client_sample_code, sampling_location, reason_for_analysis, container_damage, temperature_condition, validity_status, sequence_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($itemStmt === false) throw new Exception("Prepare failed for sample_items insert: " . $this->conn->error);

            $sampleItemIds = [];
            foreach ($sampleItemsData as $index => $item) {
                $itemStmt->bind_param("isssssssssi", 
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
                    $index + 1
                );
                $itemStmt->execute();
                $sampleItemIds[] = $this->conn->insert_id;
            }

            // 4. Insert into `sample_tests` table
            $testsData = json_decode($data['tests'], true);
            $testStmt = $this->conn->prepare("INSERT INTO sample_tests (sample_item_id, parameter_id, variant_id, test_method_id, charge) VALUES (?, ?, ?, ?, ?)");
            if ($testStmt === false) throw new Exception("Prepare failed for sample_tests insert: " . $this->conn->error);

            foreach ($testsData as $test) {
                $sampleItemIndex = (int)$test['sample'] - 1;
                if (!isset($sampleItemIds[$sampleItemIndex])) continue; // Skip if sample item doesn't exist
                
                $sampleItemId = $sampleItemIds[$sampleItemIndex];
                $methodRes = $this->conn->query("SELECT method_id FROM parameter_methods WHERE parameter_id = {$test['parameter_id']} AND is_default = 1");
                $methodRow = $methodRes->fetch_assoc();
                $methodId = $methodRow['method_id'] ?? 0;

                $testStmt->bind_param("iiidd", 
                    $sampleItemId, 
                    $test['parameter_id'], 
                    $test['variant_id'] ?: null, 
                    $methodId,
                    $test['charge']
                );
                $testStmt->execute();
            }

            $this->conn->commit();
            return ['success' => true, 'form_number' => $formNumber];
        } catch (Exception $e) {
            $this->conn->rollback();
            logError($e->getMessage(), 'SampleModel::saveSample');
            return ['success' => false, 'message' => 'Failed to save sample. Transaction rolled back.'];
        }
    }
}