<?php
// src/Views/login.php

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in → go to dashboard (index.php)
if (isset($_SESSION['user']) && !empty($_SESSION['user']['user_id'])) {
    header("Location: " . dirname(__DIR__, 2) . "/index.php");
    exit();
}

require_once __DIR__ . '/../../Config/Database.php';

$db = new Database();
$conn = $db->connect();

$errors = '';
$info = '';

// Show message if redirected due to timeout
if (isset($_GET['timeout'])) {
    $info = 'You were automatically logged out due to inactivity. Please login again.';
}

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors = 'Username and password are required.';
    } else {
        // Prepare and fetch user by username
        $stmt = $conn->prepare("SELECT user_id, fullname, username, password_hash, role, is_Active FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) {
            $errors = 'Database error (prepare failed).';
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();

                // Check active flag if you have one
                if (isset($row['is_Active']) && intval($row['is_Active']) !== 1) {
                    $errors = 'Account is inactive. Contact admin.';
                } elseif (password_verify($password, $row['password_hash'])) {
                    // Auth success - create session user array
                    $user = [
                        'user_id' => $row['user_id'],
                        'fullname' => $row['fullname'],
                        'username' => $row['username'],
                        'role' => $row['role'],
                    ];

                    // compute initials
                    $fullname = trim($user['fullname']);
                    $parts = preg_split('/\s+/', $fullname);
                    $initials = '';
                    if (!empty($parts[0])) $initials .= strtoupper(substr($parts[0], 0, 1));
                    if (!empty($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                    if ($initials === '' && $fullname !== '') $initials = strtoupper(substr($fullname, 0, 1));
                    $user['initials'] = $initials;

                    // store in session
                    $_SESSION['user'] = $user;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['regen_time'] = time();

                    // redirect to root index
                    header("Location: " . dirname(__DIR__, 2) . "/index.php");
                    exit();
                } else {
                    $errors = 'Invalid credentials.';
                }
            } else {
                $errors = 'Invalid credentials.';
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - NARA Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#11998e 0%,#38ef7d 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { max-width:420px; width:100%; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="card-body">
            <h3 class="text-center mb-3 fw-bold">NARA Lab - Login</h3>

            <?php if ($info): ?>
                <div class="alert alert-info"><?= htmlspecialchars($info) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off" id="loginForm">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="register.php" class="text-muted">Create an account</a>
            </div>
        </div>
    </div>

    <script>
    // Prevent browsers from pre-filling inputs after logout/back
    window.addEventListener('pageshow', function() {
        document.getElementById('loginForm')?.reset();
    });
    </script>
</body>
</html>
