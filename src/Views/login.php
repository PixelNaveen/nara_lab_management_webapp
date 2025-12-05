<?php
// login.php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #1e3a8a;
            --primary-blue-dark: #1e40af;
            --primary-blue-light: #3b82f6;
            --success-green: #16a34a;
            --danger-red: #dc2626;
            --light-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left i {
            font-size: 80px;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-left h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .login-left p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .login-right {
            padding: 60px 50px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h3 {
            color: var(--primary-blue-dark);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-blue-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }

        .form-control {
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.2);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--primary-blue);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background-color: #fee;
            color: var(--danger-red);
        }

        .alert-success {
            background-color: #efe;
            color: var(--success-green);
        }

        .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 2px;
            margin-right: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 40px 20px;
            }

            .login-left i {
                font-size: 60px;
            }

            .login-left h2 {
                font-size: 1.5rem;
            }

            .login-right {
                padding: 40px 30px;
            }
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 mb-0">Logging in...</p>
        </div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Side -->
        <div class="login-left">
            <i class="fas fa-flask"></i>
            <h2>Lab Management System</h2>
            <p>National Aquatic Resources Research and Development Agency</p>
            <p class="mt-3">Secure access to laboratory operations</p>
        </div>

        <!-- Right Side -->
        <div class="login-right">
            <div class="login-header">
                <h3>Welcome Back!</h3>
                <p>Please login to your account</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Login Form -->
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="footer-text">
                <p class="mb-0">&copy; 2025 NARA Lab Management System</p>
                <p class="mb-0">Version 2.0</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Password Visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Show Alert Function
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}"></i>
                    ${message}
                </div>
            `;
            alertContainer.innerHTML = alertHTML;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Login Form Submit
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                showAlert('Please enter both username and password', 'danger');
                return;
            }

            // Show loading
            document.getElementById('loadingOverlay').classList.add('active');
            document.getElementById('loginBtn').disabled = true;

            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('username', username);
                formData.append('password', password);

                const response = await fetch('src/Controllers/auth-controller.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                // Hide loading
                document.getElementById('loadingOverlay').classList.remove('active');
                document.getElementById('loginBtn').disabled = false;

                if (result.status === 'success') {
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php?page=dashboard';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Invalid username or password', 'danger');
                }
            } catch (error) {
                console.error('Login error:', error);
                document.getElementById('loadingOverlay').classList.remove('active');
                document.getElementById('loginBtn').disabled = false;
                showAlert('Network error! Please try again.', 'danger');
            }
        });

        // Press Enter to submit
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>