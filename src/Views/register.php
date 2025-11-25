<?php
// File: src/Views/register.php
session_start();

// If already logged in → go to dashboard
if (isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../../Config/Database.php';

$success = false;
$error = '';

// Check if any user exists (optional: block registration if users exist)
$db = new Database();
$result = $db->conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
$hasUsers = $row['count'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $role     = $_POST['role'] ?? 'LabTechnician';

    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if username already exists
        $check = $db->conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username already taken";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->conn->prepare("INSERT INTO users (fullname, username, email, password_hash, role, is_Active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sssss", $fullname, $username, $email, $hash, $role);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }

    // Always redirect to prevent form resubmit
    $query = $success ? "?success=1" : "?error=" . urlencode($error);
    header("Location: register.php" . $query);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create First User - NARA Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); height: 100vh; display: flex; align-items: center; }
        .register-box { max-width: 500px; width: 100%; margin: auto; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="register-box card">
    <div class="card-body p-5">
        <h3 class="text-center mb-4 text-primary fw-bold">Create Account</h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">
                Account created successfully! <br>
                <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (!$hasUsers): ?>
            <div class="alert alert-info mb-4">
                This is the first user. This account will have <strong>Admin</strong> access.
            </div>
        <?php endif; ?>

        

        <form method="POST" autocomplete="off" id="registerForm">
            <div class="mb-3">
                <input type="text" name="fullname" class="form-control form-control-lg" placeholder="Full Name" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <input type="password" name="confirm" class="form-control form-control-lg" placeholder="Confirm Password" required>
                </div>
            </div>
            <div class="mb-4">
                <select name="role" class="form-select form-select-lg">
                    <option value="LabTechnician">Lab Technician</option>
                    <option value="Assistant">Assistant</option>
                    <option value="Admin" <?= !$hasUsers ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">Create Account</button>
            <div class="text-center mt-4">
                <a href="login.php" class="text-muted">Already have an account? Login</a>
            </div>
        </form>
    </div>
</div>

<script>
// Clear form on refresh/back button
window.addEventListener('pageshow', function(e) {
    document.getElementById('registerForm')?.reset();
    document.querySelectorAll('input').forEach(i => i.value = '');
});
</script>

</body>
</html>