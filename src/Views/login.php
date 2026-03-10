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
<link rel="icon" type="image/png" sizes="32x32" href="public/images/Nara logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            <form id="loginForm" novalidate>
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
                    <div class="invalid-feedback">
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
                    <div class="invalid-feedback">
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

            <!-- Removed Footer Links based on professional/government UI guidelines -->

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Show alert message
        function showAlert(message, type = 'danger') {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }

        // Form validation
        function validateForm() {
            const form = document.getElementById('loginForm');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            let isValid = true;

            // Remove previous validation
            form.classList.remove('was-validated');

            // Validate username
            if (!username.value.trim()) {
                username.classList.add('is-invalid');
                isValid = false;
            } else {
                username.classList.remove('is-invalid');
                username.classList.add('is-valid');
            }

            // Validate password
            if (!password.value) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
                password.classList.add('is-valid');
            }

            return isValid;
        }

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate form
            if (!validateForm()) {
                return;
            }

            const loginBtn = document.getElementById('loginBtn');
            const formData = new FormData(e.target);
            formData.append('action', 'login');

            // Disable button and show loading
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

            try {
                const response = await fetch('../Controllers/auth-controller.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Login successful! Redirecting to dashboard...', 'success');

                    // Clear form validation
                    document.getElementById('username').classList.remove('is-valid');
                    document.getElementById('password').classList.remove('is-valid');

                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    showAlert(result.message, 'danger');
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                }

            } catch (error) {
                console.error('Login error:', error);
                showAlert('An error occurred. Please check your connection and try again.', 'danger');
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
            }
        });

        // Clear validation on input
        document.getElementById('username').addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
        });

        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
        });

        // Enter key on password field
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });

        // Prevent back button after login
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>
</body>

</html>