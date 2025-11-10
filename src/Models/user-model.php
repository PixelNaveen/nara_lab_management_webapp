// user-model.php
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
                FROM users ORDER BY user_id DESC";
        $result = $this->conn->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($username, $email)
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
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
    public function updateUser($id, $fullname, $username, $email, $role, $password = null)
    {
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users 
                                          SET fullname = ?, username = ?, email = ?, role = ?, password_hash = ?
                                          WHERE user_id = ?");
            $stmt->bind_param("sssssi", $fullname, $username, $email, $role, $password_hash, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users 
                                          SET fullname = ?, username = ?, email = ?, role = ?
                                          WHERE user_id = ?");
            $stmt->bind_param("ssssi", $fullname, $username, $email, $role, $id);
        }
        return $stmt->execute();
    }

    // =================== DEACTIVATE (SOFT DELETE) ===================
    public function deactivateUser($id)
    {
        $stmt = $this->conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =================== GET USER BY IDENTIFIER ===================
    public function getUserByIdentifier($identifier)
    {
        $stmt = $this->conn->prepare("SELECT user_id, fullname, username, email, role, status, password_hash 
                                      FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>