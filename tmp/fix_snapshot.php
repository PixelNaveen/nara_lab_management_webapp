<?php

/**
 * Regenerate the frozen invoice snapshot for invoice #3 (QC/26/013)
 * to fix the incorrect total that was stored with the old buggy logic.
 */
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/src/Models/InvoiceModel.php';

$invoiceModel = new InvoiceModel();
$db = new Database();
$conn = $db->connect();

// Get the existing invoice
$invoice = $conn->query("SELECT * FROM invoices WHERE sample_id = 13")->fetch_assoc();

if (!$invoice) {
    echo "ERROR: No invoice found for sample_id=13\n";
    exit;
}

echo "Found invoice #" . $invoice['invoice_id'] . " (number: " . $invoice['invoice_number'] . ")\n";

// Get old snapshot total
$oldSnap = json_decode($invoice['invoice_data_snapshot'], true);
echo "OLD snapshot total_payable: " . ($oldSnap['total_payable'] ?? 'N/A') . "\n";

// Regenerate with fixed logic
$newData = $invoiceModel->getInvoiceRawData(13);
if (!$newData) {
    echo "ERROR: Could not generate new data\n";
    exit;
}

echo "NEW calculated total_payable: " . $newData['total_payable'] . "\n";

// Preserve the selected signatory from old snapshot
$newData['selected_signatory'] = $oldSnap['selected_signatory'] ?? null;
$newJson = json_encode($newData);

// Update the snapshot
$stmt = $conn->prepare("UPDATE invoices SET invoice_data_snapshot = ? WHERE invoice_id = ?");
$stmt->bind_param('si', $newJson, $invoice['invoice_id']);

if ($stmt->execute()) {
    echo "✅ SUCCESS: Invoice #" . $invoice['invoice_id'] . " snapshot UPDATED\n";
    echo "   Old total: " . ($oldSnap['total_payable'] ?? 'N/A') . "\n";
    echo "   New total: " . $newData['total_payable'] . "\n";
    echo "   Difference fixed: " . abs(($oldSnap['total_payable'] ?? 0) - $newData['total_payable']) . " LKR\n";
} else {
    echo "ERROR: Failed to update: " . $stmt->error . "\n";
}
