const getCsrfToken = () => document.getElementById("csrf_token").value;
const AUTH_CONTROLLER = "../Controllers/AuthController.php";

// Toggle password visibility
function togglePassword() {
  const passwordInput = document.getElementById("password");
  const toggleIcon = document.getElementById("toggleIcon");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleIcon.classList.remove("fa-eye");
    toggleIcon.classList.add("fa-eye-slash");
  } else {
    passwordInput.type = "password";
    toggleIcon.classList.remove("fa-eye-slash");
    toggleIcon.classList.add("fa-eye");
  }
}

// Show alert message
function showAlert(message, type = "danger") {
  const alertDiv = document.getElementById("alertMessage");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
                <i class="fas fa-${type === "success" ? "check-circle" : type === "warning" ? "exclamation-triangle" : "exclamation-circle"} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
  alertDiv.style.display = "block";

  // Auto-hide after 5 seconds
  setTimeout(() => {
    alertDiv.style.display = "none";
  }, 5000);
}

// Form validation
function validateForm() {
  const form = document.getElementById("loginForm");
  const username = document.getElementById("username");
  const password = document.getElementById("password");
  let isValid = true;

  // Remove previous validation
  form.classList.remove("was-validated");

  // Validate username
  if (!username.value.trim()) {
    username.classList.add("is-invalid");
    isValid = false;
  } else {
    username.classList.remove("is-invalid");
    username.classList.add("is-valid");
  }

  // Validate password
  if (!password.value) {
    password.classList.add("is-invalid");
    isValid = false;
  } else {
    password.classList.remove("is-invalid");
    password.classList.add("is-valid");
  }

  return isValid;
}

// Handle form submission
document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  // Validate form
  if (!validateForm()) {
    return;
  }

  const loginBtn = document.getElementById("loginBtn");
  const formData = new FormData(e.target);
  formData.append("action", "login");
  formData.append("csrf_token", getCsrfToken());

  // Disable button and show loading
  loginBtn.disabled = true;
  loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

  try {
    const response = await fetch(AUTH_CONTROLLER, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showAlert("Login successful! Redirecting to dashboard...", "success");

      // Clear form validation
      document.getElementById("username").classList.remove("is-valid");
      document.getElementById("password").classList.remove("is-valid");

      // Redirect after short delay
      setTimeout(() => {
        window.location.href = result.redirect;
      }, 1000);
    } else {
      showAlert(result.message, "danger");
      loginBtn.disabled = false;
      loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
    }
  } catch (error) {
    console.error("Login error:", error);
    showAlert(
      "An error occurred. Please check your connection and try again.",
      "danger",
    );
    loginBtn.disabled = false;
    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
  }
});

// Clear validation on input
document.getElementById("username").addEventListener("input", function () {
  this.classList.remove("is-invalid", "is-valid");
});

document.getElementById("password").addEventListener("input", function () {
  this.classList.remove("is-invalid", "is-valid");
});

// Enter key on password field
document.getElementById("password").addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    document.getElementById("loginForm").dispatchEvent(new Event("submit"));
  }
});

// Prevent back button after login
window.history.forward();

function noBack() {
  window.history.forward();
}
