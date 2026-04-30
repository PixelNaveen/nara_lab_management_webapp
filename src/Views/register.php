<?php

/**
 * Registration Page
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lab Management System</title>
<!-- Favicon -->
<link rel="icon" type="image/png" sizes="32x32" href="public/images/Nara logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/assets/css/login.css">

    <style>
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: #dc3545;
            width: 33%;
        }

        .strength-medium {
            background: #ffc107;
            width: 66%;
        }

        .strength-strong {
            background: #28a745;
            width: 100%;
        }

        .password-requirements {
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .password-requirements li {
            color: #6c757d;
        }

        .password-requirements li.valid {
            color: #28a745;
        }

        .password-requirements li i {
            width: 15px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="login-header">
                <img src="../../public/images/Nara logo.png" alt="NARA Logo" class="logo">
                <h2>Create Account</h2>
                <p class="text-muted">Join our Laboratory Management System</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertMessage" style="display: none;"></div>

            <!-- Registration Form -->
            <form id="registerForm" novalidate>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        name="fullname"
                        id="fullname"
                        placeholder="Enter your full name"
                        required
                        autofocus>
                    <div class="invalid-feedback">
                        Please enter your full name.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-circle"></i> Username
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        name="username"
                        id="username"
                        placeholder="Choose a username (3-20 characters)"
                        pattern="[a-zA-Z0-9_]{3,20}"
                        required>
                    <div class="invalid-feedback">
                        Username must be 3-20 characters (letters, numbers, underscore only).
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        name="email"
                        id="email"
                        placeholder="Enter your email address"
                        required>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-tag"></i> Role
                    </label>
                    <select class="form-control" name="role" id="role" required>
                        <option value="">Select your role</option>
                        <option value="LabTechnician">Lab Technician</option>
                        <option value="Assistant">Assistant</option>
                        <option value="Admin">Administrator</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select your role.
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
                            placeholder="Create a password"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggleIcon1')" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <ul class="password-requirements list-unstyled">
                        <li id="req-length"><i class="fas fa-circle"></i> At least 6 characters</li>
                        <li id="req-uppercase"><i class="fas fa-circle"></i> One uppercase letter</li>
                        <li id="req-lowercase"><i class="fas fa-circle"></i> One lowercase letter</li>
                        <li id="req-number"><i class="fas fa-circle"></i> One number</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-input">
                        <input
                            type="password"
                            class="form-control"
                            name="password_confirm"
                            id="password_confirm"
                            placeholder="Confirm your password"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm', 'toggleIcon2')" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        Passwords do not match.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="registerBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <!-- Footer Links -->
            <div class="login-footer">
                <span class="text-muted">Already have an account?</span>
                <a href="login.php">Sign in</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

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

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');

            // Remove previous classes
            strengthBar.className = 'password-strength-bar';

            // Requirements
            const hasLength = password.length >= 6;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            // Update requirements UI
            updateRequirement('req-length', hasLength);
            updateRequirement('req-uppercase', hasUppercase);
            updateRequirement('req-lowercase', hasLowercase);
            updateRequirement('req-number', hasNumber);

            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUppercase) strength++;
            if (hasLowercase) strength++;
            if (hasNumber) strength++;

            // Update strength bar
            if (strength === 0) {
                strengthBar.style.width = '0%';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });

        function updateRequirement(id, isValid) {
            const element = document.getElementById(id);
            const icon = element.querySelector('i');

            if (isValid) {
                element.classList.add('valid');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            } else {
                element.classList.remove('valid');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
        }

        // Form validation
        function validateForm() {
            const form = document.getElementById('registerForm');
            const fullname = document.getElementById('fullname');
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const role = document.getElementById('role');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');

            let isValid = true;

            // Validate fullname
            if (!fullname.value.trim()) {
                fullname.classList.add('is-invalid');
                isValid = false;
            } else {
                fullname.classList.remove('is-invalid');
                fullname.classList.add('is-valid');
            }

            // Validate username
            const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
            if (!usernameRegex.test(username.value)) {
                username.classList.add('is-invalid');
                isValid = false;
            } else {
                username.classList.remove('is-invalid');
                username.classList.add('is-valid');
            }

            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
                email.classList.add('is-valid');
            }

            // Validate role
            if (!role.value) {
                role.classList.add('is-invalid');
                isValid = false;
            } else {
                role.classList.remove('is-invalid');
                role.classList.add('is-valid');
            }

            // Validate password
            if (password.value.length < 6) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
                password.classList.add('is-valid');
            }

            // Validate password confirmation
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.classList.add('is-invalid');
                isValid = false;
            } else {
                passwordConfirm.classList.remove('is-invalid');
                passwordConfirm.classList.add('is-valid');
            }

            return isValid;
        }

        // Handle form submission
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate form
            if (!validateForm()) {
                showAlert('Please fill in all fields correctly.', 'warning');
                return;
            }

            const registerBtn = document.getElementById('registerBtn');
            const formData = new FormData(e.target);
            formData.append('action', 'register');

            // Disable button and show loading
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';

            try {
                const response = await fetch('../Controllers/AuthController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Registration successful! Redirecting to login...', 'success');

                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = 'login.php?from=register';
                    }, 2000);
                } else {
                    showAlert(result.message, 'danger');
                    registerBtn.disabled = false;
                    registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
                }

            } catch (error) {
                console.error('Registration error:', error);
                showAlert('An error occurred. Please check your connection and try again.', 'danger');
                registerBtn.disabled = false;
                registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
            }
        });

        // Clear validation on input
        ['fullname', 'username', 'email', 'role', 'password', 'password_confirm'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        });

        // Prevent back button after registration
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>
</body>

</html>