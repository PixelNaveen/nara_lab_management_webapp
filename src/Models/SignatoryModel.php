<?php

/**
 * Signatory Model
 * Laboratory Management System
 *
 * CRUD operations for report signatories (scientists and heads).
 *
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class SignatoryModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get all active signatories.
     */
    public function getAll()
    {
        $sql = "SELECT signatory_id, full_name, title, division, role_type,
                       is_default, display_order, is_active, created_at, updated_at
                FROM report_signatories
                WHERE is_deleted = 0
                ORDER BY display_order ASC, full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $signatories = [];
        while ($row = $result->fetch_assoc()) {
            $signatories[] = $row;
        }
        return $signatories;
    }

    /**
     * Get a single signatory by ID.
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM report_signatories WHERE signatory_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Check if a signatory already exists by name.
     */
    public function exists($fullName, $excludeId = null)
    {
        $sql = "SELECT signatory_id FROM report_signatories 
                WHERE LOWER(full_name) = LOWER(?) AND is_deleted = 0";
        
        if ($excludeId) {
            $sql .= " AND signatory_id != ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('si', $fullName, $excludeId);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('s', $fullName);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Create a new signatory.
     */
    public function create($data)
    {
        $sql = "INSERT INTO report_signatories 
                (full_name, title, division, role_type, is_default, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $isDefault = (int) ($data['is_default'] ?? 0);
        $displayOrder = (int) ($data['display_order'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1);

        $stmt->bind_param(
            'ssssiii',
            $data['full_name'],
            $data['title'],
            $data['division'],
            $data['role_type'],
            $isDefault,
            $displayOrder,
            $isActive
        );

        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    /**
     * Update an existing signatory.
     */
    public function update($id, $data)
    {
        $sql = "UPDATE report_signatories SET 
                    full_name = ?,
                    title = ?,
                    division = ?,
                    role_type = ?,
                    is_default = ?,
                    display_order = ?,
                    is_active = ?
                WHERE signatory_id = ? AND is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $isDefault = (int) ($data['is_default'] ?? 0);
        $displayOrder = (int) ($data['display_order'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1);

        $stmt->bind_param(
            'ssssiiii',
            $data['full_name'],
            $data['title'],
            $data['division'],
            $data['role_type'],
            $isDefault,
            $displayOrder,
            $isActive,
            $id
        );

        return $stmt->execute();
    }

    /**
     * Soft delete a signatory.
     */
    public function softDelete($id)
    {
        $sql = "UPDATE report_signatories SET is_deleted = 1, is_active = 0 WHERE signatory_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
