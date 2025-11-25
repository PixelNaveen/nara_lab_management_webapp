<?php
// src/Includes/auth.php
// Central auth/session guard. Starts session safely and enforces inactivity logout.

// Start session only if not already started to avoid warnings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no login → redirect to login page
if (!isset($_SESSION['user']) || empty($_SESSION['user']['user_id'])) {
    // Use dirname to compute correct relative path regardless of include location
    header("Location: " . dirname(__DIR__, 2) . "/src/Views/login.php");
    exit();
}

// Auto logout after 60 seconds of inactivity (as requested)
$timeoutSeconds = 60; // <--- 60 seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutSeconds)) {
    // clear session and redirect to login with timeout flag
    session_unset();
    session_destroy();
    header("Location: " . dirname(__DIR__, 2) . "/src/Views/login.php?timeout=1");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Optional: regenerate session id occasionally to mitigate fixation
if (!isset($_SESSION['regen_time'])) {
    $_SESSION['regen_time'] = time();
} elseif (time() - $_SESSION['regen_time'] > 300) { // regen every 5 minutes
    session_regenerate_id(true);
    $_SESSION['regen_time'] = time();
}
?>
