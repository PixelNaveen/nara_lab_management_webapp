<?php

/**
 * Helper Functions - COMPLETE ENHANCED VERSION
 * Version: 6.0 FINAL - Production Ready
 * 
 * ENHANCEMENTS:
 * - Greedy algorithm for combo detection (prevents overlaps)
 * - Dynamic swab pricing (database-driven per parameter)
 * - Case-insensitive sample name matching
 * - Smart capitalization suggestions
 * - Future-proof for new combos and variable pricing
 */

/**
 * ============================================================================
 * CORE UTILITY FUNCTIONS
 * ============================================================================
 */

/**
 * Generate Form Number WITHIN TRANSACTION
 */
function generateFormNumber($conn, $sampleCount)
{
    try {
        $year = (int)date('Y');
        $yearShort = (int)date('y');

        $sql = "SELECT current_number FROM form_sequence WHERE year = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $insertSql = "INSERT INTO form_sequence (year, current_number) VALUES (?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) {
                throw new Exception("Insert prepare failed: " . $conn->error);
            }
            $insertStmt->bind_param("i", $year);
            $insertStmt->execute();
            $sequenceNumber = 1;
        } else {
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

        $baseNumber = sprintf("%02d/%03d", $yearShort, $sequenceNumber);
        $fullFormNumber = sprintf("%s/%03d", $baseNumber, $sampleCount);

        return [
            'success' => true,
            'base_number' => $baseNumber,
            'form_number' => $fullFormNumber,
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
 * Generate Next Invoice Number (QC/M/YY/NNN)
 * Resets every year. Uses dedicated invoice_sequence table.
 */
function getNextInvoiceNumber($conn)
{
    /**
     * NOTE: This function should be called within a transaction by the caller
     * (e.g., InvoiceModel) to ensure zero-gap numbering and consistency.
     */
    try {
        $year = (int)date('Y');
        $yearShort = date('y');

        // 1. Get and Lock the current sequence row
        $sql = "SELECT current_number FROM invoice_sequence WHERE year = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // New year: start at 1
            $sequenceNumber = 1;
            $insertSql = "INSERT INTO invoice_sequence (year, current_number) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) throw new Exception("Insert prepare failed: " . $conn->error);
            $insertStmt->bind_param("ii", $year, $sequenceNumber);
            $insertStmt->execute();
        } else {
            // Normal increment
            $row = $result->fetch_assoc();
            $sequenceNumber = (int)$row['current_number'] + 1;
            $updateSql = "UPDATE invoice_sequence SET current_number = ? WHERE year = ?";
            $updateStmt = $conn->prepare($updateSql);
            if (!$updateStmt) throw new Exception("Update prepare failed: " . $conn->error);
            $updateStmt->bind_param("ii", $sequenceNumber, $year);
            $updateStmt->execute();
        }

        return sprintf("QC/M/%s/%03d", $yearShort, $sequenceNumber);
    } catch (Exception $e) {
        logError($e->getMessage(), 'getNextInvoiceNumber');
        throw $e; // Rethrow to trigger rollback in the caller
    }
}

function generateQCReference($formNumber)
{
    return 'QC/' . $formNumber;
}

/**
 * Format parameter name with scientific italics
 * 
 * Uses the display_format column from test_parameters to determine
 * if a parameter name should be displayed in italics (scientific nomenclature).
 * 
 * Handles compound names like "Salmonella spp." by italicizing only
 * the scientific genus/species portion (before "spp." etc.)
 * 
 * @param string $name Parameter name
 * @param string $displayFormat 'scientific' or 'normal'
 * @return string HTML-formatted name (with <em> if scientific)
 */
function formatScientificName($name, $displayFormat = 'normal')
{
    // Escape for safety first
    $safeName = htmlspecialchars($name);

    if ($displayFormat !== 'scientific') {
        return $safeName;
    }

    // Check for variant suffixes in parentheses: "E. coli (Variant1, Variant2)"
    // We only italicize the main name, not the parenthetical variants
    if (preg_match('/^(.+?)(\s*\(.+\))$/', $safeName, $matches)) {
        return '<em>' . $matches[1] . '</em>' . $matches[2];
    }

    // Entire name is scientific — wrap it all in <em>
    return '<em>' . $safeName . '</em>';
}

/**
 * Format parameter name for high-precision forms (AIF, SAcF)
 * Handles scientific italics AND conditional highlighting of variants.
 * 
 * Logic: If a variant is selected (e.g., APC at 30°C), it highlights
 * the base name AND that specific variant inside the parentheses.
 */
function formatParameterWithHighlighting($name, $displayFormat = 'normal', $isSelected = false, $selectedVariants = [])
{
    if (!$isSelected) {
        return formatScientificName($name, $displayFormat);
    }

    // Ensure selectedVariants is an array (backward compatibility)
    if (!is_array($selectedVariants)) {
        $selectedVariants = $selectedVariants ? [(string)$selectedVariants] : [];
    }

    // If it has variants in parentheses: "Aerobic Plate Count (at 22°C, at 30°C, at 37°C)"
    if (preg_match('/^(.+?)\s*\((.+?)\)$/', $name, $matches)) {
        $baseName = $matches[1];
        $variantsStr = $matches[2];
        $variants = explode(', ', $variantsStr);

        $safeBase = htmlspecialchars($baseName);
        $formattedBase = ($displayFormat === 'scientific') ? "<em>$safeBase</em>" : $safeBase;

        // Highlight base name (bold + underline)
        $output = "<strong><u>$formattedBase</u></strong> (";

        // Process variants
        $highlightedVariants = [];
        foreach ($variants as $v) {
            $trimmedV = trim($v);
            $isVarSelected = false;

            // Check if this specific variant is in our selected list
            foreach ($selectedVariants as $sv) {
                if ($trimmedV === trim($sv)) {
                    $isVarSelected = true;
                    break;
                }
            }

            if ($isVarSelected) {
                $highlightedVariants[] = '<span style="display: inline-block; white-space: nowrap;"><strong><u>' . htmlspecialchars($trimmedV) . '</u></strong></span>';
            } else {
                $highlightedVariants[] = '<span style="display: inline-block; white-space: nowrap;">' . htmlspecialchars($trimmedV) . '</span>';
            }
        }

        $output .= implode(', ', $highlightedVariants) . ")";
        return $output;
    }

    // No variants - highlight the whole thing
    $formattedName = formatScientificName($name, $displayFormat);
    return "<strong><u>$formattedName</u></strong>";
}

/**
 * Alias for backward compatibility (Deprecated)
 */
function formatAnalystParameter($name, $displayFormat = 'normal', $isSelected = false, $selectedVariants = [])
{
    return formatParameterWithHighlighting($name, $displayFormat, $isSelected, $selectedVariants);
}

function getDefaultMethod($conn, $parameterId)
{
    try {
        // Try OLD table first (parameter_methods) - direct assignment
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

        // Fallback: any method from old table (non-default)
        $fallbackSql = "SELECT method_id FROM parameter_methods 
                        WHERE parameter_id = ? 
                        ORDER BY sequence_order ASC LIMIT 1";
        $fallbackStmt = $conn->prepare($fallbackSql);
        if ($fallbackStmt) {
            $fallbackStmt->bind_param("i", $parameterId);
            $fallbackStmt->execute();
            $fallbackResult = $fallbackStmt->get_result();
            if ($fallbackRow = $fallbackResult->fetch_assoc()) {
                return (int)$fallbackRow['method_id'];
            }
        }

        // Fallback: NEW table (parameter_category_methods via base_unit_config)
        $newSql = "SELECT pcm.method_id 
                   FROM parameter_base_unit_config pbc
                   INNER JOIN parameter_category_methods pcm ON pbc.config_id = pcm.config_id
                   WHERE pbc.parameter_id = ? AND pbc.is_active = 1 AND pcm.is_primary = 1
                   ORDER BY pcm.sequence_order ASC LIMIT 1";
        $newStmt = $conn->prepare($newSql);
        if ($newStmt) {
            $newStmt->bind_param("i", $parameterId);
            $newStmt->execute();
            $newResult = $newStmt->get_result();
            if ($newRow = $newResult->fetch_assoc()) {
                return (int)$newRow['method_id'];
            }
        }

        // Last fallback: any method from new table
        $lastSql = "SELECT pcm.method_id 
                    FROM parameter_base_unit_config pbc
                    INNER JOIN parameter_category_methods pcm ON pbc.config_id = pcm.config_id
                    WHERE pbc.parameter_id = ? AND pbc.is_active = 1
                    ORDER BY pcm.sequence_order ASC LIMIT 1";
        $lastStmt = $conn->prepare($lastSql);
        if ($lastStmt) {
            $lastStmt->bind_param("i", $parameterId);
            $lastStmt->execute();
            $lastResult = $lastStmt->get_result();
            if ($lastRow = $lastResult->fetch_assoc()) {
                return (int)$lastRow['method_id'];
            }
        }

        return null;
    } catch (Exception $e) {
        logError($e->getMessage(), 'getDefaultMethod');
        return null;
    }
}

function isPaymentReferenceUnique($conn, $reference, $excludeSampleId = null)
{
    try {
        if (empty($reference)) {
            return true;
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

function formatCurrency($amount)
{
    return 'Rs. ' . number_format((float)$amount, 2);
}

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

function validatePhone($phone)
{
    $cleaned = preg_replace('/[\s-]/', '', $phone);
    return preg_match('/^0\d{9}$/', $cleaned);
}

function validateEmail($email)
{
    if (empty($email)) {
        return true;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateReceivedDate($date)
{
    try {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Received date is required'];
        }

        $tz = new DateTimeZone('Asia/Colombo');
        $receivedDate = new DateTime($date, $tz);
        $today = new DateTime('now', $tz);
        $thirtyDaysAgo = clone $today;
        $thirtyDaysAgo->modify('-30 days');

        $receivedDate->setTime(0, 0, 0);
        $today->setTime(0, 0, 0);
        $thirtyDaysAgo->setTime(0, 0, 0);

        if ($receivedDate > $today) {
            return ['valid' => false, 'message' => 'Received date cannot be in the future'];
        }

        if ($receivedDate < $thirtyDaysAgo) {
            return ['valid' => false, 'message' => 'Received date cannot be more than 30 days in the past'];
        }

        return ['valid' => true, 'message' => 'Valid'];
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Invalid date format'];
    }
}

function validateTentativeDate($date)
{
    try {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Tentative date is required'];
        }

        $tz = new DateTimeZone('Asia/Colombo');
        $tentativeDate = new DateTime($date, $tz);
        $today = new DateTime('now', $tz);

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

function calculateGrandTotal($testCharges, $additionalCharges = 0.00)
{
    return (float)$testCharges + (float)$additionalCharges;
}

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

    if (!preg_match('/^[a-zA-Z0-9\-\/\s]+$/', $reference)) {
        return ['valid' => false, 'message' => 'Payment reference contains invalid characters'];
    }

    return ['valid' => true, 'message' => 'Valid'];
}

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

function sendJsonResponse($data)
{
    // Discard any buffered output (warnings, notices, etc.)
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

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
 * ============================================================================
 * ENHANCED SAMPLE NAME FUNCTIONS
 * Version: 2.0 - Case-Insensitive Auto-Save with Smart Capitalization
 * ============================================================================
 */

/**
 * Normalize sample name for consistency
 * 
 * Rules:
 * 1. Trim whitespace
 * 2. Convert to Title Case
 * 3. Remove extra spaces
 * 4. Handle special cases (pH, RNA, DNA, etc.)
 * 
 * @param string $name Sample name input
 * @return string Normalized sample name
 */
function normalizeSampleName($name)
{
    // Trim and remove extra spaces
    $name = trim(preg_replace('/\s+/', ' ', $name));

    if (empty($name)) {
        return '';
    }

    // Convert to Title Case for consistency
    $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

    // Handle special cases (acronyms and scientific terms)
    $specialCases = [
        'Ph ' => 'pH ',
        ' Ph' => ' pH',
        'Ph$' => 'pH',
        'Rna' => 'RNA',
        'Dna' => 'DNA',
        'Bod' => 'BOD',
        'Cod' => 'COD',
        'Ec' => 'EC',
        'Tds' => 'TDS',
        'Tss' => 'TSS',
        'Do' => 'DO',
        'Pcr' => 'PCR',
        'Elisa' => 'ELISA',
        'Hplc' => 'HPLC',
        'Gcms' => 'GCMS'
    ];

    foreach ($specialCases as $from => $to) {
        $name = preg_replace('/\b' . preg_quote($from, '/') . '\b/i', $to, $name);
    }

    return $name;
}

/**
 * Check if sample name exists (case-insensitive)
 * Returns the existing capitalization if found
 * 
 * @param mysqli $conn Database connection
 * @param string $name Sample name to check
 * @return array ['exists' => bool, 'canonical_name' => string|null, 'usage_count' => int]
 */
function getSampleNameCanonical($conn, $name)
{
    try {
        $sql = "SELECT sample_name, usage_count
                FROM sample_names 
                WHERE LOWER(sample_name) = LOWER(?)
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [
                'exists' => false,
                'canonical_name' => null,
                'usage_count' => 0
            ];
        }

        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return [
                'exists' => true,
                'canonical_name' => $row['sample_name'],
                'usage_count' => (int)$row['usage_count']
            ];
        }

        return [
            'exists' => false,
            'canonical_name' => null,
            'usage_count' => 0
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'getSampleNameCanonical');
        return [
            'exists' => false,
            'canonical_name' => null,
            'usage_count' => 0
        ];
    }
}

/**
 * Get popular sample names for quick selection
 * 
 * @param mysqli $conn Database connection
 * @param int $limit Number of names to return
 * @return array Sample names sorted by usage
 */
function getPopularSampleNames($conn, $limit = 10)
{
    try {
        $sql = "SELECT sample_name, usage_count
                FROM sample_names 
                ORDER BY usage_count DESC, sample_name ASC 
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row;
        }

        return $names;
    } catch (Exception $e) {
        logError($e->getMessage(), 'getPopularSampleNames');
        return [];
    }
}

/**
 * ============================================================================
 * COMBO DETECTION & PRICING FUNCTIONS
 * Version: 7.0 - With Greedy Algorithm & Dynamic Swab Pricing
 * ============================================================================
 */

/**
 * Detect combos using GREEDY ALGORITHM with DYNAMIC SWAB PRICING
 * 
 * This function implements a greedy algorithm to detect the best (largest)
 * matching combos from user's selected tests, preventing overlap.
 * 
 * NEW FEATURE: Dynamic swab pricing per parameter
 * - Each parameter can have its own swab price in the database
 * - Combo swab surcharge = SUM of individual parameter swab prices
 * - No hard-coded values - fully database-driven
 * 
 * Algorithm:
 * 1. Fetch all combos sorted by parameter count (largest first)
 * 2. For each combo, check if ALL its parameters are in user selection
 * 3. Check if ANY of those parameters are already used in a previous combo
 * 4. If swab submission, calculate TOTAL swab surcharge from database
 * 5. If valid, add the combo and mark parameters as used
 * 6. Continue until all combos are processed
 * 
 * This ensures:
 * - Largest matching combo is always selected first
 * - No overlapping combos (e.g., 2-param won't apply if 3-param already did)
 * - Accurate swab pricing per parameter
 * - Future-proof: works automatically with new combos and price changes
 * 
 * @param array $tests Array of selected tests with parameter_id
 * @param mysqli $conn Database connection
 * @param string $submissionType 'regular' or 'swab'
 * @return array Array of detected combos with pricing info
 */
function detectCombos($tests, $conn, $submissionType = 'regular')
{
    try {
        // Extract and sort parameter IDs from selected tests
        $parameterIds = array_unique(array_column($tests, 'parameter_id'));
        sort($parameterIds); // Sort for consistent comparison

        // Need at least 2 parameters for a combo
        if (count($parameterIds) < 2) {
            return [];
        }

        // Fetch all active combos, sorted by parameter count DESCENDING
        // This ensures we process larger combos first (greedy algorithm)
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
                ORDER BY param_count DESC, pc.combo_id ASC";

        $result = $conn->query($sql);
        if (!$result) {
            logError("Combo detection query failed: " . $conn->error, 'detectCombos');
            return [];
        }

        $detectedCombos = [];
        $usedParameters = []; // Track which parameters are already in a combo

        // Process each combo (largest first due to ORDER BY)
        while ($combo = $result->fetch_assoc()) {
            // Get combo's parameter IDs as sorted array
            $comboParams = array_map('intval', explode(',', $combo['param_ids']));
            sort($comboParams); // Ensure sorted for consistent comparison

            // ============================================================
            // CRITICAL CHECK 1: Do ALL combo parameters exist in selection?
            // ============================================================
            $allParamsPresent = true;
            foreach ($comboParams as $comboParam) {
                if (!in_array($comboParam, $parameterIds)) {
                    $allParamsPresent = false;
                    break;
                }
            }

            // If not all parameters are selected, skip this combo
            if (!$allParamsPresent) {
                continue;
            }

            // ============================================================
            // CRITICAL CHECK 2: Are ANY parameters already used?
            // ============================================================
            // This prevents overlapping combos (e.g., if 3-param combo [1,2,3]
            // is already detected, we won't also detect 2-param combo [1,2])
            $hasConflict = false;
            foreach ($comboParams as $comboParam) {
                if (in_array($comboParam, $usedParameters)) {
                    $hasConflict = true;
                    break;
                }
            }

            // If any parameter is already used, skip this combo
            if ($hasConflict) {
                continue;
            }

            // ============================================================
            // ENHANCED: DYNAMIC SWAB VALIDATION AND PRICING
            // ============================================================
            if ($submissionType === 'swab') {
                // Verify ALL combo parameters are swab-enabled AND get their swab prices
                $placeholders = implode(',', array_fill(0, count($comboParams), '?'));

                // ENHANCED QUERY: Get both count AND sum of swab prices
                $swabCheckSql = "SELECT 
                                    COUNT(*) as swab_enabled_count,
                                    SUM(COALESCE(sp.swab_price, 0)) as total_swab_price
                                 FROM test_parameters tp
                                 LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id
                                    AND sp.is_active = 1 AND sp.is_deleted = 0
                                 WHERE tp.parameter_id IN ($placeholders) 
                                    AND tp.swab_enabled = 1";

                $swabStmt = $conn->prepare($swabCheckSql);
                if ($swabStmt) {
                    $types = str_repeat('i', count($comboParams));
                    $swabStmt->bind_param($types, ...$comboParams);
                    $swabStmt->execute();
                    $swabResult = $swabStmt->get_result();
                    $swabRow = $swabResult->fetch_assoc();

                    // All parameters must be swab-enabled
                    if ($swabRow['swab_enabled_count'] != count($comboParams)) {
                        continue; // Skip this combo - not all params are swab-enabled
                    }

                    // DYNAMIC SWAB SURCHARGE: Sum of individual parameter swab prices
                    // This replaces the hard-coded "375.00 * count($comboParams)"
                    $swabSurcharge = (float)$swabRow['total_swab_price'];

                    // Add the calculated surcharge to combo price
                    $combo['combo_price'] = (float)$combo['combo_price'] + $swabSurcharge;
                }
            }

            // ============================================================
            // ✅ COMBO IS VALID - Add it to detected list
            // ============================================================
            $detectedCombos[] = [
                'combo_id' => (int)$combo['combo_id'],
                'combo_name' => $combo['combo_name'],
                'parameter_ids' => $comboParams,
                'combo_price' => (float)$combo['combo_price'],
                'param_count' => (int)$combo['param_count']
            ];

            // Mark all these parameters as USED
            // This prevents smaller combos with overlapping parameters from being detected
            foreach ($comboParams as $paramId) {
                $usedParameters[] = $paramId;
            }
        }

        return $detectedCombos;
    } catch (Exception $e) {
        logError($e->getMessage(), 'detectCombos');
        return [];
    }
}

// ============================================================
// SWAB SUBMISSION DEDICATED FUNCTIONS
// These functions are ONLY used when submissionType === 'swab'
// They do NOT affect Regular Submission logic in any way
// ============================================================

/**
 * Detect which selected parameters belong to Swab Combo Groups (PARTIAL MATCH)
 *
 * Rules:
 * - Checks swab_combos table (NOT parameter_combinations)
 * - Even ONE selected parameter from a combo group triggers the fixed bundle fee
 * - Uses Greedy: largest combo group is matched first
 * - A parameter can only belong to ONE detected group (no double charging)
 * - Parameters not matched to any group are returned as "standalone"
 *
 * @param array $parameterIds List of selected parameter IDs
 * @param mysqli $conn Database connection
 * @return array ['groups' => [...detected combos...], 'standalones' => [...param IDs...]]
 */
function detectSwabGroups($parameterIds, $conn)
{
    try {
        if (empty($parameterIds)) {
            return ['groups' => [], 'standalones' => []];
        }

        // Fetch all active swab combos, largest first (greedy)
        $sql = "SELECT sc.combo_id, sc.combo_name, sc.price AS combo_price,
                       GROUP_CONCAT(sci.param_id ORDER BY sci.param_id) AS param_ids,
                       COUNT(sci.param_id) AS param_count
                FROM swab_combos sc
                JOIN swab_combo_items sci ON sc.combo_id = sci.combo_id
                WHERE sc.is_active = 1 AND sc.is_deleted = 0
                GROUP BY sc.combo_id
                ORDER BY param_count DESC, sc.combo_id ASC";

        $result = $conn->query($sql);
        if (!$result) {
            logError("detectSwabGroups query failed: " . $conn->error, 'detectSwabGroups');
            return ['groups' => [], 'standalones' => $parameterIds];
        }

        $detectedGroups = [];
        $claimedParamIds = []; // Parameters already assigned to a combo group

        while ($row = $result->fetch_assoc()) {
            $comboParamIds = array_map('intval', explode(',', $row['param_ids']));

            // Find which of the user's selected params are in this combo group
            $matchingParams = array_values(array_intersect($parameterIds, $comboParamIds));

            // Skip if NONE of the user's selected params belong to this group
            if (empty($matchingParams)) {
                continue;
            }

            // Remove params already claimed by a previously detected (larger) group
            $availableParams = array_values(array_diff($matchingParams, $claimedParamIds));

            // If after removing claimed ones, nothing left — skip
            if (empty($availableParams)) {
                continue;
            }

            // Register this group as detected
            $detectedGroups[] = [
                'combo_id'     => (int)$row['combo_id'],
                'combo_name'   => $row['combo_name'],
                'combo_price'  => (float)$row['combo_price'], // Fixed surcharge for this group
                'matched_ids'  => $availableParams,           // The params from this group that were selected
            ];

            // Mark these params as claimed
            foreach ($availableParams as $pid) {
                $claimedParamIds[] = $pid;
            }
        }

        // Everything NOT claimed by a group is standalone
        $standaloneIds = array_values(array_diff($parameterIds, $claimedParamIds));

        return [
            'groups'     => $detectedGroups,
            'standalones' => $standaloneIds,
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'detectSwabGroups');
        return ['groups' => [], 'standalones' => $parameterIds];
    }
}

/**
 * Calculate charges for a SWAB Submission using two-layer pricing:
 *
 * Layer 1 — Test Charge: Always sum the individual test price for EVERY selected parameter.
 * Layer 2 — Swab Surcharge:
 *   - If params belong to a Swab Combo Group → add fixed combo_price ONCE for the whole group.
 *   - If params are standalone → add their individual swab_param.swab_price each.
 *
 * @param array $sampleTests Tests for ONE sample item
 * @param mysqli $conn Database connection
 * @return array ['test_total', 'swab_total', 'grand_total', 'groups', 'standalones', 'tests_tagged']
 */
function calculateSwabCharges($sampleTests, $conn)
{
    try {
        $parameterIds = array_unique(array_map('intval', array_column($sampleTests, 'parameter_id')));

        // Build a quick lookup: parameter_id => charge (test price)
        $testPriceLookup = [];
        foreach ($sampleTests as $test) {
            $testPriceLookup[(int)$test['parameter_id']] = (float)$test['charge'];
        }

        // === LAYER 1: Sum ALL individual test prices ===
        $testTotal = array_sum(array_values($testPriceLookup));

        // === LAYER 2: Detect swab combo groups (partial match) ===
        $groupResult  = detectSwabGroups($parameterIds, $conn);
        $groups       = $groupResult['groups'];
        $standaloneIds = $groupResult['standalones'];

        $swabTotal = 0.00;

        // Add fixed combo fee once per detected group
        foreach ($groups as $group) {
            $swabTotal += $group['combo_price'];
        }

        // Add individual swab_param price for each standalone parameter
        if (!empty($standaloneIds)) {
            $placeholders = implode(',', array_fill(0, count($standaloneIds), '?'));
            $swabSql = "SELECT sp.param_id, sp.swab_price
                        FROM swab_param sp
                        WHERE sp.param_id IN ($placeholders)
                          AND sp.is_active = 1
                          AND sp.is_deleted = 0";

            $stmt = $conn->prepare($swabSql);
            if ($stmt) {
                $types = str_repeat('i', count($standaloneIds));
                $stmt->bind_param($types, ...$standaloneIds);
                $stmt->execute();
                $swabResult = $stmt->get_result();
                while ($row = $swabResult->fetch_assoc()) {
                    $swabTotal += (float)$row['swab_price'];
                }
                $stmt->close();
            }
        }

        // === Tag each test with its group or standalone info ===
        $groupParamMap = [];
        foreach ($groups as $group) {
            foreach ($group['matched_ids'] as $pid) {
                $groupParamMap[$pid] = $group;
            }
        }

        $testsTagged = [];
        foreach ($sampleTests as $test) {
            $pid = (int)$test['parameter_id'];
            $test['is_swab_combo'] = isset($groupParamMap[$pid]);
            $test['swab_combo_id'] = $groupParamMap[$pid]['combo_id'] ?? null;
            $test['swab_combo_name'] = $groupParamMap[$pid]['combo_name'] ?? null;
            $testsTagged[] = $test;
        }

        return [
            'test_total'  => round($testTotal, 2),
            'swab_total'  => round($swabTotal, 2),
            'grand_total' => round($testTotal + $swabTotal, 2),
            'groups'      => $groups,
            'standalones' => $standaloneIds,
            'tests_tagged' => $testsTagged,
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'calculateSwabCharges');
        return [
            'test_total'  => 0.00,
            'swab_total'  => 0.00,
            'grand_total' => 0.00,
            'groups'      => [],
            'standalones' => [],
            'tests_tagged' => $sampleTests,
        ];
    }
}

/**
 * Get swab price breakdown for transparency
 * 
 * This function returns detailed swab pricing for a combo, showing the
 * individual swab price for each parameter.
 * 
 * @param array $parameterIds Array of parameter IDs in the combo
 * @param mysqli $conn Database connection
 * @return array Array with total and per-parameter breakdown
 */
function getSwabPriceBreakdown($parameterIds, $conn)
{
    try {
        if (empty($parameterIds)) {
            return [
                'success' => false,
                'total' => 0.00,
                'breakdown' => []
            ];
        }

        $placeholders = implode(',', array_fill(0, count($parameterIds), '?'));
        $sql = "SELECT 
                    tp.parameter_id,
                    tp.parameter_name,
                    COALESCE(sp.swab_price, 0) as swab_price
                FROM test_parameters tp
                LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id
                    AND sp.is_active = 1 AND sp.is_deleted = 0
                WHERE tp.parameter_id IN ($placeholders)
                ORDER BY tp.parameter_id";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $types = str_repeat('i', count($parameterIds));
        $stmt->bind_param($types, ...$parameterIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $breakdown = [];
        $total = 0.00;

        while ($row = $result->fetch_assoc()) {
            $swabPrice = (float)$row['swab_price'];
            $breakdown[] = [
                'parameter_id' => (int)$row['parameter_id'],
                'parameter_name' => $row['parameter_name'],
                'swab_price' => $swabPrice
            ];
            $total += $swabPrice;
        }

        return [
            'success' => true,
            'total' => round($total, 2),
            'breakdown' => $breakdown,
            'parameter_count' => count($breakdown)
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'getSwabPriceBreakdown');
        return [
            'success' => false,
            'total' => 0.00,
            'breakdown' => [],
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Calculate test charges with combos and dynamic swab pricing.
 *
 * For REGULAR submissions: Uses detectCombos() with exact matching.
 *   - Combo items have their individual test prices REPLACED by the combo price.
 *   - Non-combo items are charged at individual test price.
 *
 * For SWAB submissions: Uses calculateSwabCharges() with TWO-LAYER pricing.
 *   - Layer 1: EVERY selected parameter is charged its individual TEST price.
 *   - Layer 2: Swab Surcharges are added on top:
 *       * If items belong to a Swab Combo Group → fixed combo_price added ONCE for whole group.
 *       * If items are standalone → their individual swab_param.swab_price added per item.
 */
function calculateTestChargesWithCombos($testsData, $conn, $submissionType = 'regular')
{
    try {
        // Group tests by sample index
        $testsBySample = [];
        foreach ($testsData as $test) {
            $sampleIndex = $test['sample'];
            if (!isset($testsBySample[$sampleIndex])) {
                $testsBySample[$sampleIndex] = [];
            }
            $testsBySample[$sampleIndex][] = $test;
        }

        // ================================================================
        // SWAB SUBMISSION BRANCH — Two-Layer Pricing
        // ================================================================
        if ($submissionType === 'swab') {
            $finalTotal        = 0.00;
            $allTestsWithCharges = [];
            $allCombosDetected  = [];

            foreach ($testsBySample as $sampleIndex => $sampleTests) {
                // Call the dedicated swab calculator for this sample
                $swabCalc = calculateSwabCharges($sampleTests, $conn);

                // Accumulate grand total
                $finalTotal += $swabCalc['grand_total'];

                // Collect tagged tests
                foreach ($swabCalc['tests_tagged'] as $taggedTest) {
                    $taggedTest['sample'] = $sampleIndex;
                    $allTestsWithCharges[] = $taggedTest;
                }

                // Collect detected swab combo groups for reporting
                foreach ($swabCalc['groups'] as $group) {
                    $allCombosDetected[] = [
                        'sample'       => $sampleIndex,
                        'combo_id'     => $group['combo_id'],
                        'combo_name'   => $group['combo_name'],
                        'combo_price'  => $group['combo_price'],
                        'parameter_ids' => $group['matched_ids'],
                        'type'         => 'swab_combo',
                    ];
                }
            }

            return [
                'success'           => true,
                'tests_with_charges' => $allTestsWithCharges,
                'total'             => round($finalTotal, 2),
                'individual_total'  => round($finalTotal, 2), // No discounts in swab mode
                'combos_detected'   => $allCombosDetected,
                'combos_count'      => count($allCombosDetected),
                'savings'           => 0.00,
                'discount_percentage' => 0,
            ];
        }

        // ================================================================
        // REGULAR SUBMISSION BRANCH — Exact Match Combo Pricing (UNCHANGED)
        // ================================================================
        $finalTotal = 0.00;
        $individualGrandTotal = 0.00;
        $allTestsWithCharges = [];
        $allCombosDetected = [];
        $totalSavings = 0.00;

        // Process each sample
        foreach ($testsBySample as $sampleIndex => $sampleTests) {

            // Detect combos for this sample using ENHANCED greedy algorithm
            $detectedCombos = detectCombos($sampleTests, $conn, $submissionType);

            // Mark which tests are in combos
            $combosByParameterId = [];
            foreach ($detectedCombos as $combo) {
                // Store which combo each parameter belongs to
                foreach ($combo['parameter_ids'] as $paramId) {
                    $combosByParameterId[$paramId] = $combo;
                }

                // Add to overall list
                $allCombosDetected[] = [
                    'sample'        => $sampleIndex,
                    'combo_id'      => $combo['combo_id'],
                    'combo_name'    => $combo['combo_name'],
                    'combo_price'   => $combo['combo_price'],
                    'param_count'   => $combo['param_count'],
                    'parameter_ids' => $combo['parameter_ids'],
                ];
            }

            // Calculate totals for this sample
            $sampleIndividualTotal = 0.00;
            $sampleComboTotal = 0.00;

            if (!empty($detectedCombos)) {
                // Step 1: Add combo prices
                foreach ($detectedCombos as $combo) {
                    $sampleComboTotal += $combo['combo_price'];
                }

                // Step 2: Calculate individual total for ALL tests (for discount display)
                foreach ($sampleTests as $test) {
                    $sampleIndividualTotal += (float)$test['charge'];
                }

                // Step 3: Add NON-COMBO tests to the final total
                foreach ($sampleTests as $test) {
                    if (!isset($combosByParameterId[$test['parameter_id']])) {
                        // This test is NOT in a combo - add individual price
                        $sampleComboTotal += (float)$test['charge'];
                    }
                }

                // Add to grand totals
                $finalTotal += $sampleComboTotal;
                $individualGrandTotal += $sampleIndividualTotal;
                $totalSavings += ($sampleIndividualTotal - $sampleComboTotal);
            } else {
                // No combo - use individual prices for all tests
                foreach ($sampleTests as $test) {
                    $price = (float)$test['charge'];
                    $sampleIndividualTotal += $price;
                    $sampleComboTotal += $price;
                }

                $finalTotal += $sampleComboTotal;
                $individualGrandTotal += $sampleIndividualTotal;
            }

            // Tag each test with combo info
            foreach ($sampleTests as $test) {
                $test['individual_charge'] = (float)$test['charge'];
                $test['is_combo'] = isset($combosByParameterId[$test['parameter_id']]);
                $test['combo_id'] = $combosByParameterId[$test['parameter_id']]['combo_id'] ?? null;
                $test['combo_name'] = $combosByParameterId[$test['parameter_id']]['combo_name'] ?? null;

                $allTestsWithCharges[] = $test;
            }
        }

        return [
            'success' => true,
            'tests_with_charges' => $allTestsWithCharges,
            'total' => round($finalTotal, 2),
            'individual_total' => round($individualGrandTotal, 2),
            'combos_detected' => $allCombosDetected,
            'combos_count' => count($allCombosDetected),
            'savings' => round($totalSavings, 2),
            'discount_percentage' => $individualGrandTotal > 0 ?
                round(($totalSavings / $individualGrandTotal) * 100, 1) : 0
        ];
    } catch (Exception $e) {
        logError($e->getMessage(), 'calculateTestChargesWithCombos');
        return [
            'success' => false,
            'message' => 'Failed to calculate charges: ' . $e->getMessage(),
            'total' => 0.00
        ];
    }
}

/**
 * Get parameter price from database
 */
function getParameterPrice($conn, $parameterId, $includeSwab = false)
{
    try {
        $sql = "SELECT pp.test_charge, sp.swab_price
                FROM test_parameters tp
                LEFT JOIN parameter_pricing pp ON tp.parameter_id = pp.parameter_id
                    AND pp.is_active = 1 AND pp.is_deleted = 0
                LEFT JOIN swab_param sp ON tp.parameter_id = sp.param_id
                    AND sp.is_active = 1 AND sp.is_deleted = 0
                WHERE tp.parameter_id = ? AND tp.is_active = 1 AND tp.is_deleted = 0";

        $stmt = $conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $parameterId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $price = (float)$row['test_charge'];
            if ($includeSwab && $row['swab_price']) {
                $price += (float)$row['swab_price'];
            }
            return $price;
        }

        return null;
    } catch (Exception $e) {
        logError($e->getMessage(), 'getParameterPrice');
        return null;
    }
}
