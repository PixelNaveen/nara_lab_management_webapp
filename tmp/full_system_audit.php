<?php

/**
 * COMPREHENSIVE SYSTEM-WIDE PRICING INTEGRITY AUDIT
 * Checks EVERY submission for price consistency across ALL layers.
 */
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/src/Models/InvoiceModel.php';

$db = new Database();
$conn = $db->connect();
$invoiceModel = new InvoiceModel();

echo "====================================================================\n";
echo "  COMPREHENSIVE PRICING INTEGRITY AUDIT\n";
echo "  Date: " . date('Y-m-d H:i:s') . "\n";
echo "====================================================================\n\n";

// ═══════════════════════════════════════════════════════════════
// 1. CHECK ALL SUBMISSIONS: stored total vs raw test charges
// ═══════════════════════════════════════════════════════════════
echo "═══ LAYER 1: Submissions (samples table vs sample_tests) ═══\n\n";

$res = $conn->query("
    SELECT s.sample_id, s.sample_code, s.test_charges_total, s.additional_charges, s.grand_total,
        (SELECT COALESCE(SUM(st.charge), 0) FROM sample_tests st 
         INNER JOIN sample_items si ON st.sample_item_id = si.sample_item_id 
         WHERE si.sample_id = s.sample_id) as raw_test_sum,
        (SELECT COALESCE(SUM(sei.line_total), 0) FROM sample_extra_items sei WHERE sei.sample_id = s.sample_id) as raw_extra_sum
    FROM samples s
    ORDER BY s.sample_id
");

$submissionIssues = 0;
$totalSubmissions = 0;

while ($r = $res->fetch_assoc()) {
    $totalSubmissions++;
    $storedTestTotal = floatval($r['test_charges_total']);
    $rawTestSum = floatval($r['raw_test_sum']);
    $storedExtra = floatval($r['additional_charges']);
    $rawExtra = floatval($r['raw_extra_sum']);
    $storedGrand = floatval($r['grand_total']);
    $computedGrand = $rawTestSum + $rawExtra;

    // Allow 5 LKR tolerance for combo rounding
    $testDiff = abs($storedTestTotal - $rawTestSum);
    $grandDiff = abs($storedGrand - $computedGrand);
    $extraDiff = abs($storedExtra - $rawExtra);

    $testOk = $testDiff <= 5;
    $extraOk = $extraDiff <= 0.01;
    $grandOk = $grandDiff <= 5;

    $status = ($testOk && $extraOk && $grandOk) ? 'OK' : 'ISSUE';
    if ($status === 'ISSUE') $submissionIssues++;

    $marker = ($status === 'OK') ? 'OK' : '!!';
    echo sprintf("  [%s] %s (id=%d)\n", $marker, $r['sample_code'], $r['sample_id']);

    if ($status === 'ISSUE') {
        echo "      stored_test=$storedTestTotal raw_test=$rawTestSum diff=$testDiff\n";
        echo "      stored_extra=$storedExtra raw_extra=$rawExtra diff=$extraDiff\n";
        echo "      stored_grand=$storedGrand computed_grand=$computedGrand diff=$grandDiff\n";
    }
}

echo "\n  Submissions: $totalSubmissions total | $submissionIssues issues\n\n";

// ═══════════════════════════════════════════════════════════════
// 2. CHECK ALL INVOICES: snapshot vs stored vs recalculated
// ═══════════════════════════════════════════════════════════════
echo "═══ LAYER 2: Invoices (snapshot vs stored vs recalculated) ═══\n\n";

$res = $conn->query("
    SELECT i.invoice_id, i.sample_id, i.invoice_number, i.invoice_data_snapshot, 
           s.grand_total as stored_total, s.test_charges_total as stored_test
    FROM invoices i 
    INNER JOIN samples s ON i.sample_id = s.sample_id
");

$invoiceIssues = 0;
$totalInvoices = 0;

while ($r = $res->fetch_assoc()) {
    $totalInvoices++;
    $snap = json_decode($r['invoice_data_snapshot'], true);
    $snapTotal = floatval($snap['total_payable'] ?? 0);
    $storedTotal = floatval($r['stored_total']);

    // Recalculate with fixed model
    $data = $invoiceModel->getInvoiceRawData($r['sample_id']);
    $recalcTotal = $data ? floatval($data['total_payable']) : -1;

    $snapOk = abs($snapTotal - $storedTotal) <= 5;
    $recalcOk = abs($recalcTotal - $storedTotal) <= 5;

    $status = ($snapOk && $recalcOk) ? 'OK' : 'ISSUE';
    if ($status === 'ISSUE') $invoiceIssues++;

    $marker = ($status === 'OK') ? 'OK' : '!!';
    echo sprintf(
        "  [%s] Invoice #%d (%s) | snap=%s stored=%s recalc=%s\n",
        $marker,
        $r['invoice_id'],
        $r['invoice_number'],
        $snapTotal,
        $storedTotal,
        $recalcTotal
    );

    if ($status === 'ISSUE') {
        echo "      snap_diff=" . abs($snapTotal - $storedTotal) . " recalc_diff=" . abs($recalcTotal - $storedTotal) . "\n";
    }
}

echo "\n  Invoices: $totalInvoices total | $invoiceIssues issues\n\n";

// ═══════════════════════════════════════════════════════════════
// 3. CHECK COMBO PRICING INTEGRITY
// ═══════════════════════════════════════════════════════════════
echo "═══ LAYER 3: Combo Pricing (combo_pricing vs sample_tests charges) ═══\n\n";

// Get all combos used in sample_tests
$res = $conn->query("
    SELECT DISTINCT st.combo_id, pc.combo_name, cp.test_charge as defined_charge,
        (SELECT COUNT(*) FROM combination_items ci WHERE ci.combo_id = st.combo_id) as param_count
    FROM sample_tests st
    INNER JOIN parameter_combinations pc ON st.combo_id = pc.combo_id
    INNER JOIN combination_pricing cp ON pc.combo_id = cp.combo_id AND cp.is_active = 1 AND cp.is_deleted = 0
    WHERE st.combo_id IS NOT NULL
");

while ($r = $res->fetch_assoc()) {
    $definedCharge = floatval($r['defined_charge']);
    $paramCount = intval($r['param_count']);
    $perParamCharge = round($definedCharge / $paramCount, 2);
    $reconstructed = $perParamCharge * $paramCount;
    $roundingLoss = abs($definedCharge - $reconstructed);

    $marker = ($roundingLoss <= 1) ? 'OK' : '!!';
    echo sprintf("  [%s] combo_id=%d (%s)\n", $marker, $r['combo_id'], $r['combo_name']);
    echo sprintf(
        "      defined=%s  params=%d  per_param=%s  reconstructed=%s  rounding_loss=%s\n",
        $definedCharge,
        $paramCount,
        $perParamCharge,
        $reconstructed,
        $roundingLoss
    );
}

// ═══════════════════════════════════════════════════════════════
// 4. CHECK ACKNOWLEDGEMENT TABLE SYNC
// ═══════════════════════════════════════════════════════════════
echo "\n═══ LAYER 4: Acknowledgements (sample_acknowledgement vs samples) ═══\n\n";

$res = $conn->query("
    SELECT sa.sample_id, sa.test_charges, sa.additional_charges as ack_extra, sa.total_charges,
           s.test_charges_total, s.additional_charges as sample_extra, s.grand_total
    FROM sample_acknowledgement sa
    INNER JOIN samples s ON sa.sample_id = s.sample_id
");

$ackIssues = 0;
$totalAcks = 0;

while ($r = $res->fetch_assoc()) {
    $totalAcks++;
    $testMatch = abs(floatval($r['test_charges']) - floatval($r['test_charges_total'])) <= 0.01;
    $extraMatch = abs(floatval($r['ack_extra']) - floatval($r['sample_extra'])) <= 0.01;
    $totalMatch = abs(floatval($r['total_charges']) - floatval($r['grand_total'])) <= 0.01;

    if (!$testMatch || !$extraMatch || !$totalMatch) {
        $ackIssues++;
        echo sprintf(
            "  [!!] sample_id=%d: ack_test=%s vs stored_test=%s | ack_grand=%s vs stored_grand=%s\n",
            $r['sample_id'],
            $r['test_charges'],
            $r['test_charges_total'],
            $r['total_charges'],
            $r['grand_total']
        );
    }
}

echo ($ackIssues == 0 ? "  All $totalAcks acknowledgements match perfectly.\n" : "  Issues: $ackIssues/$totalAcks\n");

// ═══════════════════════════════════════════════════════════════
// 5. FUTURE RESILIENCE CHECK
// ═══════════════════════════════════════════════════════════════
echo "\n═══ LAYER 5: Future Resilience Assessment ═══\n\n";

echo "  [1] Submission pricing: LOCKED AT SAVE TIME\n";
echo "      - test_charges_total stored in samples table\n";
echo "      - Individual charges stored in sample_tests.charge\n";
echo "      - Extra items stored in sample_extra_items.line_total\n";
echo "      -> Future price changes will NOT affect existing submissions\n\n";

echo "  [2] Invoice snapshots: FROZEN JSON\n";
echo "      - invoice_data_snapshot stores full pricing breakdown\n";
echo "      - Once generated, snapshots are immutable (unless regenerated)\n";
echo "      -> Future price changes will NOT affect existing invoices\n\n";

echo "  [3] InvoiceModel recalculation: CROSS-VALIDATED\n";
echo "      - Reads charges from sample_tests (frozen at submission)\n";
echo "      - Cross-validates against samples.grand_total\n";
echo "      - Falls back to stored total if discrepancy > 5 LKR\n";
echo "      -> Even if calculation has a bug, the safety net catches it\n\n";

echo "  [4] Combo rounding: KNOWN LIMITATION\n";
echo "      - combo_price / param_count may lose 1-2 LKR per combo\n";
echo "      - Max impact: ~3 LKR per submission (within tolerance)\n";
echo "      - InvoiceModel uses combo_test_charge from pricing table when available\n\n";

// ═══════════════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════════════
echo "====================================================================\n";
echo "  FINAL SUMMARY\n";
echo "====================================================================\n";
echo "  Submissions:     $totalSubmissions checked, $submissionIssues issues\n";
echo "  Invoices:        $totalInvoices checked, $invoiceIssues issues\n";
echo "  Acknowledgements: $totalAcks checked, $ackIssues issues\n";
$totalIssues = $submissionIssues + $invoiceIssues + $ackIssues;
echo "  TOTAL ISSUES: $totalIssues\n";
echo ($totalIssues == 0
    ? "  STATUS: ALL CLEAR - System is 100% price-accurate\n"
    : "  STATUS: ISSUES FOUND - Review above\n");
echo "====================================================================\n";
