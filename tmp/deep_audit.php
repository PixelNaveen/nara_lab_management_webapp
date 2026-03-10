<?php
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';

$db = new Database();
$conn = $db->connect();

echo "====================================================================\n";
echo "  DEEP PRICE AUDIT FOR SUBMISSION QC/26/013 (sample_id=13)\n";
echo "====================================================================\n\n";

// 1. Sample record - key fields only
echo "--- SAMPLE RECORD ---\n";
$res = $conn->query("SELECT * FROM samples WHERE sample_id = 13");
$sample = $res->fetch_assoc();
if ($sample) {
    foreach ($sample as $key => $val) {
        echo sprintf("  %-30s %s\n", $key, $val ?? 'NULL');
    }
}

// 2. Invoice snapshot
echo "\n--- INVOICE RECORD ---\n";
$res = $conn->query("SELECT invoice_id, sample_id, invoice_number, report_number, created_at FROM invoices WHERE sample_id = 13");
$inv = $res->fetch_assoc();
if ($inv) {
    foreach ($inv as $k => $v) echo "  $k: $v\n";

    // Get snapshot total
    $res2 = $conn->query("SELECT invoice_data_snapshot FROM invoices WHERE sample_id = 13");
    $snapRow = $res2->fetch_assoc();
    if ($snapRow && $snapRow['invoice_data_snapshot']) {
        $snap = json_decode($snapRow['invoice_data_snapshot'], true);
        echo "  SNAPSHOT total_payable: " . ($snap['total_payable'] ?? 'N/A') . "\n";
        if (isset($snap['rows'])) {
            foreach ($snap['rows'] as $row) {
                echo "    - " . $row['sample_type'] . " | Qty:" . $row['quantity'] . " | Unit:" . $row['unit_price'] . " | Sub:" . $row['sub_total'] . "\n";
            }
        }
        if (!empty($snap['extra_items'])) {
            foreach ($snap['extra_items'] as $ex) {
                echo "    - EXTRA: " . $ex['name'] . " | Qty:" . $ex['quantity'] . " | Total:" . $ex['line_total'] . "\n";
            }
        }
    }
} else {
    echo "  NO INVOICE FOUND\n";
}

// 3. All sample items
echo "\n--- ALL SAMPLE_ITEMS ---\n";
$res = $conn->query("SELECT sample_item_id, sample_name FROM sample_items WHERE sample_id = 13");
$items = [];
while ($r = $res->fetch_assoc()) {
    $items[] = $r;
    echo "  item_id=" . $r['sample_item_id'] . " | name=" . $r['sample_name'] . "\n";
}

// 4. All tests with charges - raw sum
echo "\n--- ALL SAMPLE_TESTS (RAW CHARGES) ---\n";
$sql = "SELECT 
    st.sample_test_id, st.sample_item_id, st.parameter_id, st.variant_id, st.combo_id,
    st.charge, st.is_swab,
    tp.parameter_name, tp.short_name,
    pv.variant_name,
    pc.combo_name,
    si.sample_name
FROM sample_tests st
INNER JOIN sample_items si ON st.sample_item_id = si.sample_item_id
INNER JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
LEFT JOIN parameter_variants pv ON st.variant_id = pv.variant_id
LEFT JOIN parameter_combinations pc ON st.combo_id = pc.combo_id
WHERE si.sample_id = 13
ORDER BY si.sample_name, st.sample_item_id, st.sample_test_id";

$res = $conn->query($sql);
$totalRawCharges = 0;
$prevItem = '';
$itemCharges = [];
while ($r = $res->fetch_assoc()) {
    $itemLabel = $r['sample_name'] . " (item=" . $r['sample_item_id'] . ")";
    if ($itemLabel != $prevItem) {
        echo "\n  >> $itemLabel\n";
        $prevItem = $itemLabel;
    }
    $paramLabel = $r['parameter_name'];
    if ($r['variant_name']) $paramLabel .= ' ' . $r['variant_name'];
    if ($r['combo_name']) $paramLabel .= ' [COMBO: ' . $r['combo_name'] . ']';
    $charge = floatval($r['charge']);
    echo sprintf(
        "     test=%d  %-50s charge=%.2f  combo=%s\n",
        $r['sample_test_id'],
        $paramLabel,
        $charge,
        $r['combo_id'] ?? 'NULL'
    );
    $totalRawCharges += $charge;

    $itemId = $r['sample_item_id'];
    if (!isset($itemCharges[$itemId])) $itemCharges[$itemId] = 0;
    $itemCharges[$itemId] += $charge;
}

echo "\n  RAW SUM of ALL individual test charges: $totalRawCharges\n";

echo "\n--- PER-ITEM CHARGE SUMS ---\n";
foreach ($itemCharges as $iid => $total) {
    echo "  item_id=$iid  raw_charge_sum=$total\n";
}

// 5. Extra items
echo "\n--- EXTRA ITEMS ---\n";
$res = $conn->query("SELECT sei.quantity, sei.unit_price, sei.line_total, ei.item_name FROM sample_extra_items sei INNER JOIN extra_items ei ON sei.item_id = ei.item_id WHERE sei.sample_id = 13");
$extraSum = 0;
while ($r = $res->fetch_assoc()) {
    echo "  " . $r['item_name'] . " | qty=" . $r['quantity'] . " | unit=" . $r['unit_price'] . " | total=" . $r['line_total'] . "\n";
    $extraSum += floatval($r['line_total']);
}
echo "  Extra total: $extraSum\n";

// 6. Combo pricing table check
echo "\n--- COMBO PRICING TABLE ---\n";
$res = $conn->query("SELECT pc.combo_id, pc.combo_name, cp.test_charge FROM parameter_combinations pc LEFT JOIN combination_pricing cp ON pc.combo_id = cp.combo_id AND cp.is_active = 1 AND cp.is_deleted = 0 WHERE pc.combo_id IN (SELECT DISTINCT combo_id FROM sample_tests st INNER JOIN sample_items si ON st.sample_item_id = si.sample_item_id WHERE si.sample_id = 13 AND st.combo_id IS NOT NULL)");
while ($r = $res->fetch_assoc()) {
    echo "  combo_id=" . $r['combo_id'] . " | name=" . $r['combo_name'] . " | pricing_charge=" . ($r['test_charge'] ?? 'NULL') . "\n";
}

// 7. Summary
echo "\n====================================================================\n";
echo "  SUMMARY\n";
echo "  Raw test charges sum: $totalRawCharges\n";
echo "  Extra items sum:      $extraSum\n";
echo "  Raw grand total:      " . ($totalRawCharges + $extraSum) . "\n";
echo "====================================================================\n";
