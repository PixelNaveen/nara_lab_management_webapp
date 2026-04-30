<?php
require_once __DIR__ . '/../../Config/Database.php';

class SwabModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Get all swab_param records (only non-deleted, swab-enabled parameters)
     * This page is ONLY for updating prices of existing swab-enabled parameters
     */
    public function getAllSwabParams($filters = [])
    {
        $sql = "SELECT 
                    sp.swab_param_id,
                    sp.param_id,
                    sp.swab_price,
                    sp.is_active,
                    tp.parameter_name,
                    tp.parameter_code
                FROM swab_param sp
                INNER JOIN test_parameters tp ON sp.param_id = tp.parameter_id
                WHERE sp.is_deleted = 0 AND tp.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND sp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($filters['search']) && trim($filters['search']) !== '') {
            $sql .= " AND (tp.parameter_name LIKE ? OR tp.parameter_code LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        $sql .= " ORDER BY tp.parameter_name ASC";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'swab_param_id' => $r['swab_param_id'],
                'param_id' => $r['param_id'],
                'name' => $r['parameter_name'],
                'code' => $r['parameter_code'],
                'price' => number_format((float)$r['swab_price'], 2, '.', ''),
                'is_active' => intval($r['is_active'])
            ];
        }

        return $rows;
    }

    /**
     * Get single swab record by swab_param_id for editing
     */
    public function getSwabById($swabParamId)
    {
        $stmt = $this->conn->prepare(
            "SELECT sp.swab_param_id, sp.param_id, sp.swab_price, sp.is_active,
                    tp.parameter_name, tp.parameter_code
             FROM swab_param sp
             INNER JOIN test_parameters tp ON sp.param_id = tp.parameter_id
             WHERE sp.swab_param_id = ? AND sp.is_deleted = 0
             LIMIT 1"
        );
        $stmt->bind_param("i", $swabParamId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Update swab price and status
     */
    public function updateSwabPrice($swabParamId, $price, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param
             SET swab_price = ?, is_active = ?, updated_at = NOW()
             WHERE swab_param_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("dii", $price, $isActive, $swabParamId);
        return $stmt->execute();
    }

    /**
     * ✅ CRITICAL: Get parameters dropdown (only swab-enabled parameters WITHOUT existing swab_param records)
     * This query is CORRECT and will work properly after removing auto-creation logic from parameter-controller
     */
    public function getParametersDropdown()
    {
        $sql = "SELECT tp.parameter_id, tp.parameter_name, tp.parameter_code
                FROM test_parameters tp
                LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id AND sp.is_deleted = 0
                WHERE tp.is_deleted = 0 
                AND tp.swab_enabled = 1
                AND sp.swab_param_id IS NULL
                ORDER BY tp.parameter_name ASC";
        $res = $this->conn->query($sql);
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Get ALL active swab-enabled parameters (for combo multi-select dropdown)
     * This includes parameters that already have individual pricing
     */
    public function getAllSwabEnabledParams()
    {
        $sql = "SELECT tp.parameter_id, tp.parameter_name, tp.parameter_code
                FROM test_parameters tp
                WHERE tp.is_deleted = 0 
                AND tp.swab_enabled = 1
                AND tp.is_active = 1
                ORDER BY tp.parameter_name ASC";
        $res = $this->conn->query($sql);
        if (!$res) {
            error_log("SwabModel::getAllSwabEnabledParams error: " . $this->conn->error);
            return [];
        }
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Check if swab_param already exists for a parameter
     */
    public function findByParamId($paramId)
    {
        $stmt = $this->conn->prepare("SELECT swab_param_id, is_deleted FROM swab_param WHERE param_id = ? LIMIT 1");
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Insert new swab_param record
     */
    public function insertSwab($paramId, $price, $isActive = 1)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO swab_param (param_id, swab_price, is_active, is_deleted, created_at, updated_at)
             VALUES (?, ?, ?, 0, NOW(), NOW())"
        );
        $stmt->bind_param("idi", $paramId, $price, $isActive);
        return $stmt->execute();
    }

    /**
     * Reactivate previously deleted swab record
     */
    public function reactivateSwabByParam($paramId, $price, $isActive = 1)
    {
        $stmt = $this->conn->prepare("SELECT swab_param_id FROM swab_param WHERE param_id = ? AND is_deleted = 1 LIMIT 1");
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $stmt2 = $this->conn->prepare(
                "UPDATE swab_param SET swab_price = ?, is_active = ?, is_deleted = 0, updated_at = NOW()
                 WHERE swab_param_id = ?"
            );
            $stmt2->bind_param("dii", $price, $isActive, $row['swab_param_id']);
            return $stmt2->execute();
        }

        return $this->insertSwab($paramId, $price, $isActive);
    }

    /**
     * Soft delete by swab_param_id
     */
    public function softDeleteById($swabParamId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param SET is_deleted = 1, updated_at = NOW() WHERE swab_param_id = ?"
        );
        $stmt->bind_param("i", $swabParamId);
        return $stmt->execute();
    }

    // =================== SWAB COMBO METHODS ===================

    /**
     * Generate combo display name from parameter IDs
     * Returns: "Aerobic Plate Count + Coliforms + E.coli"
     */
    public function generateComboName($parameterIds)
    {
        if (empty($parameterIds) || !is_array($parameterIds)) {
            return '';
        }

        // Sanitize all IDs to integers
        $parameterIds = array_map('intval', $parameterIds);
        $placeholders = implode(',', array_fill(0, count($parameterIds), '?'));
        $types = str_repeat('i', count($parameterIds));

        // Preserve the order the user selected them
        $fieldOrder = implode(',', $parameterIds);

        $stmt = $this->conn->prepare(
            "SELECT parameter_name 
             FROM test_parameters 
             WHERE parameter_id IN ($placeholders) 
             AND is_deleted = 0
             ORDER BY FIELD(parameter_id, $fieldOrder)"
        );

        $stmt->bind_param($types, ...$parameterIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['parameter_name'];
        }

        return implode(' + ', $names);
    }

    /**
     * Fetch all swab combos (non-deleted) with filters
     */
    public function getAllSwabCombos($filters = [])
    {
        $sql = "SELECT 
                    sc.combo_id,
                    sc.combo_name,
                    sc.price,
                    sc.is_active,
                    sc.created_at,
                    sc.updated_at,
                    GROUP_CONCAT(tp.parameter_name ORDER BY sci.id ASC SEPARATOR ' + ') AS combo_params,
                    GROUP_CONCAT(tp.parameter_id ORDER BY sci.id ASC) AS param_ids
                FROM swab_combos sc
                INNER JOIN swab_combo_items sci ON sc.combo_id = sci.combo_id
                INNER JOIN test_parameters tp ON sci.param_id = tp.parameter_id
                WHERE sc.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND sc.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        $sql .= " GROUP BY sc.combo_id";

        if (isset($filters['search']) && trim($filters['search']) !== '') {
            $sql .= " HAVING combo_params LIKE ?";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $types .= "s";
        }

        $sql .= " ORDER BY sc.combo_id DESC";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("SwabModel::getAllSwabCombos prepare error: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'combo_id'     => intval($r['combo_id']),
                'name'         => $r['combo_params'] ?: $r['combo_name'],
                'price'        => number_format((float)$r['price'], 2, '.', ''),
                'is_active'    => intval($r['is_active']),
                'param_ids'    => $r['param_ids'],
                'type'         => 'combo'
            ];
        }

        return $rows;
    }

    /**
     * Get a single swab combo by ID with its parameter list
     */
    public function getSwabComboById($comboId)
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                sc.combo_id, sc.combo_name, sc.price, sc.is_active
             FROM swab_combos sc
             WHERE sc.combo_id = ? AND sc.is_deleted = 0
             LIMIT 1"
        );
        $stmt->bind_param("i", $comboId);
        $stmt->execute();
        $res = $stmt->get_result();
        $combo = $res->fetch_assoc();

        if (!$combo) {
            return null;
        }

        // Fetch associated parameter IDs
        $stmt2 = $this->conn->prepare(
            "SELECT sci.param_id, tp.parameter_name
             FROM swab_combo_items sci
             INNER JOIN test_parameters tp ON sci.param_id = tp.parameter_id
             WHERE sci.combo_id = ?
             ORDER BY sci.id ASC"
        );
        $stmt2->bind_param("i", $comboId);
        $stmt2->execute();
        $res2 = $stmt2->get_result();

        $paramIds = [];
        $paramNames = [];
        while ($row = $res2->fetch_assoc()) {
            $paramIds[] = intval($row['param_id']);
            $paramNames[] = $row['parameter_name'];
        }

        $combo['param_ids'] = $paramIds;
        $combo['combo_name'] = implode(' + ', $paramNames);

        return $combo;
    }

    /**
     * Check if an exact combo already exists (same set of parameters)
     */
    public function hasExactCombo($parameterIds, $excludeComboId = null)
    {
        if (empty($parameterIds) || !is_array($parameterIds)) {
            return false;
        }

        $parameterIds = array_map('intval', $parameterIds);
        sort($parameterIds);
        $paramCount = count($parameterIds);

        // Find all non-deleted combos that have exactly this many items
        $sql = "SELECT sc.combo_id
                FROM swab_combos sc
                WHERE sc.is_deleted = 0
                AND (SELECT COUNT(*) FROM swab_combo_items WHERE combo_id = sc.combo_id) = ?";

        $params = [$paramCount];
        $types = "i";

        if ($excludeComboId) {
            $sql .= " AND sc.combo_id != ?";
            $params[] = intval($excludeComboId);
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("SwabModel::hasExactCombo prepare error: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $candidateId = $row['combo_id'];
            // Fetch this candidate's parameter IDs
            $stmt2 = $this->conn->prepare(
                "SELECT param_id FROM swab_combo_items WHERE combo_id = ? ORDER BY param_id ASC"
            );
            $stmt2->bind_param("i", $candidateId);
            $stmt2->execute();
            $res2 = $stmt2->get_result();

            $candidateParams = [];
            while ($r2 = $res2->fetch_assoc()) {
                $candidateParams[] = intval($r2['param_id']);
            }

            if ($candidateParams === $parameterIds) {
                return true; // Exact match found
            }
        }

        return false;
    }

    /**
     * Insert a new swab combo with its items (transactional)
     */
    public function insertSwabCombo($parameterIds, $price, $isActive = 1)
    {
        $this->conn->begin_transaction();

        try {
            // Generate combo name first
            $comboName = $this->generateComboName($parameterIds);

            // Insert into swab_combos
            $stmt = $this->conn->prepare(
                "INSERT INTO swab_combos (combo_name, price, is_active, is_deleted, created_at)
                 VALUES (?, ?, ?, 0, NOW())"
            );
            $stmt->bind_param("sdi", $comboName, $price, $isActive);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert swab combo: " . $stmt->error);
            }

            $comboId = $this->conn->insert_id;

            // Insert combo items (swab_combo_items has: id, combo_id, param_id)
            $stmtItem = $this->conn->prepare(
                "INSERT INTO swab_combo_items (combo_id, param_id) VALUES (?, ?)"
            );

            foreach ($parameterIds as $paramId) {
                $paramId = intval($paramId);
                $stmtItem->bind_param("ii", $comboId, $paramId);
                if (!$stmtItem->execute()) {
                    throw new Exception("Failed to insert combo item for param $paramId: " . $stmtItem->error);
                }
            }

            $this->conn->commit();
            return $comboId;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("SwabModel::insertSwabCombo error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing swab combo and its items (transactional)
     */
    public function updateSwabCombo($comboId, $parameterIds, $price, $isActive)
    {
        $this->conn->begin_transaction();

        try {
            // Generate updated combo name
            $comboName = $this->generateComboName($parameterIds);

            // Update the combo record
            $stmt = $this->conn->prepare(
                "UPDATE swab_combos 
                 SET combo_name = ?, price = ?, is_active = ?, updated_at = NOW()
                 WHERE combo_id = ? AND is_deleted = 0"
            );
            $stmt->bind_param("sdii", $comboName, $price, $isActive, $comboId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update swab combo: " . $stmt->error);
            }

            // Delete old combo items
            $stmtDel = $this->conn->prepare(
                "DELETE FROM swab_combo_items WHERE combo_id = ?"
            );
            $stmtDel->bind_param("i", $comboId);
            if (!$stmtDel->execute()) {
                throw new Exception("Failed to clear old combo items: " . $stmtDel->error);
            }

            // Re-insert new combo items (swab_combo_items has: id, combo_id, param_id)
            $stmtItem = $this->conn->prepare(
                "INSERT INTO swab_combo_items (combo_id, param_id) VALUES (?, ?)"
            );

            foreach ($parameterIds as $paramId) {
                $paramId = intval($paramId);
                $stmtItem->bind_param("ii", $comboId, $paramId);
                if (!$stmtItem->execute()) {
                    throw new Exception("Failed to insert combo item for param $paramId: " . $stmtItem->error);
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("SwabModel::updateSwabCombo error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete a swab combo by ID
     */
    public function softDeleteComboById($comboId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_combos SET is_deleted = 1, updated_at = NOW() WHERE combo_id = ?"
        );
        $stmt->bind_param("i", $comboId);
        return $stmt->execute();
    }
}
