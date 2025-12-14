<?php

/**
 * Helper Functions - FIXED VERSION 3.1
 * 
 * CRITICAL FIX: Form sequence only increments on successful commit
 * 
 * Changes:
 * 1. generateFormNumber() now part of main transaction
 * 2. Sequence increments ONLY if submission succeeds
 * 3. Payment reference uses NULL instead of empty string
 */

/**
 * Generate Form Number WITHIN TRANSACTION
 * CRITICAL: This must be called INSIDE the main transaction
 * Do NOT commit here - let the calling function handle commit
 * 
 * @param mysqli $conn Database connection (with active transaction)
 * @param int $sampleCount Number of samples in this submission
 * @return array ['success' => bool, 'form_number' => string, 'base_number' => string]
 */
function generateFormNumber($conn, $sampleCount)
{
    try {
        $year = (int)date('Y');
        $yearShort = (int)date('y'); // 2025 -> 25

        // Lock the row for update (within existing transaction)
        $sql = "SELECT current_number FROM form_sequence WHERE year = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // First form of this year
            $insertSql = "INSERT INTO form_sequence (year, current_number) VALUES (?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) {
                throw new Exception("Insert prepare failed: " . $conn->error);
            }
            $insertStmt->bind_param("i", $year);
            $insertStmt->execute();
            $sequenceNumber = 1;
        } else {
            // Increment sequence
            $row = $result->fetch_assoc();
            $sequenceNumber = $row['current_number'] + 1;

            $updateSql = "UPDATE form_sequence SET current_number = ? WHERE year = ?";
            $updateStmt = $conn->prepare($updateSql);
            if (!$updateStmt) {
                throw new Exception("Update prepare failed: " . $conn->error);
            }
            $updateStmt->bind_param("ii", $sequenceNumber, $year);
            $updateStmt->execute();
        }

        // Format: YY/NNNN/CC
        $baseNumber = sprintf("%02d/%04d", $yearShort, $sequenceNumber);
        $fullFormNumber = sprintf("%s/%02d", $baseNumber, $sampleCount);

        return [
            'success' => true,
            'base_number' => $baseNumber,      // 25/0001
            'form_number' => $fullFormNumber,  // 25/0001/03
            'sequence' => $sequenceNumber,
            'year' => $yearShort,
            'sample_count' => $sampleCount
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'generateFormNumber');
        return [
            'success' => false,
            'message' => 'Failed to generate form number: ' . $e->getMessage()
        ];
    }
}

/**
 * Generate AC Reference from Form Number
 * Adds 'AC/' prefix to form number for acceptance and acknowledgement forms
 * 
 * @param string $formNumber Form number (e.g., "25/0001/03")
 * @return string AC reference (e.g., "AC/25/0001/03")
 */
function generateACReference($formNumber)
{
    return 'AC/' . $formNumber;
}

/**
 * Get Default Test Method for Parameter
 * Returns the default method_id, or first available if no default set
 * 
 * @param mysqli $conn Database connection
 * @param int $parameterId Parameter ID
 * @return int|null Method ID or null if not found
 */
function getDefaultMethod($conn, $parameterId)
{
    try {
        // Try to get default method
        $sql = "SELECT method_id FROM parameter_methods 
                WHERE parameter_id = ? AND is_default = 1 
                ORDER BY sequence_order ASC LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $parameterId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return (int)$row['method_id'];
        }

        // If no default found, get first available method
        $fallbackSql = "SELECT method_id FROM parameter_methods 
                        WHERE parameter_id = ? 
                        ORDER BY sequence_order ASC LIMIT 1";
        $fallbackStmt = $conn->prepare($fallbackSql);
        if (!$fallbackStmt) {
            return null;
        }

        $fallbackStmt->bind_param("i", $parameterId);
        $fallbackStmt->execute();
        $fallbackResult = $fallbackStmt->get_result();

        if ($fallbackRow = $fallbackResult->fetch_assoc()) {
            return (int)$fallbackRow['method_id'];
        }

        return null;
    } catch (Exception $e) {
        logError($e->getMessage(), 'getDefaultMethod');
        return null;
    }
}

