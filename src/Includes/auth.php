<?php

/**
 * Authentication Check Helper
 * Include this file at the top of any protected page
 * 
 * Security Features:
 * - Prevents caching of protected pages
 * - Checks if user is logged in
 * - Validates session timeout
 * - Prevents session hijacking (IP check)
 * - Regenerates session ID periodically
 * 
 * @package LabManagementSystem
 * @subpackage Includes
 * @version 1.0
 */

//  PREVENT CACHING OF PROTECTED PAGES
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

//  CONFIGURE SESSION LIFETIME (Override server defaults)
// Set to 2 hours (7200 seconds) to match our custom inactivity logic
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 0); // Cookie expires when browser closes

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // User is not logged in, redirect to login page
    header('Location: src/Views/login.php');
    exit;
}

require_once __DIR__ . '/session-helper.php';
checkSessionTimeout(false);

//  IP ADDRESS VALIDATION (Prevents session hijacking)
// Note: Can be disabled if users have dynamic IPs
if (isset($_SESSION['ip_address'])) {
    $currentIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if ($_SESSION['ip_address'] !== $currentIp) {
        // IP changed - possible session hijacking
        $_SESSION = [];
        session_destroy();
        header('Location: src/Views/login.php?security=1');
        exit;
    }
}

//  REGENERATE SESSION ID PERIODICALLY (Every 5 minutes)
// Prevents session fixation attacks
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

//  UPDATE LAST ACTIVITY
$_SESSION['last_activity'] = time();

//  MAKE USER DATA AVAILABLE GLOBALLY
$currentUser = [
    'user_id' => $_SESSION['user_id'],
    'fullname' => $_SESSION['fullname'],
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'] ?? '',
    'role' => $_SESSION['role']
];

//  GET USER INITIALS FOR DISPLAY
function getUserInitials($fullname)
{
    $names = explode(' ', trim($fullname));
    $initials = strtoupper(substr($names[0], 0, 1));
    if (isset($names[1])) {
        $initials .= strtoupper(substr($names[1], 0, 1));
    }
    return $initials;
}

$userInitials = getUserInitials($currentUser['fullname']);

//  SESSION IS VALID - Continue with page
