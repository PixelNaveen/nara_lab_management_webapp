<?php
require_once __DIR__ . '/../../Config/Database.php';

class TestMethodModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // =================== GET ALL ACTIVE TEST METHODS ===================
    public function getAllTestMethods()
    {
        $sql = "SELECT method_id, method_name, standard_body, 
                IF(is_active = 1, 'active', 'inactive') AS status
                FROM test_methods WHERE is_deleted = 0 ORDER BY method_id ASC";
        $result = $this->conn->query($sql);
        $testMethods = [];
        while ($row = $result->fetch_assoc()) {
            $testMethods[] = $row;
        }
        return $testMethods;
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($method_name, $standard_body, $exclude_id = null)
    {
        $sql = "SELECT method_id FROM test_methods WHERE method_name = ? AND standard_body = ? AND is_deleted = 0";
        if ($exclude_id !== null) {
            $sql .= " AND method_id != ?";
        }
        $stmt = $this->conn->prepare($sql);
        if ($exclude_id !== null) {
            $stmt->bind_param("ssi", $method_name, $standard_body, $exclude_id);
        } else {
            $stmt->bind_param("ss", $method_name, $standard_body);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== INSERT ===================
    public function insertTestMethod($method_name, $standard_body, $status)
    {
        $is_active = ($status === 'active') ? 1 : 0;
        $stmt = $this->conn->prepare("INSERT INTO test_methods (method_name, standard_body, is_active, created_at)
                                      VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $method_name, $standard_body, $is_active);
        return $stmt->execute();
    }

    // =================== UPDATE ===================
    public function updateTestMethod($id, $method_name, $standard_body, $status)
    {
        $is_active = ($status === 'active') ? 1 : 0;
        $stmt = $this->conn->prepare("UPDATE test_methods 
                                      SET method_name = ?, standard_body = ?, is_active = ?, updated_at = NOW()
                                      WHERE method_id = ?");
        $stmt->bind_param("ssii", $method_name, $standard_body, $is_active, $id);
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteTestMethod($id)
    {
        $stmt = $this->conn->prepare("UPDATE test_methods SET is_deleted = 1 WHERE method_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Get soft-deleted method ID by name + body
public function getDeletedMethodId($method_name, $standard_body)
{
    $stmt = $this->conn->prepare("SELECT method_id FROM test_methods WHERE method_name = ? AND standard_body = ? AND is_deleted = 1");
    $stmt->bind_param("ss", $method_name, $standard_body);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['method_id'];
    }
    return null;
}

// Reactivate soft-deleted method
public function reactivateTestMethod($id, $status)
{
    $is_active = ($status === 'active') ? 1 : 0;
    $stmt = $this->conn->prepare("UPDATE test_methods SET is_deleted = 0, is_active = ?, updated_at = NOW() WHERE method_id = ?");
    $stmt->bind_param("ii", $is_active, $id);
    return $stmt->execute();
}

}
?>