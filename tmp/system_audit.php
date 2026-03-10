<?php

/**
 * System-wide invoice audit: Check ALL invoices for price mismatches.
 */
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/src/Models/InvoiceModel.php';

$invoiceModel = new InvoiceModel();
$db = new Database();
$conn = $db->connect();

echo "====================================================================\n";
echo "  SYSTEM-WIDE INVOICE AUDIT\n";
echo "====================================================================\n\n";

// Get ALL invoices
$res = $conn->query("SELECT i.invoice_id, i.sample_id, i.invoice_number, i.invoice_data_snapshot, s.grand_total as stored_total FROM invoices i INNER JOIN samples s ON i.sample_id = s.sample_id");

$total = 0;
$mismatches = 0;

while ($row = $res->fetch_assoc()) {
    $total++;
    $snap = json_decode($row['invoice_data_snapshot'], true);
    $snapTotal = $snap['total_payable'] ?? 'N/A';
    $storedTotal = floatval($row['stored_total']);

    // Recalculate with fixed model
    $data = $invoiceModel->getInvoiceRawData($row['sample_id']);
    $calcTotal = $data ? $data['total_payable'] : 'ERROR';

    $match = (abs(floatval($snapTotal) - $storedTotal) <= 5) ? '✅' : '❌';
    if ($match === '❌') $mismatches++;

    echo sprintf(
        "  %s Invoice #%-3d (%s) | Snapshot: %-10s | Stored: %-10s | Recalc: %-10s\n",
        $match,
        $row['invoice_id'],
        $row['invoice_number'],
        $snapTotal,
        $storedTotal,
        $calcTotal
    );
}

echo "\n====================================================================\n";
echo "  Total invoices: $total | Mismatches: $mismatches\n";
echo "====================================================================\n";
