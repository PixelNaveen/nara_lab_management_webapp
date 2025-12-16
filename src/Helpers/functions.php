<?php

/**
 * Helper Functions - COMPLETE WITH COMBO FIX
 * Version: 5.0 FINAL - Production Ready
 * 
 * COMBO PRICING FIX:
 * - Stores full combo price (not divided)
 * - Calculates individual → discount → combo totals
 * - Future-proof for new combos
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

        $baseNumber = sprintf("%02d/%04d", $yearShort, $sequenceNumber);
        $fullFormNumber = sprintf("%s/%02d", $baseNumber, $sampleCount);

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

function generateACReference($formNumber)
{
    return 'AC/' . $formNumber;
}

function getDefaultMethod($conn, $parameterId)
{
    try {
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

        $receivedDate = new DateTime($date);
        $today = new DateTime();
        $fiveDaysAgo = new DateTime('-5 days');

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

function validateTentativeDate($date)
{
    try {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Tentative date is required'];
        }

        $tentativeDate = new DateTime($date);
        $today = new DateTime();

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

/**
 * Detect combos from selected tests
 * Returns array of detected combos with their full pricing
 */
function detectCombos($tests, $conn, $submissionType = 'regular')
{
    try {
        $parameterIds = array_unique(array_column($tests, 'parameter_id'));

        if (count($parameterIds) < 2) {
            return [];
        }

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
                ORDER BY param_count DESC";

        $result = $conn->query($sql);
        if (!$result) {
            logError("Combo detection query failed: " . $conn->error, 'detectCombos');
            return [];
        }

        $detectedCombos = [];
        $usedParameters = [];  // CRITICAL FIX: Track used parameters

        while ($combo = $result->fetch_assoc()) {
            $comboParams = explode(',', $combo['param_ids']);

            // CRITICAL FIX: Check if ALL params match AND none are already used
            $matchesAll = true;
            $alreadyUsed = false;

            foreach ($comboParams as $comboParam) {
                if (!in_array($comboParam, $parameterIds)) {
                    $matchesAll = false;
                    break;
                }
                if (in_array($comboParam, $usedParameters)) {
                    $alreadyUsed = true;
                    break;
                }
            }

            // Skip if not all match OR if any parameter already used
            if (!$matchesAll || $alreadyUsed) {
                continue;
            }

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
                        continue;
                    }

                    $swabSurcharge = 375.00 * count($comboParams);
                    $combo['combo_price'] = (float)$combo['combo_price'] + $swabSurcharge;
                }
            }

            // Add combo to detected list
            $detectedCombos[] = [
                'combo_id' => (int)$combo['combo_id'],
                'combo_name' => $combo['combo_name'],
                'parameter_ids' => $comboParams,
                'combo_price' => (float)$combo['combo_price'],
                'param_count' => (int)$combo['param_count']
            ];

            // CRITICAL FIX: Mark these parameters as used
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

/**
 * ============================================================================
 * COMBO PRICING FIX - Calculate charges with FULL combo price
 * ============================================================================
 * 
 * This calculates:
 * 1. Individual total (sum of all individual prices)
 * 2. Combo total (full combo price, not divided)
 * 3. Discount amount (individual - combo)
 * 
 * For UI display: Individual → Discount → Combo Price
 */
function calculateTestChargesWithCombos($testsData, $conn, $submissionType = 'regular')
{
    try {
        // Group tests by sample
        $testsBySample = [];
        foreach ($testsData as $test) {
            $sampleIndex = $test['sample'];
            if (!isset($testsBySample[$sampleIndex])) {
                $testsBySample[$sampleIndex] = [];
            }
            $testsBySample[$sampleIndex][] = $test;
        }

        $finalTotal = 0.00;
        $individualGrandTotal = 0.00;
        $allTestsWithCharges = [];
        $allCombosDetected = [];
        $totalSavings = 0.00;

        // Process each sample
        foreach ($testsBySample as $sampleIndex => $sampleTests) {

            // Detect combos for this sample
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
                    'sample' => $sampleIndex,
                    'combo_id' => $combo['combo_id'],
                    'combo_name' => $combo['combo_name'],
                    'combo_price' => $combo['combo_price'],
                    'param_count' => $combo['param_count'],
                    'parameter_ids' => $combo['parameter_ids']
                ];
            }

            // Calculate totals for this sample
            $sampleIndividualTotal = 0.00;
            $sampleComboTotal = 0.00;

            // CRITICAL FIX: Handle BOTH combo and individual tests correctly
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
            'total' => round($finalTotal, 2),                          // Final price (with combos)
            'individual_total' => round($individualGrandTotal, 2),     // Original price
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

function sendJsonResponse($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
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