/**
 * Validate Payment Reference Uniqueness
 * Checks if payment reference already exists in samples table
 * 
 * @param mysqli $conn Database connection
 * @param string $reference Payment reference to check
 * @param int|null $excludeSampleId Exclude this sample_id from check (for updates)
 * @return bool True if unique (available), false if already exists
 */
function isPaymentReferenceUnique($conn, $reference, $excludeSampleId = null)
{
    try {
        if (empty($reference)) {
            return true; // Empty references are allowed (not paid)
        }

        $sql = "SELECT COUNT(*) as count FROM samples WHERE payment_reference = ?";
        $params = [$reference];
        $types = "s";

        if ($excludeSampleId !== null) {
            $sql .= " AND sample_id != ?";
            $params[] = $excludeSampleId;
            $types .= "i";
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['count'] == 0;
    } catch (Exception $e) {
        logError($e->getMessage(), 'isPaymentReferenceUnique');
        return false;
    }
}

/**
 * Format currency with Rs. prefix
 * 
 * @param float $amount Amount to format
 * @return string Formatted currency (e.g., "Rs. 1,250.00")
 */
function formatCurrency($amount)
{
    return 'Rs. ' . number_format((float)$amount, 2);
}

/**
 * Sanitize input data recursively
 * Handles strings, arrays, and nested arrays
 * 
 * @param mixed $input Input to sanitize
 * @return mixed Sanitized input
 */
function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }

    if (!is_string($input)) {
        return $input;
    }

    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate phone number format
 * First digit must be 0, followed by 9 digits
 * 
 * @param string $phone Phone number
 * @return bool Valid or not
 */
function validatePhone($phone)
{
    // Remove spaces and dashes
    $cleaned = preg_replace('/[\s-]/', '', $phone);
    // Must be 10 digits starting with 0
    return preg_match('/^0\d{9}$/', $cleaned);
}

/**
 * Validate email format
 * 
 * @param string $email Email address
 * @return bool Valid or not
 */
