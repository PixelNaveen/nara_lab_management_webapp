<!-- src/Views/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.login-card {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    width: 360px;
    padding: 30px;
}
.login-card h2 {
    color: #0d6efd;
    text-align: center;
    margin-bottom: 25px;
}
.btn-primary {
    width: 100%;
}
</style>
</head>
<body>
<div class="login-card">
    <h2>Login</h2>
    <form id="loginForm">
        <div class="mb-3">
            <label for="loginIdentifier" class="form-label">Username or Email</label>
            <input type="text" class="form-control" id="loginIdentifier" name="identifier" placeholder="Enter username or email" required>
        </div>
        <div class="mb-3">
            <label for="loginPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <div class="mt-3 text-center" id="loginMessage"></div>
    </form>
</div>

<script>
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const CONTROLLER_PATH = '../../src/Controllers/user-controller.php';

loginForm.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(loginForm);
    
    fetch(CONTROLLER_PATH, {
        method: 'POST',
        body: new URLSearchParams({
            action: 'login',
            identifier: formData.get('identifier'),
            password: formData.get('password')
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            loginMessage.textContent = data.message;
            loginMessage.className = 'text-success';
            // Redirect or perform actions after successful login
            setTimeout(() => { window.location.href = '../../index.php'; }, 1000);
        } else {
            loginMessage.textContent = data.message;
            loginMessage.className = 'text-danger';
        }
    })
    .catch(() => {
        loginMessage.textContent = 'Network error!';
        loginMessage.className = 'text-danger';
    });
});
</script>
</body>
</html>