<?php
/**
 * General Helper Functions
 */

/**
 * Logs an error message to a file.
 * @param string $message The error message.
 * @param string $context The context where the error occurred.
 */
function logError($message, $context = '')
{
    $logDir = __DIR__ . '/../../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $log = date('Y-m-d H:i:s') . " [$context] $message" . PHP_EOL;
    file_put_contents($logDir . '/error.log', $log, FILE_APPEND);
}

/**
 * Validates a phone number format (e.g., 0771234567).
 * @param string $phone The phone number to validate.
 * @return bool True if valid, false otherwise.
 */
function validatePhone($phone)
{
    // Allows for optional spaces or dashes, but requires 10 digits starting with 0
    return preg_match('/^0\d{9}$/', preg_replace('/[\s-]/', '', $phone));
}

/**
 * Sanitizes user input to prevent XSS.
 * @param string|array $input The input to sanitize.
 * @return string|array The sanitized input.
 */
function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sends a JSON response and terminates the script.
 * @param array $data The data to encode as JSON.
 */
function sendJsonResponse($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}