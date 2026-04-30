<?php
require_once __DIR__ . '/../../Config/Database.php';

class UserModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // =================== GET ALL USERS ===================
    public function getAllUsers()
    {
        $sql = "SELECT user_id, fullname, username, email, role, status 
                FROM users 
                WHERE (is_deleted = 0 OR is_deleted IS NULL)
                ORDER BY user_id DESC";
        $result = $this->conn->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($username, $email, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND is_deleted = 0 AND user_id != ?");
            $stmt->bind_param("ssi", $username, $email, $excludeId);
        } else {
            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND is_deleted = 0");
            $stmt->bind_param("ss", $username, $email);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== INSERT ===================
    public function insertUser($fullname, $username, $email, $role, $password)
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (fullname, username, email, role, password_hash, status)
                                      VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssss", $fullname, $username, $email, $role, $password_hash);
        return $stmt->execute();
    }

    // =================== UPDATE ===================
    public function updateUser($id, $fullname, $username, $email, $role, $status, $password = null)
    {
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users 
                                          SET fullname = ?, username = ?, email = ?, role = ?, status = ?, password_hash = ?
                                          WHERE user_id = ?");
            $stmt->bind_param("ssssssi", $fullname, $username, $email, $role, $status, $password_hash, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users 
                                          SET fullname = ?, username = ?, email = ?, role = ?, status = ?
                                          WHERE user_id = ?");
            $stmt->bind_param("sssssi", $fullname, $username, $email, $role, $status, $id);
        }
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteUser($id)
    {
        $stmt = $this->conn->prepare("UPDATE users SET is_deleted = 1, status = 'inactive' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =================== DEACTIVATE ===================
    public function deactivateUser($id)
    {
        $stmt = $this->conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>