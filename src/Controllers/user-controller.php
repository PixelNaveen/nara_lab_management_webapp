// user-controller.php
<?php
require_once __DIR__ . '/../Models/user-model.php';
header('Content-Type: application/json');

$model = new UserModel();
$action = $_POST['action'] ?? '';

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

        if (!in_array($role, ['LabTechnician', 'Assistant', 'Admin'])) {
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
        $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;

        if (empty($fullname) || empty($username) || empty($email) || empty($role) || $id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        if (!in_array($role, ['LabTechnician', 'Assistant', 'Admin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role.']);
            exit;
        }

        if ($model->updateUser($id, $fullname, $username, $email, $role, $password)) {
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
        if ($model->deactivateUser($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User deactivated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Deactivation failed.']);
        }
        break;

    // ========== LOGIN ==========
    case 'login':
        session_start();
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Identifier and password are required.']);
            exit;
        }

        $user = $model->getUserByIdentifier($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
            exit;
        }

        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'name' => $user['fullname'],
            'role' => $user['role'],
            'initials' => strtoupper(implode('', array_map(fn($word) => $word[0], explode(' ', $user['fullname']))))
        ];

        echo json_encode(['status' => 'success', 'message' => 'Login successful.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>