<?php
/**
 * Authentication Model
 * Handles all database operations for user authentication
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class AuthModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Validate user credentials and return user data
     * 
     * @param string $username Username or email
     * @param string $password Plain text password
     * @return array Result with success status and user data or error message
     */
    public function validateUser($username, $password) {
        try {
            // Allow login with username or email
            $sql = "SELECT user_id, fullname, username, email, role, status, password_hash 
                    FROM users 
                    WHERE (username = ? OR email = ?) AND status = 'active'
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return [
                    'success' => false, 
                    'message' => 'Invalid username or password'
                ];
            }

            $user = $result->fetch_assoc();

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false, 
                    'message' => 'Invalid username or password'
                ];
            }

            // Check if user is active
            if ($user['status'] !== 'active') {
                return [
                    'success' => false, 
                    'message' => 'Your account has been deactivated. Please contact administrator.'
                ];
            }

            // Remove password hash from return data
            unset($user['password_hash']);

            // Update last login
            $this->updateLastLogin($user['user_id']);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful'
            ];

        } catch (Exception $e) {
            error_log("AuthModel::validateUser() Error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred. Please try again.'
            ];
        }
    }

    /**
     * Update last login timestamp and IP address
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    private function updateLastLogin($userId) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $sql = "UPDATE users 
                    SET last_login = NOW(), last_ip = ? 
                    WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $ip, $userId);
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("AuthModel::updateLastLogin() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by ID (for session validation)
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById($userId) {
        try {
            $sql = "SELECT user_id, fullname, username, email, role, status 
                    FROM users 
                    WHERE user_id = ? AND status = 'active'
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }

            return null;

        } catch (Exception $e) {
            error_log("AuthModel::getUserById() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @return bool True if exists, false otherwise
     */
    public function usernameExists($username) {
        try {
            $sql = "SELECT user_id FROM users WHERE username = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;

        } catch (Exception $e) {
            error_log("AuthModel::usernameExists() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool True if exists, false otherwise
     */
    public function emailExists($email) {
        try {
            $sql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;

        } catch (Exception $e) {
            error_log("AuthModel::emailExists() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register new user
     * 
     * @param array $data User registration data
     * @return array Result with success status and user ID or error message
     */
    public function registerUser($data) {
        try {
            // Validate required fields
            $required = ['fullname', 'username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false, 
                        'message' => ucfirst($field) . ' is required'
                    ];
                }
            }

            // Check if username already exists
            if ($this->usernameExists($data['username'])) {
                return [
                    'success' => false, 
                    'message' => 'Username already exists'
                ];
            }

            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                return [
                    'success' => false, 
                    'message' => 'Email already exists'
                ];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false, 
                    'message' => 'Invalid email format'
                ];
            }

            // Validate password strength
            if (strlen($data['password']) < 6) {
                return [
                    'success' => false, 
                    'message' => 'Password must be at least 6 characters long'
                ];
            }

            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (fullname, username, email, role, status, password_hash) 
                    VALUES (?, ?, ?, ?, 'active', ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssss", 
                $data['fullname'],
                $data['username'],
                $data['email'],
                $data['role'],
                $passwordHash
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'user_id' => $this->conn->insert_id,
                    'message' => 'Registration successful'
                ];
            }

            return [
                'success' => false, 
                'message' => 'Failed to register user'
            ];

        } catch (Exception $e) {
            error_log("AuthModel::registerUser() Error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred. Please try again.'
            ];
        }
    }

    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Result with success status
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $sql = "SELECT password_hash FROM users WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return [
                    'success' => false, 
                    'message' => 'User not found'
                ];
            }

            $user = $result->fetch_assoc();

            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return [
                    'success' => false, 
                    'message' => 'Current password is incorrect'
                ];
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false, 
                    'message' => 'New password must be at least 6 characters long'
                ];
            }

            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $newPasswordHash, $userId);

            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Password changed successfully'
                ];
            }

            return [
                'success' => false, 
                'message' => 'Failed to change password'
            ];

        } catch (Exception $e) {
            error_log("AuthModel::changePassword() Error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred. Please try again.'
            ];
        }
    }
}
?>