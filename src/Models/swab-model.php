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
     * Get parameters dropdown (only swab-enabled parameters WITHOUT existing swab_param records)
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
}
?>