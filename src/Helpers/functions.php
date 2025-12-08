<?php
/**
 * Helper Functions for Laboratory Management System
 * 
 * @package LabManagementSystem
 * @subpackage Helpers
 * @version 1.0
 */

/**
 * Generate Form Number with Sample Count
 * Format: YY/NNNN/CC (e.g., 25/0001/03)
 * 
 * @param mysqli $conn Database connection
 * @param int $sampleCount Number of samples in this submission
 * @return array ['success' => bool, 'form_number' => string, 'message' => string]
 */
function generateFormNumber($conn, $sampleCount) {
    try {
        // Get current year (last 2 digits)
        $year = (int)date('Y');
        $yearShort = (int)date('y'); // 2025 -> 25
        
        // Start transaction for atomic operation
        $conn->begin_transaction();
        
        // Lock the row for update
        $sql = "SELECT current_number FROM form_sequence WHERE year = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // First form of this year
            $insertSql = "INSERT INTO form_sequence (year, current_number) VALUES (?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("i", $year);
            $insertStmt->execute();
            $sequenceNumber = 1;
        } else {
            // Increment sequence
            $row = $result->fetch_assoc();
            $sequenceNumber = $row['current_number'] + 1;
            
            $updateSql = "UPDATE form_sequence SET current_number = ? WHERE year = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $sequenceNumber, $year);
            $updateStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Format: YY/NNNN/CC
        $baseFormNumber = sprintf("%02d/%04d", $yearShort, $sequenceNumber);
        $fullFormNumber = sprintf("%s/%02d", $baseFormNumber, $sampleCount);
        
        return [
            'success' => true,
            'base_form_number' => $baseFormNumber, // 25/0001
            'form_number' => $fullFormNumber,      // 25/0001/03
            'sequence' => $sequenceNumber,
            'year' => $yearShort,
            'sample_count' => $sampleCount
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return [
            'success' => false,
            'message' => 'Failed to generate form number: ' . $e->getMessage()
        ];
    }
}

/**
 * Generate individual sample codes for each sample
 * Format: YY/NNNN/01, YY/NNNN/02, etc.
 * 
 * @param string $baseFormNumber Base form number (e.g., 25/0001)
 * @param int $sampleCount Total number of samples
 * @return array Array of sample codes
 */
function generateSampleCodes($baseFormNumber, $sampleCount) {
    $sampleCodes = [];
    for ($i = 1; $i <= $sampleCount; $i++) {
        $sampleCodes[] = sprintf("%s/%02d", $baseFormNumber, $i);
    }
    return $sampleCodes;
}

/**
 * Format currency for display
 * 
 * @param float $amount Amount to format
 * @param bool $includeSymbol Include Rs. symbol
 * @return string Formatted currency
 */
function formatCurrency($amount, $includeSymbol = true) {
    $formatted = number_format($amount, 2, '.', ',');
    return $includeSymbol ? "Rs. " . $formatted : $formatted;
}

/**
 * Sanitize user input
 * 
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    if (is_string($data)) {
        // Remove whitespace
        $data = trim($data);
        // Remove backslashes
        $data = stripslashes($data);
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Validate payment reference format
 * 
 * @param string $reference Payment reference to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePaymentReference($reference) {
    if (empty($reference)) {
        return ['valid' => false, 'message' => 'Payment reference is required'];
    }
    
    // Remove whitespace
    $reference = trim($reference);
    
    // Must be at least 3 characters
    if (strlen($reference) < 3) {
        return ['valid' => false, 'message' => 'Payment reference must be at least 3 characters'];
    }
    
    // Must not exceed 100 characters
    if (strlen($reference) > 100) {
        return ['valid' => false, 'message' => 'Payment reference too long (max 100 characters)'];
    }
    
    // Allow alphanumeric, hyphens, slashes, and spaces
    if (!preg_match('/^[a-zA-Z0-9\-\/\s]+$/', $reference)) {
        return ['valid' => false, 'message' => 'Payment reference contains invalid characters'];
    }
    
    return ['valid' => true, 'message' => 'Valid'];
}

/**
 * Calculate total charge for selected tests
 * 
 * @param array $tests Array of test data with 'charge' field
 * @return float Total charge
 */
function calculateTestTotal($tests) {
    $total = 0.00;
    
    foreach ($tests as $test) {
        $charge = isset($test['charge']) ? floatval($test['charge']) : 0.00;
        $total += $charge;
    }
    
    return $total;
}

/**
 * Get default test method for a parameter
 * 
 * @param mysqli $conn Database connection
 * @param int $parameterId Parameter ID
 * @return int|null Method ID or null if not found
 */
function getDefaultMethod($conn, $parameterId) {
    try {
        $sql = "SELECT method_id FROM parameter_methods 
                WHERE parameter_id = ? AND is_default = 1 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parameterId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['method_id'];
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Error getting default method: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate mobile number format (Sri Lankan)
 * 
 * @param string $mobile Mobile number to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validateMobile($mobile) {
    if (empty($mobile)) {
        return ['valid' => true, 'message' => 'Optional']; // Mobile is optional
    }
    
    // Remove spaces, hyphens, and parentheses
    $mobile = preg_replace('/[\s\-\(\)]/', '', $mobile);
    
    // Sri Lankan mobile format: 07XXXXXXXX (10 digits starting with 07)
    if (!preg_match('/^07[0-9]{8}$/', $mobile)) {
        return ['valid' => false, 'message' => 'Invalid mobile format (should be 07XXXXXXXX)'];
    }
    
    return ['valid' => true, 'message' => 'Valid'];
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validateEmail($email) {
    if (empty($email)) {
        return ['valid' => true, 'message' => 'Optional']; // Email is optional
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Invalid email format'];
    }
    
    return ['valid' => true, 'message' => 'Valid'];
}

/**
 * Log error to file
 * 
 * @param string $message Error message
 * @param string $context Context/location of error
 * @return void
 */
function logError($message, $context = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] ";
    
    if (!empty($context)) {
        $logMessage .= "[{$context}] ";
    }
    
    $logMessage .= $message . PHP_EOL;
    
    // Log to PHP error log
    error_log($logMessage);
}

/**
 * Generate a unique sample code (alternative method if needed)
 * 
 * @param mysqli $conn Database connection
 * @return string Unique sample code
 */
function generateUniqueSampleCode($conn) {
    $year = date('y');
    $timestamp = date('His');
    $random = mt_rand(100, 999);
    
    return sprintf("%s%s%s", $year, $timestamp, $random);
}

/**
 * Check if a payment reference already exists
 * 
 * @param mysqli $conn Database connection
 * @param string $reference Payment reference to check
 * @param int|null $excludeSampleId Sample ID to exclude from check (for updates)
 * @return bool True if exists, false otherwise
 */
function paymentReferenceExists($conn, $reference, $excludeSampleId = null) {
    try {
        if ($excludeSampleId) {
            $sql = "SELECT sample_id FROM samples 
                    WHERE payment_reference = ? AND sample_id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $reference, $excludeSampleId);
        } else {
            $sql = "SELECT sample_id FROM samples 
                    WHERE payment_reference = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $reference);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
        
    } catch (Exception $e) {
        logError("Error checking payment reference: " . $e->getMessage(), "paymentReferenceExists");
        return false;
    }
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Output format (default: d/m/Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y') {
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Calculate grand total (test charges + additional charges)
 * 
 * @param float $testCharges Test charges total
 * @param float $additionalCharges Additional charges
 * @return float Grand total
 */
function calculateGrandTotal($testCharges, $additionalCharges = 0.00) {
    return floatval($testCharges) + floatval($additionalCharges);
}

/**
 * Validate received date (today or past 5 days only)
 * 
 * @param string $date Date to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validateReceivedDate($date) {
    try {
        $receivedDate = new DateTime($date);
        $today = new DateTime();
        $fiveDaysAgo = (new DateTime())->modify('-5 days');
        
        // Reset time to compare only dates
        $receivedDate->setTime(0, 0, 0);
        $today->setTime(0, 0, 0);
        $fiveDaysAgo->setTime(0, 0, 0);
        
        if ($receivedDate > $today) {
            return ['valid' => false, 'message' => 'Received date cannot be in the future'];
        }
        
        if ($receivedDate < $fiveDaysAgo) {
            return ['valid' => false, 'message' => 'Received date cannot be more than 5 days in the past'];
        }
        
        return ['valid' => true, 'message' => 'Valid'];
        
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Invalid date format'];
    }
}

/**
 * Validate tentative date (today or future only)
 * 
 * @param string $date Date to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validateTentativeDate($date) {
    try {
        $tentativeDate = new DateTime($date);
        $today = new DateTime();
        
        // Reset time to compare only dates
        $tentativeDate->setTime(0, 0, 0);
        $today->setTime(0, 0, 0);
        
        if ($tentativeDate < $today) {
            return ['valid' => false, 'message' => 'Tentative date cannot be in the past'];
        }
        
        return ['valid' => true, 'message' => 'Valid'];
        
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Invalid date format'];
    }
}

/**
 * Send JSON response and exit
 * 
 * @param array $data Data to send
 * @return void
 */
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>