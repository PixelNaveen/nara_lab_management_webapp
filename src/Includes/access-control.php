<?php
/**
 * Page Access Control Middleware
 * Include this after auth.php on protected pages
 * 
 * Usage: require_once 'access-control.php';
 * 
 * @package LabManagementSystem
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/roles-permissions.php';

// Get current page
$currentPage = $_GET['page'] ?? 'dashboard';

// Get user role from session
$userRole = $_SESSION['role'] ?? 'Client';

// Check if user has permission to access this page
if (!RolePermissions::hasPermission($userRole, $currentPage)) {
    // User does not have access - redirect to dashboard with error
    $_SESSION['access_denied'] = true;
    $_SESSION['access_denied_page'] = $currentPage;
    header('Location: index.php?page=dashboard&error=access_denied');
    exit;
}

// User has access - continue loading page
?>