function validateEmail($email)
{
    if (empty($email)) {
        return true; // Optional field
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate received date (today or past 5 days only)
 * 
 * @param string $date Date to validate (YYYY-MM-DD)
 * @return array ['valid' => bool, 'message' => string]
 */
function validateReceivedDate($date)
{
    try {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Received date is required'];
        }

        $receivedDate = new DateTime($date);
        $today = new DateTime();
        $fiveDaysAgo = (clone $today)->modify('-5 days');

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
 * @param string $date Date to validate (YYYY-MM-DD)
 * @return array ['valid' => bool, 'message' => string]
 */
function validateTentativeDate($date)
{
    try {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Tentative date is required'];
        }

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
 * Calculate grand total (test charges + additional charges)
 * 
 * @param float $testCharges Test charges total
 * @param float $additionalCharges Additional charges
 * @return float Grand total
 */
function calculateGrandTotal($testCharges, $additionalCharges = 0.00)
{
    return (float)$testCharges + (float)$additionalCharges;
}

/**
 * Detect combos from selected tests
 * Returns array of detected combos with their charges
 * Prioritizes larger combos (more parameters) first
 * 
 * @param array $tests Array of test objects with parameter_id
 * @param mysqli $conn Database connection
 * @param string $submissionType 'regular' or 'swab'
 * @return array Detected combos with pricing
 */
function detectCombos($tests, $conn, $submissionType = 'regular')
{
    try {
        // Extract unique parameter IDs from tests
        $parameterIds = array_unique(array_column($tests, 'parameter_id'));

        if (count($parameterIds) < 2) {
            return []; // Need at least 2 parameters for a combo
        }

        // Get all active combos with their parameters
        $sql = "SELECT 
                    pc.combo_id,
                    pc.combo_name,
                    cp.test_charge AS combo_price,
                    GROUP_CONCAT(ci.parameter_id ORDER BY ci.parameter_id) AS param_ids,
                    COUNT(ci.parameter_id) AS param_count
                FROM parameter_combinations pc
                JOIN combination_pricing cp ON pc.combo_id = cp.combo_id
                JOIN combination_items ci ON pc.combo_id = ci.combo_id
                WHERE pc.is_active = 1 
                  AND pc.is_deleted = 0
                  AND cp.is_active = 1
                  AND cp.is_deleted = 0
                GROUP BY pc.combo_id
                ORDER BY param_count DESC"; // Prioritize larger combos

        $result = $conn->query($sql);
        if (!$result) {
            logError("Combo detection query failed: " . $conn->error, 'detectCombos');
            return [];
        }

        $detectedCombos = [];

        while ($combo = $result->fetch_assoc()) {
            $comboParams = explode(',', $combo['param_ids']);

            // Check if all combo parameters are in selected tests
            $matchesAll = true;
            foreach ($comboParams as $comboParam) {
                if (!in_array($comboParam, $parameterIds)) {
                    $matchesAll = false;
                    break;
                }
            }

            if ($matchesAll) {
                // For SWAB mode: verify all parameters are swab_enabled
                if ($submissionType === 'swab') {
                    $placeholders = implode(',', array_fill(0, count($comboParams), '?'));
                    $swabCheckSql = "SELECT COUNT(*) as count 
                                     FROM test_parameters 
                                     WHERE parameter_id IN ($placeholders) 
                                     AND swab_enabled = 1";

                    $swabStmt = $conn->prepare($swabCheckSql);
                    if ($swabStmt) {
                        $types = str_repeat('i', count($comboParams));
                        $swabStmt->bind_param($types, ...$comboParams);
                        $swabStmt->execute();
                        $swabResult = $swabStmt->get_result();
                        $swabRow = $swabResult->fetch_assoc();

                        if ($swabRow['count'] != count($comboParams)) {
                            continue; // Not all parameters are swab-enabled, skip this combo
                        }

                        // Add SWAB surcharge (Rs. 375 × number of swab-enabled params)
                        $swabSurcharge = 375.00 * count($comboParams);
                        $combo['combo_price'] = (float)$combo['combo_price'] + $swabSurcharge;
                    }
                }

                $detectedCombos[] = [
                    'combo_id' => (int)$combo['combo_id'],
                    'combo_name' => $combo['combo_name'],
                    'parameter_ids' => $comboParams,
                    'combo_price' => (float)$combo['combo_price'],
                    'param_count' => (int)$combo['param_count']
                ];
            }
        }

        return $detectedCombos;
    } catch (Exception $e) {
        logError($e->getMessage(), 'detectCombos');
        return [];
    }
}

/**
 * Log error to file
 * Creates logs directory if it doesn't exist
 * 
 * @param string $message Error message
 * @param string $context Context where error occurred
 * @return void
 */
function logError($message, $context = '')
{
    $logDir = __DIR__ . '/../../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $contextStr = $context ? "[$context] " : '';
    $log = "[{$timestamp}] {$contextStr}{$message}" . PHP_EOL;

    file_put_contents($logDir . '/error.log', $log, FILE_APPEND);
}

/**
 * Send JSON response and exit
 * Sets proper headers and exits script
 * 
 * @param array $data Data to send as JSON
 * @return void
 */
function sendJsonResponse($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Format date for display
 * 
 * @param string $date Date string (YYYY-MM-DD)
 * @param string $format Output format (default: d/m/Y)
 * @return string Formatted date or 'N/A' if empty
 */
function formatDate($date, $format = 'd/m/Y')
{
    try {
        if (empty($date)) {
            return 'N/A';
        }
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Validate payment reference format
 * Basic validation for payment reference string
 * 
 * @param string $reference Payment reference
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePaymentReference($reference)
{
    if (empty($reference)) {
        return ['valid' => false, 'message' => 'Payment reference is required'];
    }

    $reference = trim($reference);

    if (strlen($reference) < 3) {
        return ['valid' => false, 'message' => 'Payment reference must be at least 3 characters'];
    }

    if (strlen($reference) > 100) {
        return ['valid' => false, 'message' => 'Payment reference too long (max 100 characters)'];
    }

    // Allow alphanumeric, hyphens, slashes, and spaces
    if (!preg_match('/^[a-zA-Z0-9\-\/\s]+$/', $reference)) {
        return ['valid' => false, 'message' => 'Payment reference contains invalid characters'];
    }

    return ['valid' => true, 'message' => 'Valid'];
}
