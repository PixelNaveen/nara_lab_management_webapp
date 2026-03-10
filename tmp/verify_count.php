<?php
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/src/Models/InvoiceModel.php';

$invoiceModel = new InvoiceModel();
$data = $invoiceModel->getInvoiceRawData(13);

echo "Verification for QC/26/013:\n";
echo "Total Payable: " . $data['total_payable'] . "\n";
echo "Total Items Count (Samples Only): " . $data['total_items_count'] . "\n";

foreach ($data['rows'] as $row) {
    echo "Row: " . $row['sample_type'] . " | Qty: " . $row['quantity'] . "\n";
}

echo "Extra Items count: " . count($data['extra_items']) . "\n";
foreach ($data['extra_items'] as $extra) {
    echo "Extra: " . $extra['name'] . " | Qty: " . $extra['quantity'] . "\n";
}
