<?php
require_once __DIR__ . '/../../Config/Database.php';

class ParameterModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // =================== GET ALL PARAMETERS WITH VARIANT COUNT ===================
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
                    COUNT(pv.variant_id) as variant_count
                FROM test_parameters tp
                LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id AND pv.is_active = 1
                WHERE 1=1";
        
        $params = [];
        $types = "";

        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND tp.is_active = ?";
            $params[] = $filters['is_active'];
            $types .= "i";
        }

        $sql .= " GROUP BY tp.parameter_id ORDER BY tp.parameter_id DESC";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $parameters = [];
        while ($row = $result->fetch_assoc()) {
            $parameters[] = $row;
        }
        return $parameters;
    }

    // =================== GET PARAMETER BY ID ===================
    public function getParameterById($id)
    {
        $stmt = $this->conn->prepare("SELECT parameter_id, parameter_code, parameter_name, 
                                      parameter_category, base_unit, has_variants, 
                                      swab_enabled, is_active 
                                      FROM test_parameters WHERE parameter_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== DUPLICATE CHECK (NAME ONLY) ===================
    public function isDuplicate($name, $excludeId = null)
    {
        $sql = "SELECT parameter_id FROM test_parameters WHERE parameter_name = ?";
        
        if ($excludeId) {
            $sql .= " AND parameter_id != ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $name, $excludeId);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $name);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== GET NEXT AVAILABLE CODE ===================
    public function getNextParameterCode()
    {
        // Get the last used code
        $result = $this->conn->query("SELECT parameter_code FROM test_parameters 
                                      ORDER BY parameter_id DESC LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = $row['parameter_code'];
            
            // If it's a single letter, increment it
            if (strlen($lastCode) === 1 && ctype_alpha($lastCode)) {
                $nextCode = chr(ord(strtoupper($lastCode)) + 1);
                // If we've gone past Z, start with AA
                if ($nextCode > 'Z') {
                    return 'AA';
                }
                return $nextCode;
            }
            
            // If it's multi-letter (like AA, AB), increment accordingly
            if (ctype_alpha($lastCode)) {
                $lastCode = strtoupper($lastCode);
                $nextCode = ++$lastCode;
                return $nextCode;
            }
        }
        
        // If no codes exist, start with A
        return 'A';
    }

    // =================== INSERT PARAMETER ===================
    public function insertParameter($name, $category, $baseUnit, $swabEnabled)
    {
        // Auto-generate the next code
        $code = $this->getNextParameterCode();
        
        $stmt = $this->conn->prepare("INSERT INTO test_parameters 
                                      (parameter_code, parameter_name, parameter_category, 
                                       base_unit, swab_enabled, has_variants, is_active, created_at)
                                      VALUES (?, ?, ?, ?, ?, 0, 1, NOW())");
        $stmt->bind_param("ssssi", $code, $name, $category, $baseUnit, $swabEnabled);
        return $stmt->execute();
    }

    // =================== UPDATE PARAMETER ===================
    public function updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)
    {
        $stmt = $this->conn->prepare("UPDATE test_parameters 
                                      SET parameter_code = ?, 
                                          parameter_name = ?, 
                                          parameter_category = ?,
                                          base_unit = ?, 
                                          swab_enabled = ?,
                                          is_active = ?,
                                          updated_at = NOW()
                                      WHERE parameter_id = ?");
        $stmt->bind_param("ssssiis", $code, $name, $category, $baseUnit, $swabEnabled, $isActive, $id);
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteParameter($id)
    {
        $stmt = $this->conn->prepare("UPDATE test_parameters SET is_active = 0, updated_at = NOW() 
                                      WHERE parameter_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =================== CHECK IF PARAMETER HAS VARIANTS ===================
    public function hasActiveVariants($id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM parameter_variants 
                                      WHERE parameter_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
}
?>