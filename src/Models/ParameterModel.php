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
                    tp.short_name,
                    tp.display_format,
                    tp.result_mode,
                    tp.espc_applicable,
                    tp.swab_enabled,
                    tp.is_active,
                    tp.created_at,
                    MAX(pbuc.is_slab_accredited) as is_slab_accredited
                FROM test_parameters tp
                LEFT JOIN parameter_base_unit_config pbuc ON tp.parameter_id = pbuc.parameter_id
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
                    short_name, display_format,
                    result_mode, espc_applicable,
                    swab_enabled, is_active 
            FROM test_parameters 
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get array of method_ids for a parameter (from both old and new tables)
    public function getParameterMethodIds($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT method_id FROM (
                -- Old table: parameter_methods
                SELECT pm.method_id, pm.sequence_order 
                FROM parameter_methods pm WHERE pm.parameter_id = ?
                UNION
                -- New table: parameter_category_methods
                SELECT pcm.method_id, pcm.sequence_order
                FROM parameter_base_unit_config pbc
                INNER JOIN parameter_category_methods pcm ON pbc.config_id = pcm.config_id
                WHERE pbc.parameter_id = ? AND pbc.is_active = 1
            ) AS combined
            ORDER BY sequence_order, method_id"
        );
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $methodIds = [];
        while ($row = $result->fetch_assoc()) {
            $methodIds[] = $row['method_id'];
        }
        return $methodIds;
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
    public function insertParameter($name, $category, $swabEnabled, $isActive = 1, $extraFields = [])
    {
        $code = $this->getNextParameterCode();
        $shortName = $extraFields['short_name'] ?? null;
        $displayFormat = $extraFields['display_format'] ?? 'normal';
        $resultMode = $extraFields['result_mode'] ?? 'numeric_or_ND';
        $espcApplicable = isset($extraFields['espc_applicable']) ? (int)$extraFields['espc_applicable'] : 0;

        $stmt = $this->conn->prepare(
            "INSERT INTO test_parameters 
            (parameter_code, parameter_name, parameter_category,
             swab_enabled, has_variants,
             short_name, display_format,
             result_mode, espc_applicable,
             is_active, is_deleted, created_at)
            VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, ?, 0, NOW())"
        );

        $stmt->bind_param(
            "sssisssii",
            $code,
            $name,
            $category,
            $swabEnabled,
            $shortName,
            $displayFormat,
            $resultMode,
            $espcApplicable,
            $isActive
        );

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
    public function updateParameter($id, $code, $name, $category, $swabEnabled, $isActive, $extraFields = [])
    {
        $shortName = $extraFields['short_name'] ?? null;
        $displayFormat = $extraFields['display_format'] ?? 'normal';
        $resultMode = $extraFields['result_mode'] ?? 'numeric_or_ND';
        $espcApplicable = isset($extraFields['espc_applicable']) ? (int)$extraFields['espc_applicable'] : 0;

        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET parameter_code = ?,
                parameter_name = ?, 
                parameter_category = ?,
                swab_enabled = ?,
                short_name = ?,
                display_format = ?,
                result_mode = ?,
                espc_applicable = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE parameter_id = ? AND is_deleted = 0"
        );

        $stmt->bind_param(
            "sssisssiii",
            $code,
            $name,
            $category,
            $swabEnabled,
            $shortName,
            $displayFormat,
            $resultMode,
            $espcApplicable,
            $isActive,
            $id
        );
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

    // Assign methods to parameter with sequence ordering
    public function assignMethodsToParameter($paramId, $methodIds)
    {
        if (empty($methodIds) || !is_array($methodIds)) {
            return true;
        }

        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO parameter_methods (parameter_id, method_id, is_default, sequence_order, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );

        $sequence = 0;
        foreach ($methodIds as $methodId) {
            if (!is_numeric($methodId) || $methodId <= 0) continue;

            $isDefault = ($sequence === 0) ? 1 : 0; // First method is default
            $stmt->bind_param("iiii", $paramId, $methodId, $isDefault, $sequence);
            $stmt->execute();
            $sequence++;
        }

        return true;
    }

    // Sync methods (delete all existing, then assign new)
    public function syncParameterMethods($paramId, $methodIds)
    {
        // Delete existing associations
        $stmt = $this->conn->prepare("DELETE FROM parameter_methods WHERE parameter_id = ?");
        $stmt->bind_param("i", $paramId);
        $stmt->execute();

        // Assign new ones
        return $this->assignMethodsToParameter($paramId, $methodIds);
    }

    // =================== SWAB PRICE MANAGEMENT ===================

    /**
     * ⚠️ DEPRECATED: No longer used after architecture fix
     * Kept for backward compatibility only
     * Swab params are now created manually in swab-param page
     */
    public function createInitialSwabPrice($paramId, $price = 0.00)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO swab_param (param_id, swab_price, is_active, is_deleted, created_at)
            VALUES (?, ?, 1, 0, NOW())"
        );
        $stmt->bind_param("id", $paramId, $price);
        return $stmt->execute();
    }

    /**
     * ⚠️ DEPRECATED: No longer used after architecture fix
     * Kept for backward compatibility only
     * Swab params are now created manually in swab-param page
     */
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

    /**
     * Soft delete swab_param record when swab is disabled
     * Called when parameter.swab_enabled changes from 1 to 0
     */
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

    /**
     * ⚠️ DEPRECATED: Use syncSwabParamStatusIfExists() instead
     * This method will fail if swab_param doesn't exist yet
     */
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

    /**
     * ✅ NEW: Safe sync method that doesn't fail if swab_param doesn't exist
     * Synchronizes parameter status with swab_param status
     * Silent success if swab_param record doesn't exist (not created yet)
     * 
     * @param int $paramId - The parameter ID
     * @param int $isActive - The active status to sync (0 or 1)
     * @return bool - Always returns true (silent success if record doesn't exist)
     */
    public function syncSwabParamStatusIfExists($paramId, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param 
            SET is_active = ?, updated_at = NOW()
            WHERE param_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("ii", $isActive, $paramId);
        $stmt->execute();

        // Always return true - if record doesn't exist, that's OK
        // The update will affect 0 rows but won't throw an error
        return true;
    }

    public function getActiveMethods()
    {
        $sql = "SELECT method_id, method_name 
                FROM test_methods
                WHERE is_active = 1 AND is_deleted = 0
                ORDER BY method_id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = $row;
        }
        return $methods;
    }

    public function getMethodIdByName($methodName)
    {
        $stmt = $this->conn->prepare(
            "SELECT method_id FROM test_methods 
             WHERE method_name = ? AND is_deleted = 0 LIMIT 1"
        );
        $stmt->bind_param("s", $methodName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? intval($row['method_id']) : false;
    }

    // =================== GET METHODS BY PARAMETER ===================
    public function getMethodsByParameter($paramId)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT tm.method_id, tm.method_name
             FROM (
                -- Old table: parameter_methods
                SELECT pm.method_id, pm.sequence_order
                FROM parameter_methods pm WHERE pm.parameter_id = ?
                UNION
                -- New table: parameter_category_methods
                SELECT pcm.method_id, pcm.sequence_order
                FROM parameter_base_unit_config pbc
                INNER JOIN parameter_category_methods pcm ON pbc.config_id = pcm.config_id
                WHERE pbc.parameter_id = ? AND pbc.is_active = 1
             ) AS combined
             INNER JOIN test_methods tm ON combined.method_id = tm.method_id
             WHERE tm.is_deleted = 0 AND tm.is_active = 1
             ORDER BY combined.sequence_order ASC, tm.method_name ASC"
        );

        $stmt->bind_param("ii", $paramId, $paramId);
        $stmt->execute();
        $result = $stmt->get_result();

        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = $row;
        }

        return $methods; // Returns array of ['method_id'=>..., 'method_name'=>...]
    }

    public function getParametersWithMethods()
    {
        $sql = "SELECT 
                    tp.parameter_name,
                    GROUP_CONCAT(DISTINCT tm.method_name ORDER BY tm.method_name SEPARATOR ', ') AS method_names
                FROM test_parameters AS tp
                LEFT JOIN parameter_base_unit_config AS pbuc ON tp.parameter_id = pbuc.parameter_id
                LEFT JOIN parameter_category_methods AS pcm ON pbuc.config_id = pcm.config_id
                LEFT JOIN test_methods AS tm ON pcm.method_id = tm.method_id
                WHERE tp.is_active = 1 AND tp.is_deleted = 0
                GROUP BY tp.parameter_id
                ORDER BY tp.parameter_name ASC";

        $result = $this->conn->query($sql);

        $tableData = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tableData[] = $row;
            }
        }

        return $tableData;
    }

    // =================== CATEGORY CONFIG METHODS ===================

    /**
     * Get all category configurations for a parameter
     * Returns category info + unit info + config details
     */
    public function getCategoryConfigs($paramId)
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                pbu.config_id,
                pbu.parameter_id,
                pbu.base_category_id,
                pbu.base_unit_id,
                pbu.is_slab_accredited,
                pbu.certificate_id,
                pbu.temperature_options,
                pbu.is_active,
                buc.category_name,
                buc.category_code,
                bu.unit_name
            FROM parameter_base_unit_config pbu
            INNER JOIN base_unit_categories buc ON pbu.base_category_id = buc.base_category_id
            INNER JOIN base_units bu ON pbu.base_unit_id = bu.base_unit_id
            WHERE pbu.parameter_id = ?
            ORDER BY buc.display_order ASC"
        );
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $result = $stmt->get_result();

        $configs = [];
        while ($row = $result->fetch_assoc()) {
            $configs[] = $row;
        }
        return $configs;
    }

    /**
     * Save or update a single category config for a parameter
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert
     */
    public function saveCategoryConfig($paramId, $categoryId, $unitId, $isSlabAccredited, $certificateId)
    {
        // If not accredited, clear certificate_id
        if (!$isSlabAccredited) {
            $certificateId = null;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO parameter_base_unit_config 
            (parameter_id, base_category_id, base_unit_id, is_slab_accredited, 
             certificate_id, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
                base_unit_id = VALUES(base_unit_id),
                is_slab_accredited = VALUES(is_slab_accredited),
                certificate_id = VALUES(certificate_id),
                is_active = 1,
                updated_at = NOW()"
        );
        $stmt->bind_param(
            "iiiii",
            $paramId,
            $categoryId,
            $unitId,
            $isSlabAccredited,
            $certificateId
        );

        if ($stmt->execute()) {
            // Return the config_id (either newly inserted or existing)
            if ($this->conn->insert_id > 0) {
                return $this->conn->insert_id;
            }
            // For update cases, get the existing config_id
            $getStmt = $this->conn->prepare(
                "SELECT config_id FROM parameter_base_unit_config 
                 WHERE parameter_id = ? AND base_category_id = ?"
            );
            $getStmt->bind_param("ii", $paramId, $categoryId);
            $getStmt->execute();
            $getResult = $getStmt->get_result();
            $row = $getResult->fetch_assoc();
            return $row ? $row['config_id'] : false;
        }
        return false;
    }

    /**
     * Get active accreditation certificates for dropdown
     */
    public function getActiveCertificates()
    {
        $sql = "SELECT certificate_id, certificate_code, certificate_name
                FROM accreditation_certificates
                WHERE is_deleted = 0 AND status = 'active'
                ORDER BY is_current DESC, certificate_id ASC";
        $result = $this->conn->query($sql);
        $certs = [];
        while ($row = $result->fetch_assoc()) {
            $certs[] = $row;
        }
        return $certs;
    }

    /**
     * Delete all category configs for a parameter
     * Also cascades to parameter_category_methods via FK
     */
    public function deleteCategoryConfigs($paramId)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM parameter_base_unit_config WHERE parameter_id = ?"
        );
        $stmt->bind_param("i", $paramId);
        return $stmt->execute();
    }

    /**
     * Delete a specific category config for a parameter
     */
    public function deleteSingleCategoryConfig($paramId, $categoryId)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM parameter_base_unit_config 
             WHERE parameter_id = ? AND base_category_id = ?"
        );
        $stmt->bind_param("ii", $paramId, $categoryId);
        return $stmt->execute();
    }

    /**
     * Get methods assigned to a specific category config
     */
    public function getCategoryMethods($configId)
    {
        $stmt = $this->conn->prepare(
            "SELECT pcm.pcm_id, pcm.method_id, pcm.sequence_order, pcm.is_primary,
                    tm.method_name
             FROM parameter_category_methods pcm
             INNER JOIN test_methods tm ON pcm.method_id = tm.method_id
             WHERE pcm.config_id = ?
             ORDER BY pcm.sequence_order ASC"
        );
        $stmt->bind_param("i", $configId);
        $stmt->execute();
        $result = $stmt->get_result();

        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = $row;
        }
        return $methods;
    }

    /**
     * Save methods for a category config (delete + re-insert)
     */
    public function saveCategoryMethods($configId, $methodIds)
    {
        // Delete existing
        $delStmt = $this->conn->prepare(
            "DELETE FROM parameter_category_methods WHERE config_id = ?"
        );
        $delStmt->bind_param("i", $configId);
        $delStmt->execute();

        if (empty($methodIds) || !is_array($methodIds)) {
            return true;
        }

        $insStmt = $this->conn->prepare(
            "INSERT INTO parameter_category_methods 
             (config_id, method_id, sequence_order, is_primary)
             VALUES (?, ?, ?, ?)"
        );

        $seq = 0;
        foreach ($methodIds as $methodId) {
            if (!is_numeric($methodId) || $methodId <= 0) continue;
            $isPrimary = ($seq === 0) ? 1 : 0;
            $insStmt->bind_param("iiii", $configId, $methodId, $seq, $isPrimary);
            $insStmt->execute();
            $seq++;
        }
        return true;
    }

    /**
     * Get full parameter data including all category configs and their methods
     * Used by the edit form to load everything at once
     */
    public function getFullParameterData($paramId)
    {
        $param = $this->getParameterById($paramId);
        if (!$param) return null;

        // Get universal method IDs
        $param['method_ids'] = $this->getParameterMethodIds($paramId);

        // Get category configs with their methods
        $configs = $this->getCategoryConfigs($paramId);
        foreach ($configs as &$config) {
            $config['methods'] = $this->getCategoryMethods($config['config_id']);
            $config['method_ids'] = array_column($config['methods'], 'method_id');
        }
        $param['category_configs'] = $configs;

        return $param;
    }
}
