<?php

/**
 * Sample Names Model
 * CRUD operations for sample names with category management
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class SampleNamesModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // =================== GET ALL SAMPLE NAMES ===================
    public function getAllSampleNames()
    {
        $sql = "SELECT sn.sample_name_id, sn.sample_name, sn.category_id, sn.usage_count, sn.is_slab_accredited,
                       stc.category_name, stc.category_code
                FROM sample_names sn
                LEFT JOIN sample_type_categories stc ON sn.category_id = stc.category_id
                ORDER BY stc.display_order ASC, sn.sample_name ASC";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row;
        }
        return $names;
    }

    // =================== GET SINGLE SAMPLE NAME ===================
    public function getSampleNameById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT sn.sample_name_id, sn.sample_name, sn.category_id, sn.usage_count, sn.is_slab_accredited,
                    stc.category_name, stc.category_code
             FROM sample_names sn
             LEFT JOIN sample_type_categories stc ON sn.category_id = stc.category_id
             WHERE sn.sample_name_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== GET ALL CATEGORIES ===================
    public function getCategories()
    {
        $sql = "SELECT category_id, category_name, category_code, description, 
                       base_category_id, is_slab_accredited, display_order
                FROM sample_type_categories 
                WHERE is_active = 1 
                ORDER BY display_order ASC";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }

    // =================== CHECK DUPLICATE ===================
    public function isDuplicate($name, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM sample_names WHERE LOWER(sample_name) = LOWER(?)";
        $params = [$name];
        $types = "s";

        if ($excludeId !== null) {
            $sql .= " AND sample_name_id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    // =================== INSERT SAMPLE NAME ===================
    public function insertSampleName($name, $categoryId, $isSlabAccredited = 0)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO sample_names (sample_name, category_id, is_slab_accredited, usage_count, created_at) 
             VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->bind_param("sii", $name, $categoryId, $isSlabAccredited);

        if (!$stmt->execute()) {
            throw new Exception("Insert failed: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    // =================== UPDATE SAMPLE NAME ===================
    public function updateSampleName($id, $name, $categoryId, $isSlabAccredited = 0)
    {
        $stmt = $this->conn->prepare(
            "UPDATE sample_names 
             SET sample_name = ?, category_id = ?, is_slab_accredited = ?, updated_at = NOW() 
             WHERE sample_name_id = ?"
        );
        $stmt->bind_param("siii", $name, $categoryId, $isSlabAccredited, $id);

        if (!$stmt->execute()) {
            throw new Exception("Update failed: " . $stmt->error);
        }
        return $stmt->affected_rows >= 0;
    }

    // =================== DELETE SAMPLE NAME ===================
    public function deleteSampleName($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM sample_names WHERE sample_name_id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Delete failed: " . $stmt->error);
        }
        return ['success' => true, 'message' => 'Sample name deleted successfully'];
    }

    // =================== GET NAMES BY CATEGORY ===================
    public function getNamesByCategory($categoryId)
    {
        $stmt = $this->conn->prepare(
            "SELECT sample_name_id, sample_name, usage_count
             FROM sample_names 
             WHERE category_id = ?
             ORDER BY sample_name ASC"
        );
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row;
        }
        return $names;
    }

    // =================== GET CATEGORY STATS ===================
    public function getCategoryStats()
    {
        $sql = "SELECT stc.category_id, stc.category_name, stc.category_code, 
                       stc.is_slab_accredited, stc.display_order,
                       COUNT(sn.sample_name_id) as name_count,
                       COALESCE(SUM(sn.usage_count), 0) as total_usage
                FROM sample_type_categories stc
                LEFT JOIN sample_names sn ON stc.category_id = sn.category_id
                WHERE stc.is_active = 1
                GROUP BY stc.category_id
                ORDER BY stc.display_order ASC";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }
}
