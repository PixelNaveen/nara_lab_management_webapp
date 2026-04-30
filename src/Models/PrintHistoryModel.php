<?php
/**
 * Print History Model
 * Silently tracks all form printing actions
 * NO UI DISPLAY - Backend logging only
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class PrintHistoryModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Log a print action (silent - no user feedback)
     * 
     * @param int $sampleId Sample ID
     * @param string $formType SAF|ACKNOWLEDGEMENT|ANALYST
     * @param string $printedBy User fullname from session
     * @param int $userId User ID from session
     * @param string $printFormat PDF or PRINT
     * @param array $formsIncluded Array of form types included
     * @return bool Success
     */
    public function logPrint($sampleId, $formType, $printedBy, $userId, $printFormat = 'PDF', $formsIncluded = [])
    {
        try {
            $formsString = !empty($formsIncluded) ? implode(',', $formsIncluded) : $formType;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $sql = "INSERT INTO print_history 
                    (sample_id, form_type, printed_by, printed_by_user_id, 
                     print_format, forms_included, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ississss",
                $sampleId,
                $formType,
                $printedBy,
                $userId,
                $printFormat,
                $formsString,
                $ipAddress,
                $userAgent
            );

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Print History Log Error: " . $e->getMessage());
            return false; // Silent fail - don't break user experience
        }
    }

    /**
     * Get print count for a sample (optional - for future reporting)
     * 
     * @param int $sampleId Sample ID
     * @return int Print count
     */
    public function getPrintCount($sampleId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM print_history WHERE sample_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return intval($row['count']);
        } catch (Exception $e) {
            error_log("Get Print Count Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get last print info for a sample (for "Issued By" display on form)
     * 
     * @param int $sampleId Sample ID
     * @param string $formType Form type
     * @return array|null [printed_by, printed_at] or null
     */
    public function getLastPrintInfo($sampleId, $formType)
    {
        try {
            $sql = "SELECT printed_by, printed_at 
                    FROM print_history 
                    WHERE sample_id = ? AND form_type = ?
                    ORDER BY printed_at DESC 
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $sampleId, $formType);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }

            return null;
        } catch (Exception $e) {
            error_log("Get Last Print Info Error: " . $e->getMessage());
            return null;
        }
    }
}