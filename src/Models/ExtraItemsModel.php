<?php
// src/Models/ExtraItemsModel.php - UPDATED WITHOUT DISPLAY_ORDER

require_once __DIR__ . '/../../Config/Database.php';

class ExtraItemsModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Get all items with filters
     */
    public function getAllItems($filters = [])
    {
        try {
            $sql = "SELECT item_id, item_name, item_value, item_unit, item_price, 
                           item_description, is_active, created_at, created_by 
                    FROM extra_items 
                    WHERE is_deleted = 0";
            
            // Apply filters
            if (isset($filters['is_active']) && $filters['is_active'] !== '') {
                $sql .= " AND is_active = " . intval($filters['is_active']);
            }
            
            if (isset($filters['search']) && trim($filters['search']) !== '') {
                $search = $this->conn->real_escape_string($filters['search']);
                $sql .= " AND LOWER(item_name) LIKE LOWER('{$search}%')";
            }
            
            // Sorting
            $sortBy = $filters['sort'] ?? 'name';
            switch ($sortBy) {
                case 'price':
                    $sql .= " ORDER BY item_price ASC";
                    break;
                case 'date':
                    $sql .= " ORDER BY created_at DESC";
                    break;
                default:
                    $sql .= " ORDER BY item_name ASC, item_value ASC";
            }
            
            $result = $this->conn->query($sql);
            $items = [];
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return [
                'data' => $items,
                'total' => count($items)
            ];
        } catch (Exception $e) {
            error_log("ExtraItemsModel::getAllItems Error: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Get item by ID
     */
    public function getItemById($itemId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM extra_items WHERE item_id = ? AND is_deleted = 0"
            );
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("ExtraItemsModel::getItemById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if item exists (same name + value + unit)
     */
    public function itemExists($itemName, $itemValue, $itemUnit, $excludeId = null)
    {
        try {
            $sql = "SELECT item_id FROM extra_items 
                    WHERE LOWER(item_name) = LOWER(?) 
                      AND item_value = ? 
                      AND item_unit = ?
                      AND is_deleted = 0";
            
            if ($excludeId) {
                $sql .= " AND item_id != ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sdsi", $itemName, $itemValue, $itemUnit, $excludeId);
            } else {
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sds", $itemName, $itemValue, $itemUnit);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("ExtraItemsModel::itemExists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert new item
     */
    public function insertItem($itemName, $itemValue, $itemUnit, $itemPrice, $itemDescription, $createdBy = 'admin')
    {
        try {
            $itemName = $this->sanitizeItemName($itemName);
            $itemDescription = trim($itemDescription);
            
            $stmt = $this->conn->prepare(
                "INSERT INTO extra_items (item_name, item_value, item_unit, item_price, 
                                         item_description, created_by) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param("sdsdss", $itemName, $itemValue, $itemUnit, $itemPrice, 
                             $itemDescription, $createdBy);
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("ExtraItemsModel::insertItem Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update item
     */
    public function updateItem($itemId, $itemName, $itemValue, $itemUnit, $itemPrice, $itemDescription)
    {
        try {
            $itemName = $this->sanitizeItemName($itemName);
            $itemDescription = trim($itemDescription);
            
            $stmt = $this->conn->prepare(
                "UPDATE extra_items 
                 SET item_name = ?, item_value = ?, item_unit = ?, item_price = ?,
                     item_description = ?
                 WHERE item_id = ?"
            );
            
            $stmt->bind_param("sdsdsi", $itemName, $itemValue, $itemUnit, $itemPrice,
                             $itemDescription, $itemId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ExtraItemsModel::updateItem Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete item
     */
    public function softDeleteItem($itemId)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE extra_items SET is_deleted = 1, is_active = 0 WHERE item_id = ?"
            );
            $stmt->bind_param("i", $itemId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ExtraItemsModel::softDeleteItem Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle item status
     */
    public function toggleStatus($itemId, $isActive)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE extra_items SET is_active = ? WHERE item_id = ?"
            );
            $stmt->bind_param("ii", $isActive, $itemId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ExtraItemsModel::toggleStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0
            ];
            
            $result = $this->conn->query(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                 FROM extra_items 
                 WHERE is_deleted = 0"
            );
            
            if ($row = $result->fetch_assoc()) {
                $stats = $row;
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("ExtraItemsModel::getStatistics Error: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    /**
     * Check if deleted record exists
     */
    public function findDeletedByDetails($itemName, $itemValue, $itemUnit)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT item_id FROM extra_items 
                 WHERE LOWER(item_name) = LOWER(?) 
                   AND item_value = ?
                   AND item_unit = ?
                   AND is_deleted = 1"
            );
            $stmt->bind_param("sds", $itemName, $itemValue, $itemUnit);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Reactivate deleted item
     */
    public function reactivateItem($itemId, $itemPrice, $itemDescription)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE extra_items 
                 SET is_deleted = 0, is_active = 1, item_price = ?, item_description = ?
                 WHERE item_id = ?"
            );
            $stmt->bind_param("dsi", $itemPrice, $itemDescription, $itemId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ExtraItemsModel::reactivateItem Error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Sanitize item name (letters and spaces only)
     */
    private function sanitizeItemName($name)
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);
        // Keep only letters and spaces
        return preg_replace('/[^a-zA-Z\s]/', '', $name);
    }
}
?>