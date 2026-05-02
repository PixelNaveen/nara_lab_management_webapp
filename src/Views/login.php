<?php

/**
 * Login Page
 * Laboratory Management System
 */

session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../index.php?page=dashboard');
    exit;
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get messages from URL
$timeout = isset($_GET['timeout']) ? true : false;
$security = isset($_GET['security']) ? true : false;
$registered = isset($_GET['registered']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Management System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../../public/images/Nara logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="login-header">
                <img src="../../public/images/Nara logo.png" alt="NARA Logo" class="logo">
                <h2>Laboratory Management System</h2>
                <p class="text-muted">National Aquatic Resources Research & Development Agency</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertMessage" style="display: none;"></div>

            <?php if ($timeout): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-clock me-2"></i>Your session has expired. Please login again.
                </div>
            <?php endif; ?>

            <?php if ($security): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-shield-alt me-2"></i>Security alert: Session terminated. Please login again.
                </div>
            <?php endif; ?>

            <?php if ($registered): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Registration successful! Please login with your credentials.
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="loginForm" method="POST" novalidate>
                <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Username or Email
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        name="username"
                        id="username"
                        placeholder="Enter your username or email"
                        required
                        autofocus>
                    <div class="invalid-feedback" id="usernameError">
                        Please enter your username or email.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input
                            type="password"
                            class="form-control"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="passwordError">
                        Please enter your password.
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="remember_me"
                        id="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        Remember me for 30 days
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <!-- Footer Links -->
            <div class="login-footer">
                <a href="#" class="text-muted">Forgot password?</a>
                <span class="text-muted">•</span>
                <a href="register.php?from=login">Create account</a>
            </div>

            <!-- System Info -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    Laboratory Management System v1.0<br>
                    © 2025 NARA. All rights reserved.
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Load External JS -->
    <script src="../../public/assets/js/login.js"></script>
</body>

</html>