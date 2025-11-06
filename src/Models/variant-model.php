<?php
require_once __DIR__ . '/../../Config/Database.php';

class VariantModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect(); // expects mysqli connection like your other model
    }

    /**
     * Fetch all variants (not deleted). Optionally filter by parameter_id or is_active.
     * Returns array.
     */
    public function getAllVariants($filters = []) {
        $sql = "SELECT 
                    v.variant_id,
                    v.parameter_id,
                    v.variant_name,
                    v.full_display_name,
                    v.is_active,
                    v.created_at,
                    v.updated_at,
                    p.parameter_name,
                    CONCAT(
                        p.parameter_name,
                        IF(p.base_unit != '', CONCAT(' ', p.base_unit), ''),
                        IF(v.variant_name != '', CONCAT(' ', v.variant_name), '')
                    ) as full_variant_name
                FROM parameter_variants v
                JOIN test_parameters p ON v.parameter_id = p.parameter_id
                WHERE v.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['parameter_id']) && $filters['parameter_id'] !== '') {
            $sql .= " AND v.parameter_id = ?";
            $params[] = intval($filters['parameter_id']);
            $types .= "i";
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND v.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        $sql .= " ORDER BY v.variant_id DESC";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch single variant by id (not deleted)
     */
    public function getVariantById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM parameter_variants WHERE variant_id = ? AND is_deleted = 0");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Check if variant exists for the same parameter (includes deleted records for possible restore)
     * returns assoc row or null
     */
    public function findByNameAndParameter($name, $parameter_id) {
        $stmt = $this->conn->prepare("SELECT variant_id, is_deleted FROM parameter_variants 
                                      WHERE variant_name = ? AND parameter_id = ? LIMIT 1");
        $stmt->bind_param("si", $name, $parameter_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Insert variant.
     * Behavior:
     *  - If an exact variant exists and is_deleted = 1 -> reactivate (set is_deleted=0, update fields).
     *  - If exists and is_deleted = 0 -> return 'duplicate'
     *  - Else insert new record and return true/false
     */
    public function insertVariant($parameter_id, $variant_name, $full_display_name, $is_active = 1) {
        $existing = $this->findByNameAndParameter($variant_name, $parameter_id);
        if ($existing) {
            if ($existing['is_deleted'] == 1) {
                // Reactivate
                $stmt = $this->conn->prepare("UPDATE parameter_variants 
                                              SET is_deleted = 0, is_active = ?, full_display_name = ?, updated_at = NOW()
                                              WHERE variant_id = ?");
                $stmt->bind_param("isi", $is_active, $full_display_name, $existing['variant_id']);
                return $stmt->execute();
            } else {
                return 'duplicate';
            }
        }

        $stmt = $this->conn->prepare("INSERT INTO parameter_variants
            (parameter_id, variant_name, full_display_name, is_active, is_deleted, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("issi", $parameter_id, $variant_name, $full_display_name, $is_active);
        return $stmt->execute();
    }

    /**
     * Update variant (only if not deleted)
     */
    public function updateVariant($variant_id, $parameter_id, $variant_name, $full_display_name, $is_active) {
        // check duplicate name on same parameter (exclude self)
        // check duplicate
        $stmt2 = $this->conn->prepare("SELECT variant_id FROM parameter_variants 
                                       WHERE variant_name = ? AND parameter_id = ? AND variant_id != ? AND is_deleted = 0 LIMIT 1");
        $stmt2->bind_param("sii", $variant_name, $parameter_id, $variant_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($res2->num_rows > 0) {
            return 'duplicate';
        }

        $stmt3 = $this->conn->prepare("UPDATE parameter_variants 
                                       SET parameter_id = ?, variant_name = ?, full_display_name = ?, is_active = ?, updated_at = NOW()
                                       WHERE variant_id = ? AND is_deleted = 0");
        $stmt3->bind_param("issii", $parameter_id, $variant_name, $full_display_name, $is_active, $variant_id);
        return $stmt3->execute();
    }

    /**
     * Soft delete variant -> mark is_deleted = 1
     */
    public function softDeleteVariant($variant_id) {
        $stmt = $this->conn->prepare("UPDATE parameter_variants SET is_deleted = 1, updated_at = NOW() WHERE variant_id = ?");
        $stmt->bind_param("i", $variant_id);
        return $stmt->execute();
    }

    /**
     * Fetch all active parameters to populate combobox (for form)
     */
    public function getActiveParameters() {
        $sql = "SELECT parameter_id, parameter_name FROM test_parameters WHERE is_active = 1 ORDER BY parameter_id ASC";
        $result = $this->conn->query($sql);
        $rows = [];
        while ($r = $result->fetch_assoc()) $rows[] = $r;
        return $rows;
    }
}
?>