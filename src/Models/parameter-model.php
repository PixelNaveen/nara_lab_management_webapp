<?php
require_once __DIR__ . '/../../Config/Database.php';

class ParameterModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    }

    // =================== GET ALL PARAMETERS WITH PAGINATION ===================
    public function getAllParameters($filters = [])
    {
        $sql = "SELECT 
                    tp.parameter_id,
                    tp.parameter_code,
                    tp.parameter_name,
                    tp.parameter_category,
                    tp.base_unit,
                    tp.has_variants,
                    tp.swab_enabled,
                    tp.is_active,
                    tp.created_at,
                    COUNT(DISTINCT pv.variant_id) as variant_count
                FROM test_parameters tp
                LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id 
                    AND pv.is_active = 1 AND pv.is_deleted = 0
                WHERE tp.is_deleted = 0";
        
        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND tp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (tp.parameter_name LIKE ? OR tp.parameter_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        $sql .= " GROUP BY tp.parameter_id ORDER BY tp.parameter_id ASC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
        if (!empty($params)) {
            $stmtCount = $this->conn->prepare($countSql);
            $stmtCount->bind_param($types, ...$params);
            $stmtCount->execute();
            $countResult = $stmtCount->get_result();
            $total = $countResult->fetch_assoc()['total'];
        } else {
            $countResult = $this->conn->query($countSql);
            $total = $countResult->fetch_assoc()['total'];
        }

        // Add pagination
        $page = isset($filters['page']) ? intval($filters['page']) : 1;
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        $offset = ($page - 1) * $limit;
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $parameters = [];
        while ($row = $result->fetch_assoc()) {
            $parameters[] = $row;
        }

        return [
            'data' => $parameters,
            'total' => $total
        ];
    }

    // =================== GET PARAMETER BY ID ===================
    public function getParameterById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT parameter_id, parameter_code, parameter_name, 
                    parameter_category, base_unit, has_variants, 
                    swab_enabled, is_active 
            FROM test_parameters 
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($name, $excludeId = null)
    {
        $sql = "SELECT parameter_id FROM test_parameters 
                WHERE parameter_name = ? AND is_deleted = 0";
        
        $params = [$name];
        $types = "s";
        
        if ($excludeId) {
            $sql .= " AND parameter_id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== FIND DELETED RECORD ===================
    public function findDeletedByName($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT parameter_id, parameter_code 
            FROM test_parameters 
            WHERE parameter_name = ? AND is_deleted = 1 
            LIMIT 1"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== GET NEXT CODE ===================
    public function getNextParameterCode()
    {
        $result = $this->conn->query(
            "SELECT parameter_code FROM test_parameters 
            ORDER BY parameter_id DESC LIMIT 1"
        );
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = strtoupper($row['parameter_code']);
            
            if (strlen($lastCode) === 1 && ctype_alpha($lastCode)) {
                $nextCode = chr(ord($lastCode) + 1);
                return $nextCode > 'Z' ? 'AA' : $nextCode;
            }
            
            if (ctype_alpha($lastCode)) {
                return ++$lastCode;
            }
        }
        
        return 'A';
    }

    // =================== INSERT PARAMETER ===================
    public function insertParameter($name, $category, $baseUnit, $swabEnabled, $isActive = 1)
    {
        $code = $this->getNextParameterCode();
        
        $stmt = $this->conn->prepare(
            "INSERT INTO test_parameters 
            (parameter_code, parameter_name, parameter_category, base_unit, 
             swab_enabled, has_variants, is_active, is_deleted, created_at)
            VALUES (?, ?, ?, ?, ?, 0, ?, 0, NOW())"
        );
        
        $stmt->bind_param("ssssii", $code, $name, $category, $baseUnit, $swabEnabled, $isActive);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // =================== REACTIVATE PARAMETER ===================
    public function reactivateParameter($id, $category, $baseUnit, $swabEnabled, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET parameter_category = ?, 
                base_unit = ?, 
                swab_enabled = ?,
                is_active = ?,
                is_deleted = 0,
                updated_at = NOW()
            WHERE parameter_id = ?"
        );
        
        $stmt->bind_param("ssiii", $category, $baseUnit, $swabEnabled, $isActive, $id);
        return $stmt->execute();
    }

    // =================== UPDATE PARAMETER ===================
    public function updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET parameter_code = ?,
                parameter_name = ?, 
                parameter_category = ?,
                base_unit = ?, 
                swab_enabled = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        
        $stmt->bind_param("ssssiii", $code, $name, $category, $baseUnit, $swabEnabled, $isActive, $id);
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteParameter($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET is_deleted = 1, updated_at = NOW() 
            WHERE parameter_id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =================== TOGGLE STATUS ===================
    public function toggleStatus($id, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET is_active = ?, updated_at = NOW() 
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("ii", $isActive, $id);
        return $stmt->execute();
    }

    // =================== CHECK VARIANTS ===================
    public function hasActiveVariants($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM parameter_variants 
            WHERE parameter_id = ? AND is_active = 1 AND is_deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    // =================== SWAB PRICE MANAGEMENT ===================
    // Only called from parameter operations when swab is initially enabled
    
    public function createInitialSwabPrice($paramId, $price = 0.00)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO swab_param (param_id, swab_price, is_active, is_deleted, created_at)
            VALUES (?, ?, 1, 0, NOW())"
        );
        $stmt->bind_param("id", $paramId, $price);
        return $stmt->execute();
    }

    public function reactivateSwabPrice($paramId, $price = 0.00)
    {
        $stmt = $this->conn->prepare(
            "SELECT swab_param_id FROM swab_param 
            WHERE param_id = ? AND is_deleted = 1 
            LIMIT 1"
        );
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt2 = $this->conn->prepare(
                "UPDATE swab_param 
                SET swab_price = ?, is_active = 1, is_deleted = 0, updated_at = NOW()
                WHERE swab_param_id = ?"
            );
            $stmt2->bind_param("di", $price, $row['swab_param_id']);
            return $stmt2->execute();
        }
        
        return $this->createInitialSwabPrice($paramId, $price);
    }

    public function disableSwabParam($paramId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param 
            SET is_deleted = 1, updated_at = NOW()
            WHERE param_id = ?"
        );
        $stmt->bind_param("i", $paramId);
        return $stmt->execute();
    }

    public function syncSwabParamStatus($paramId, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param 
            SET is_active = ?, updated_at = NOW()
            WHERE param_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("ii", $isActive, $paramId);
        return $stmt->execute();
    }
}
?>