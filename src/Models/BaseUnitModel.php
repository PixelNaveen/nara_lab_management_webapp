<?php
/**
 * BaseUnitModel.php
 * 
 * Purpose: Manage base unit categories and units
 * Used for: Parameter configuration (selecting units per category)
 * 
 * Phase: 2 - Base Unit System
 */

require_once __DIR__ . '/../../Config/Database.php';

class BaseUnitModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    }

    // =================== GET ALL CATEGORIES ===================
    /**
     * Get all base unit categories (Water, Food, Swab)
     * Used in: Parameter form to show category configuration panels
     */
    public function getAllCategories()
    {
        $sql = "SELECT 
                    base_category_id,
                    category_name,
                    category_code,
                    description,
                    is_active,
                    display_order,
                    (SELECT COUNT(*) FROM base_units 
                     WHERE base_category_id = buc.base_category_id) AS unit_count
                FROM base_unit_categories buc
                WHERE is_active = 1
                ORDER BY display_order ASC";
        
        $result = $this->conn->query($sql);
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    // =================== GET CATEGORY BY ID ===================
    public function getCategoryById($categoryId)
    {
        $stmt = $this->conn->prepare(
            "SELECT base_category_id, category_name, category_code, description
             FROM base_unit_categories
             WHERE base_category_id = ? AND is_active = 1"
        );
        
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // =================== GET CATEGORY BY CODE ===================
    public function getCategoryByCode($code)
    {
        $stmt = $this->conn->prepare(
            "SELECT base_category_id, category_name, category_code, description
             FROM base_unit_categories
             WHERE category_code = ? AND is_active = 1"
        );
        
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // =================== GET UNITS FOR CATEGORY ===================
    /**
     * Get all units available for a specific category
     * Used in: Parameter form dropdowns
     * 
     * @param int $categoryId - The category ID
     * @param bool $commonOnly - If true, only return is_common=1 units
     * @return array - Array of units
     */
    public function getUnitsForCategory($categoryId, $commonOnly = false)
    {
        $sql = "SELECT 
                    base_unit_id,
                    unit_name,
                    base_category_id,
                    unit_type,
                    is_common,
                    display_order
                FROM base_units
                WHERE base_category_id = ?";
        
        if ($commonOnly) {
            $sql .= " AND is_common = 1";
        }
        
        $sql .= " ORDER BY display_order ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $units = [];
        while ($row = $result->fetch_assoc()) {
            $units[] = $row;
        }
        
        return $units;
    }

    // =================== GET ALL UNITS (ALL CATEGORIES) ===================
    /**
     * Get all units grouped by category
     * Used in: Admin pages, debugging
     */
    public function getAllUnitsGrouped()
    {
        $sql = "SELECT 
                    bu.base_unit_id,
                    bu.unit_name,
                    bu.unit_type,
                    bu.is_common,
                    bu.display_order,
                    buc.base_category_id,
                    buc.category_name,
                    buc.category_code
                FROM base_units bu
                INNER JOIN base_unit_categories buc 
                    ON bu.base_category_id = buc.base_category_id
                WHERE buc.is_active = 1
                ORDER BY buc.display_order, bu.display_order";
        
        $result = $this->conn->query($sql);
        
        $grouped = [];
        while ($row = $result->fetch_assoc()) {
            $categoryCode = $row['category_code'];
            
            if (!isset($grouped[$categoryCode])) {
                $grouped[$categoryCode] = [
                    'category_id' => $row['base_category_id'],
                    'category_name' => $row['category_name'],
                    'category_code' => $categoryCode,
                    'units' => []
                ];
            }
            
            $grouped[$categoryCode]['units'][] = [
                'base_unit_id' => $row['base_unit_id'],
                'unit_name' => $row['unit_name'],
                'unit_type' => $row['unit_type'],
                'is_common' => $row['is_common'],
                'display_order' => $row['display_order']
            ];
        }
        
        return $grouped;
    }

    // =================== GET UNIT BY ID ===================
    public function getUnitById($unitId)
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                bu.base_unit_id,
                bu.unit_name,
                bu.base_category_id,
                bu.unit_type,
                bu.is_common,
                buc.category_name,
                buc.category_code
             FROM base_units bu
             INNER JOIN base_unit_categories buc 
                ON bu.base_category_id = buc.base_category_id
             WHERE bu.base_unit_id = ?"
        );
        
        $stmt->bind_param("i", $unitId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // =================== SEARCH UNITS ===================
    /**
     * Search for units by name across all categories
     * Used in: Auto-complete, search functionality
     */
    public function searchUnits($searchTerm)
    {
        $searchTerm = '%' . $searchTerm . '%';
        
        $stmt = $this->conn->prepare(
            "SELECT 
                bu.base_unit_id,
                bu.unit_name,
                bu.unit_type,
                buc.category_name,
                buc.category_code
             FROM base_units bu
             INNER JOIN base_unit_categories buc 
                ON bu.base_category_id = buc.base_category_id
             WHERE bu.unit_name LIKE ?
                AND buc.is_active = 1
             ORDER BY buc.display_order, bu.display_order
             LIMIT 20"
        );
        
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $units = [];
        while ($row = $result->fetch_assoc()) {
            $units[] = $row;
        }
        
        return $units;
    }

    // =================== GET STATISTICS ===================
    /**
     * Get statistics about base unit system
     * Used in: Admin dashboard
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM base_unit_categories WHERE is_active = 1) AS total_categories,
                    (SELECT COUNT(*) FROM base_units) AS total_units,
                    (SELECT COUNT(*) FROM base_units WHERE is_common = 1) AS common_units,
                    (SELECT COUNT(*) FROM base_units WHERE base_category_id = 1) AS water_units,
                    (SELECT COUNT(*) FROM base_units WHERE base_category_id = 2) AS food_units,
                    (SELECT COUNT(*) FROM base_units WHERE base_category_id = 3) AS swab_units";
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    // =================== ADMIN: ADD NEW UNIT ===================
    /**
     * Add a new unit to a category
     * Used in: Admin tools (Phase 11)
     */
    public function insertUnit($categoryId, $unitName, $unitType, $isCommon = 1)
    {
        // Get next display order
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(MAX(display_order), 0) + 1 AS next_order
             FROM base_units
             WHERE base_category_id = ?"
        );
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $displayOrder = $row['next_order'];
        
        // Insert unit
        $stmt = $this->conn->prepare(
            "INSERT INTO base_units 
             (unit_name, base_category_id, unit_type, is_common, display_order, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        
        $stmt->bind_param("ssiii", $unitName, $categoryId, $unitType, $isCommon, $displayOrder);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    // =================== ADMIN: UPDATE UNIT ===================
    public function updateUnit($unitId, $unitName, $unitType, $isCommon)
    {
        $stmt = $this->conn->prepare(
            "UPDATE base_units
             SET unit_name = ?, unit_type = ?, is_common = ?
             WHERE base_unit_id = ?"
        );
        
        $stmt->bind_param("ssii", $unitName, $unitType, $isCommon, $unitId);
        return $stmt->execute();
    }

    // =================== ADMIN: DELETE UNIT ===================
    /**
     * Delete a unit (only if not used by any parameters)
     * Should check usage first!
     */
    public function deleteUnit($unitId)
    {
        // Check if unit is used (will implement in Phase 5)
        // For now, just allow deletion
        
        $stmt = $this->conn->prepare("DELETE FROM base_units WHERE base_unit_id = ?");
        $stmt->bind_param("i", $unitId);
        return $stmt->execute();
    }
}