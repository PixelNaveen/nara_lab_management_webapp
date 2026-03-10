<?php
require_once __DIR__ . '/../../Config/Database.php';

class PricingModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    }

    // =================== INDIVIDUAL PRICING ===================

    /**
     * Get all individual prices with filters
     */
    public function getAllIndividualPrices($filters = [])
    {
        $sql = "SELECT 
                    pp.pricing_id,
                    pp.parameter_id,
                    pp.test_charge,
                    pp.is_active,
                    pp.created_at,
                    tp.parameter_name,
                    tp.parameter_code
                FROM parameter_pricing pp
                INNER JOIN test_parameters tp ON pp.parameter_id = tp.parameter_id
                WHERE pp.is_deleted = 0 AND tp.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND pp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND tp.parameter_name LIKE ?";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $types .= "s";
        }

        $sql .= " ORDER BY tp.parameter_name ASC";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $prices = [];
        while ($row = $result->fetch_assoc()) {
            $prices[] = $row;
        }

        return $prices;
    }

    /**
     * Get individual price by ID
     */
    public function getIndividualPriceById($pricing_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT pp.*, tp.parameter_name, tp.parameter_code
            FROM parameter_pricing pp
            INNER JOIN test_parameters tp ON pp.parameter_id = tp.parameter_id
            WHERE pp.pricing_id = ? AND pp.is_deleted = 0"
        );
        $stmt->bind_param("i", $pricing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Check if parameter already has pricing
     */
    public function hasIndividualPrice($parameter_id, $exclude_pricing_id = null)
    {
        $sql = "SELECT pricing_id FROM parameter_pricing 
                WHERE parameter_id = ? AND is_deleted = 0";
        
        $params = [$parameter_id];
        $types = "i";

        if ($exclude_pricing_id) {
            $sql .= " AND pricing_id != ?";
            $params[] = $exclude_pricing_id;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Find deleted individual price by parameter_id
     */
    public function findDeletedIndividualPrice($parameter_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT pricing_id FROM parameter_pricing 
            WHERE parameter_id = ? AND is_deleted = 1 
            LIMIT 1"
        );
        $stmt->bind_param("i", $parameter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Insert individual price
     */
    public function insertIndividualPrice($parameter_id, $test_charge, $is_active = 1)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO parameter_pricing 
            (parameter_id, test_charge, is_active, is_deleted, created_at)
            VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->bind_param("idi", $parameter_id, $test_charge, $is_active);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Reactivate deleted individual price
     */
    public function reactivateIndividualPrice($pricing_id, $test_charge, $is_active)
    {
        $stmt = $this->conn->prepare(
            "UPDATE parameter_pricing 
            SET test_charge = ?, is_active = ?, is_deleted = 0, updated_at = NOW()
            WHERE pricing_id = ?"
        );
        $stmt->bind_param("dii", $test_charge, $is_active, $pricing_id);
        return $stmt->execute();
    }

    /**
     * Update individual price
     */
    public function updateIndividualPrice($pricing_id, $parameter_id, $test_charge, $is_active)
    {
        $stmt = $this->conn->prepare(
            "UPDATE parameter_pricing 
            SET parameter_id = ?, test_charge = ?, is_active = ?, updated_at = NOW()
            WHERE pricing_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("idii", $parameter_id, $test_charge, $is_active, $pricing_id);
        return $stmt->execute();
    }

    /**
     * Soft delete individual price
     */
    public function softDeleteIndividualPrice($pricing_id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE parameter_pricing 
            SET is_deleted = 1, updated_at = NOW()
            WHERE pricing_id = ?"
        );
        $stmt->bind_param("i", $pricing_id);
        return $stmt->execute();
    }

    // =================== COMBO PRICING ===================

    /**
     * Generate next combo code (COMBO-001, COMBO-002, ...)
     */
    public function getNextComboCode()
    {
        $result = $this->conn->query(
            "SELECT combo_code FROM parameter_combinations 
             WHERE combo_code LIKE 'COMBO-%'
             ORDER BY combo_id DESC LIMIT 1"
        );
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = $row['combo_code'];
            preg_match('/COMBO-(\d+)/', $lastCode, $matches);
            $nextNum = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
            return 'COMBO-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }
        
        return 'COMBO-001';
    }

    /**
     * Generate combo name from parameter IDs
     * Returns: "Param1 + Param2 + Param3"
     */
    public function generateComboName($parameter_ids)
    {
        if (empty($parameter_ids) || !is_array($parameter_ids)) {
            return '';
        }
        
        $placeholders = implode(',', array_fill(0, count($parameter_ids), '?'));
        $types = str_repeat('i', count($parameter_ids));
        
        // Build ORDER BY FIELD clause
        $fieldOrder = implode(',', $parameter_ids);
        
        $stmt = $this->conn->prepare(
            "SELECT parameter_name 
             FROM test_parameters 
             WHERE parameter_id IN ($placeholders) 
             AND is_active = 1 AND is_deleted = 0
             ORDER BY FIELD(parameter_id, $fieldOrder)"
        );
        
        $stmt->bind_param($types, ...$parameter_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['parameter_name'];
        }
        
        return implode(' + ', $names);
    }

    /**
     * Check if exact combo exists (same parameters)
     */
    public function hasExactCombo($parameter_ids, $exclude_combo_id = null)
    {
        if (count($parameter_ids) < 2) {
            return false;
        }

        // Sort to ensure consistent comparison
        sort($parameter_ids);
        $paramSet = implode(',', $parameter_ids);

        $sql = "SELECT 
                    pc.combo_id,
                    GROUP_CONCAT(ci.parameter_id ORDER BY ci.parameter_id SEPARATOR ',') as param_set
                FROM parameter_combinations pc
                INNER JOIN combination_items ci ON pc.combo_id = ci.combo_id
                WHERE pc.is_deleted = 0";
        
        if ($exclude_combo_id) {
            $sql .= " AND pc.combo_id != ?";
        }
        
        $sql .= " GROUP BY pc.combo_id
                  HAVING param_set = ?";

        if ($exclude_combo_id) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $exclude_combo_id, $paramSet);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $paramSet);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Insert combo with auto-generated name and code
     */
    public function insertCombo($parameter_ids, $test_charge, $is_active = 1)
    {
        if (count($parameter_ids) < 2) {
            throw new Exception('At least 2 parameters required for combo');
        }
        
        // Generate combo name and code
        $combo_name = $this->generateComboName($parameter_ids);
        $combo_code = $this->getNextComboCode();
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // 1. Insert into parameter_combinations
            $stmt1 = $this->conn->prepare(
                "INSERT INTO parameter_combinations 
                (combo_name, combo_code, description, is_active, is_deleted, created_at)
                VALUES (?, ?, NULL, ?, 0, NOW())"
            );
            $stmt1->bind_param("ssi", $combo_name, $combo_code, $is_active);
            $stmt1->execute();
            $combo_id = $this->conn->insert_id;
            
            // 2. Insert into combination_items
            $stmt2 = $this->conn->prepare(
                "INSERT INTO combination_items 
                (combo_id, parameter_id, sequence_order, created_at)
                VALUES (?, ?, ?, NOW())"
            );
            
            foreach ($parameter_ids as $index => $param_id) {
                $stmt2->bind_param("iii", $combo_id, $param_id, $index);
                $stmt2->execute();
            }
            
            // 3. Insert into combination_pricing
            $stmt3 = $this->conn->prepare(
                "INSERT INTO combination_pricing 
                (combo_id, test_charge, is_active, is_deleted, created_at)
                VALUES (?, ?, ?, 0, NOW())"
            );
            $stmt3->bind_param("idi", $combo_id, $test_charge, $is_active);
            $stmt3->execute();
            
            $this->conn->commit();
            return $combo_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Get all combo prices with auto-generated display names
     */
    public function getAllComboPrices($filters = [])
    {
        $sql = "SELECT 
                    pc.combo_id,
                    pc.combo_name,
                    pc.combo_code,
                    cp.test_charge,
                    cp.is_active,
                    pc.created_at,
                    GROUP_CONCAT(
                        tp.parameter_name 
                        ORDER BY ci.sequence_order 
                        SEPARATOR ' + '
                    ) as combo_params
                FROM parameter_combinations pc
                INNER JOIN combination_pricing cp ON pc.combo_id = cp.combo_id
                INNER JOIN combination_items ci ON pc.combo_id = ci.combo_id
                INNER JOIN test_parameters tp ON ci.parameter_id = tp.parameter_id
                WHERE pc.is_deleted = 0 AND cp.is_deleted = 0";
        
        $params = [];
        $types = "";
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND cp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }
        
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND pc.combo_name LIKE ?";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $types .= "s";
        }
        
        $sql .= " GROUP BY pc.combo_id, pc.combo_name, pc.combo_code, 
                         cp.test_charge, cp.is_active, pc.created_at
                  ORDER BY pc.combo_id DESC";
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $combos = [];
        while ($row = $result->fetch_assoc()) {
            $combos[] = $row;
        }
        
        return $combos;
    }

    /**
     * Get combo by ID with parameter IDs
     */
    public function getComboPriceById($combo_id)
    {
        // Get combo details
        $stmt1 = $this->conn->prepare(
            "SELECT pc.*, cp.test_charge, cp.is_active
            FROM parameter_combinations pc
            INNER JOIN combination_pricing cp ON pc.combo_id = cp.combo_id
            WHERE pc.combo_id = ? AND pc.is_deleted = 0 AND cp.is_deleted = 0"
        );
        $stmt1->bind_param("i", $combo_id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $combo = $result1->fetch_assoc();

        if (!$combo) {
            return null;
        }

        // Get parameter IDs
        $stmt2 = $this->conn->prepare(
            "SELECT parameter_id 
            FROM combination_items 
            WHERE combo_id = ?
            ORDER BY sequence_order"
        );
        $stmt2->bind_param("i", $combo_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $parameter_ids = [];
        while ($row = $result2->fetch_assoc()) {
            $parameter_ids[] = $row['parameter_id'];
        }

        $combo['parameter_ids'] = $parameter_ids;
        return $combo;
    }

    /**
     * Update combo - regenerates name if parameters change
     */
    public function updateCombo($combo_id, $parameter_ids, $test_charge, $is_active)
    {
        if (count($parameter_ids) < 2) {
            throw new Exception('At least 2 parameters required for combo');
        }
        
        // Regenerate combo name
        $combo_name = $this->generateComboName($parameter_ids);
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // 1. Update parameter_combinations
            $stmt1 = $this->conn->prepare(
                "UPDATE parameter_combinations 
                SET combo_name = ?, is_active = ?, updated_at = NOW()
                WHERE combo_id = ? AND is_deleted = 0"
            );
            $stmt1->bind_param("sii", $combo_name, $is_active, $combo_id);
            $stmt1->execute();
            
            // 2. Delete old combination_items
            $stmt2 = $this->conn->prepare(
                "DELETE FROM combination_items WHERE combo_id = ?"
            );
            $stmt2->bind_param("i", $combo_id);
            $stmt2->execute();
            
            // 3. Insert new combination_items
            $stmt3 = $this->conn->prepare(
                "INSERT INTO combination_items 
                (combo_id, parameter_id, sequence_order, created_at)
                VALUES (?, ?, ?, NOW())"
            );
            
            foreach ($parameter_ids as $index => $param_id) {
                $stmt3->bind_param("iii", $combo_id, $param_id, $index);
                $stmt3->execute();
            }
            
            // 4. Update combination_pricing
            $stmt4 = $this->conn->prepare(
                "UPDATE combination_pricing 
                SET test_charge = ?, is_active = ?, updated_at = NOW()
                WHERE combo_id = ? AND is_deleted = 0"
            );
            $stmt4->bind_param("dii", $test_charge, $is_active, $combo_id);
            $stmt4->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Soft delete combo (cascades to pricing)
     */
    public function softDeleteCombo($combo_id)
    {
        $this->conn->begin_transaction();
        
        try {
            // Delete parameter_combinations
            $stmt1 = $this->conn->prepare(
                "UPDATE parameter_combinations 
                SET is_deleted = 1, updated_at = NOW()
                WHERE combo_id = ?"
            );
            $stmt1->bind_param("i", $combo_id);
            $stmt1->execute();
            
            // Delete combination_pricing
            $stmt2 = $this->conn->prepare(
                "UPDATE combination_pricing 
                SET is_deleted = 1, updated_at = NOW()
                WHERE combo_id = ?"
            );
            $stmt2->bind_param("i", $combo_id);
            $stmt2->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // =================== HELPER METHODS ===================

    /**
     * Get all active parameters for dropdowns
     */
    public function getActiveParameters()
    {
        $stmt = $this->conn->prepare(
            "SELECT parameter_id, parameter_name, parameter_code
            FROM test_parameters
            WHERE is_active = 1 AND is_deleted = 0
            ORDER BY parameter_name ASC"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $parameters = [];
        while ($row = $result->fetch_assoc()) {
            $parameters[] = $row;
        }

        return $parameters;
    }
}