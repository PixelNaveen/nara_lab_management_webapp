<?php
/**
 * Authentication Controller
 * Handles login, logout, registration, and session management
 * 
 * @package LabManagementSystem
 * @subpackage Controllers
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../Models/auth-model.php';

header('Content-Type: application/json');

$authModel = new AuthModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($authModel);
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'register':
        handleRegister($authModel);
        break;
    
    case 'checkSession':
        checkSession($authModel);
        break;
    
    case 'changePassword':
        changePassword($authModel);
        break;
    
    default:
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid action'
        ]);
}

/**
 * Handle login request
 */
function handleLogin($authModel) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Username and password are required'
        ]);
        return;
    }

    // Validate credentials
    $result = $authModel->validateUser($username, $password);

    if ($result['success']) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Create session with all user data
        $_SESSION['user_id'] = $result['user']['user_id'];
        $_SESSION['fullname'] = $result['user']['fullname'];
        $_SESSION['username'] = $result['user']['username'];
        $_SESSION['email'] = $result['user']['email'];
        $_SESSION['role'] = $result['user']['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_regeneration'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        // Remember me cookie (optional - 30 days)
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            // Note: Store token in database for production use
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'fullname' => $result['user']['fullname'],
                'username' => $result['user']['username'],
                'role' => $result['user']['role']
            ],
            'redirect' => '../../index.php?page=dashboard'
        ]);
    } else {
        echo json_encode($result);
    }
}

/**
 * Handle logout request
 */
function handleLogout() {
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 3600,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => '../Views/login.php'
    ]);
}

/**
 * Handle registration request
 */
function handleRegister($authModel) {
    $data = [
        'fullname' => trim($_POST['fullname'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
        'role' => $_POST['role'] ?? 'LabTechnician'
    ];

    // Validation
    if (empty($data['fullname']) || empty($data['username']) || 
        empty($data['email']) || empty($data['password'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'All fields are required'
        ]);
        return;
    }

    // Check password confirmation
    if ($data['password'] !== $data['password_confirm']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Passwords do not match'
        ]);
        return;
    }

    // Validate password strength
    if (strlen($data['password']) < 6) {
        echo json_encode([
            'success' => false, 
            'message' => 'Password must be at least 6 characters long'
        ]);
        return;
    }

    // Validate username (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Username must be 3-20 characters (letters, numbers, underscore only)'
        ]);
        return;
    }

    // Register user
    $result = $authModel->registerUser($data);
    echo json_encode($result);
}

/**
 * Check if session is valid
 */
function checkSession($authModel) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || 
        $_SESSION['logged_in'] !== true) {
        echo json_encode([
            'success' => false, 
            'message' => 'Not logged in'
        ]);
        return;
    }

    // Verify user still exists and is active
    $user = $authModel->getUserById($_SESSION['user_id']);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'fullname' => $user['fullname'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // User no longer exists or is inactive
        session_destroy();
        echo json_encode([
            'success' => false, 
            'message' => 'Session invalid'
        ]);
    }
}

/**
 * Handle password change request
 */
function changePassword($authModel) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Not logged in'
        ]);
        return;
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode([
            'success' => false, 
            'message' => 'All fields are required'
        ]);
        return;
    }

    // Check password confirmation
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false, 
            'message' => 'New passwords do not match'
        ]);
        return;
    }

    $result = $authModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
    echo json_encode($result);
}
?>