<?php
/**
 * Role-Based Access Control (RBAC) Configuration
 * Defines all roles and their permissions
 * 
 * @package LabManagementSystem
 * @version 1.0
 */

class RolePermissions
{
    // ========== ROLE DEFINITIONS ==========
    const ADMIN = 'Admin';
    const LAB_MANAGER = 'LabManager';
    const LAB_TECHNICIAN = 'LabTechnician';
    const RECEPTIONIST = 'Receptionist';
    const CLIENT = 'Client';

    // ========== PERMISSION DEFINITIONS ==========
    private static $permissions = [
        'Admin' => [
            'dashboard' => true,
            'clients' => true,
            'users' => true,
            'sample-submission' => true,
            'form-info' => true,
            'form-acceptance' => true,
            'form-acknowledgement' => true,
            'form-analyst' => true,
            'test-assignment' => true,
            'test-results' => true,
            'test-status' => true,
            'manage-parameter' => true,
            'param-variants' => true,
            'swab-parameter' => true,
            'methods' => true,
            'pricing' => true,
            'samples' => true,
            'report-daily' => true,
            'report-client' => true,
            'report-revenue' => true,
            'report-turnaround' => true,
            'settings-general' => true,
            'settings-lab' => true,
            'settings-users' => true,
            'settings-backup' => true,
            'settings-notifications' => true,
            'manage-cities' => true,
            'manage-extra-items' => true
        ],

        'LabManager' => [
            'dashboard' => true,
            'clients' => true,
            'users' => false, // Cannot manage users
            'sample-submission' => true,
            'form-info' => true,
            'form-acceptance' => true,
            'form-acknowledgement' => true,
            'form-analyst' => true,
            'test-assignment' => true,
            'test-results' => true,
            'test-status' => true,
            'manage-parameter' => true,
            'param-variants' => true,
            'swab-parameter' => true,
            'methods' => true,
            'pricing' => true,
            'samples' => true,
            'report-daily' => true,
            'report-client' => true,
            'report-revenue' => true,
            'report-turnaround' => true,
            'settings-general' => true,
            'settings-lab' => true,
            'settings-users' => false, // Cannot manage permissions
            'settings-backup' => false,
            'settings-notifications' => true,
            'manage-cities' => true,
            'manage-extra-items' => true
        ],

        'LabTechnician' => [
            'dashboard' => true,
            'clients' => true, // Can view
            'users' => false,
            'sample-submission' => true,
            'form-info' => true,
            'form-acceptance' => true,
            'form-acknowledgement' => true,
            'form-analyst' => true,
            'test-assignment' => false, // Cannot assign
            'test-results' => true, // Can enter results
            'test-status' => true,
            'manage-parameter' => false,
            'param-variants' => false,
            'swab-parameter' => false,
            'methods' => false, // Can view
            'pricing' => false,
            'samples' => true,
            'report-daily' => true,
            'report-client' => false,
            'report-revenue' => false,
            'report-turnaround' => true,
            'settings-general' => false,
            'settings-lab' => false,
            'settings-users' => false,
            'settings-backup' => false,
            'settings-notifications' => false,
            'manage-cities' => true,
            'manage-extra-items' => true
        ],

        'Receptionist' => [
            'dashboard' => true,
            'clients' => true, // Main job
            'users' => false,
            'sample-submission' => true, // Main job
            'form-info' => true,
            'form-acceptance' => true,
            'form-acknowledgement' => true,
            'form-analyst' => false,
            'test-assignment' => false,
            'test-results' => false,
            'test-status' => true, // Can check status
            'manage-parameter' => false,
            'param-variants' => false,
            'swab-parameter' => false,
            'methods' => false,
            'pricing' => true, // Can view pricing
            'samples' => true, // Can view
            'report-daily' => true,
            'report-client' => true,
            'report-revenue' => false,
            'report-turnaround' => false,
            'settings-general' => false,
            'settings-lab' => false,
            'settings-users' => false,
            'settings-backup' => false,
            'settings-notifications' => false,
            'manage-cities' => false,
            'manage-extra-items' => false
        ],

        'Client' => [
            'dashboard' => true, // Limited dashboard
            'clients' => false,
            'users' => false,
            'sample-submission' => true, // Can submit own samples
            'form-info' => false,
            'form-acceptance' => false,
            'form-acknowledgement' => false,
            'form-analyst' => false,
            'test-assignment' => false,
            'test-results' => false,
            'test-status' => true, // Can check own samples
            'manage-parameter' => false,
            'param-variants' => false,
            'swab-parameter' => false,
            'methods' => false,
            'pricing' => true, // Can view pricing
            'samples' => true, // Can view own samples only
            'report-daily' => false,
            'report-client' => false,
            'report-revenue' => false,
            'report-turnaround' => false,
            'settings-general' => false,
            'settings-lab' => false,
            'settings-users' => false,
            'settings-backup' => false,
            'settings-notifications' => false,
            'manage-cities' => false,
            'manage-extra-items' => false
        ]
    ];

    /**
     * Check if a role has permission to access a page
     */
    public static function hasPermission($role, $page)
    {
        // Admin always has access
        if ($role === self::ADMIN) {
            return true;
        }

        // Check if role exists
        if (!isset(self::$permissions[$role])) {
            return false;
        }

        // Check if page permission exists
        if (!isset(self::$permissions[$role][$page])) {
            return false;
        }

        return self::$permissions[$role][$page];
    }

    /**
     * Get all accessible pages for a role
     */
    public static function getAccessiblePages($role)
    {
        if (!isset(self::$permissions[$role])) {
            return [];
        }

        $accessible = [];
        foreach (self::$permissions[$role] as $page => $hasAccess) {
            if ($hasAccess) {
                $accessible[] = $page;
            }
        }

        return $accessible;
    }

    /**
     * Get all roles
     */
    public static function getAllRoles()
    {
        return [
            self::ADMIN,
            self::LAB_MANAGER,
            self::LAB_TECHNICIAN,
            self::RECEPTIONIST,
            self::CLIENT
        ];
    }
}
?>