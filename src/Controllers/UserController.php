
<?php
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
}

// Ensure user is authenticated and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Admin access required.']);
    exit;
}

require_once __DIR__ . '/../Models/UserModel.php';
header('Content-Type: application/json');

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    echo json_encode(['status' => 'error', 'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"]);
    exit;
});

$model = new UserModel();
$action = $_POST['action'] ?? '';

// Regex patterns
$nameRegex = '/^[A-Za-z.\s]{3,}$/'; // Only letters and spaces, min 3
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,12}$/'; // 8-12 chars, 1 uppercase, 1 lowercase, 1 number
$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'fetchAll') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
        exit;
    }
}

switch ($action) {

    // ========== FETCH ALL USERS ==========
    case 'fetchAll':
        $users = $model->getAllUsers();
        echo json_encode(['status' => 'success', 'data' => $users]);
        break;

    // ========== INSERT USER ==========
    case 'insert':
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($fullname) || empty($username) || empty($email) || empty($role) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        if (!preg_match($nameRegex, $fullname)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be at least 3 characters and contain only letters and spaces.']);
            exit;
        }

        if (!preg_match($emailRegex, $email)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        if (!preg_match($passwordRegex, $password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be 8-12 characters, include 1 uppercase, 1 lowercase, and 1 number.']);
            exit;
        }

        // Allowed roles based on RolePermissions.php
        $allowedRoles = ['Admin', 'LabManager', 'LabTechnician', 'Receptionist', 'Client'];
        if (!in_array($role, $allowedRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role.']);
            exit;
        }

        // Prevent duplicates
        if ($model->isDuplicate($username, $email)) {
            echo json_encode(['status' => 'error', 'message' => 'Username or email already exists!']);
            exit;
        }

        if ($model->insertUser($fullname, $username, $email, $role, $password)) {
            echo json_encode(['status' => 'success', 'message' => 'User added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
        }
        break;

    // ========== UPDATE USER ==========
    case 'update':
        $id = intval($_POST['user_id'] ?? 0);
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;

        if (empty($fullname) || empty($username) || empty($email) || empty($role) || empty($status) || $id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        if (!preg_match($nameRegex, $fullname)) {
            echo json_encode(['status' => 'error', 'message' => 'Name must be at least 3 characters and contain only letters and spaces.']);
            exit;
        }

        if (!preg_match($emailRegex, $email)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        if ($password !== null && !preg_match($passwordRegex, $password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be 8-12 characters, include 1 uppercase, 1 lowercase, and 1 number.']);
            exit;
        }

        $allowedRoles = ['Admin', 'LabManager', 'LabTechnician', 'Receptionist', 'Client'];
        if (!in_array($role, $allowedRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role.']);
            exit;
        }

        // Prevent duplicates (excluding current user)
        if ($model->isDuplicate($username, $email, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Username or email already exists!']);
            exit;
        }

        if ($model->updateUser($id, $fullname, $username, $email, $role, $status, $password)) {
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
        break;

    // ========== DEACTIVATE ==========
    case 'delete':
        $id = intval($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
            exit;
        }
        if ($model->softDeleteUser($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
