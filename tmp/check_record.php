<?php
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/Config/Database.php';
require_once 'c:/Users/KNJ/OneDrive/Desktop/nara_lab_management_webapp/src/Models/InvoiceModel.php';

$invoiceModel = new InvoiceModel();
$db = new Database();
$conn = $db->connect();

$search = 'QC/26/013/007';

// 1. Find sample by code
$stmt = $conn->prepare("SELECT sample_id, client_id, sample_code FROM samples WHERE sample_code = ? OR sample_code LIKE ?");
$likeSearch = '%' . $search . '%';
$stmt->bind_param('ss', $search, $likeSearch);
$stmt->execute();
$sample = $stmt->get_result()->fetch_assoc();

if (!$sample) {
    echo "RECORD NOT FOUND: $search\n";
    exit;
}

$sampleId = $sample['sample_id'];
echo "FOUND SAMPLE ID: $sampleId for CODE: " . $sample['sample_code'] . "\n";

// 2. Get Invoice Data using model
$data = $invoiceModel->getInvoiceRawData($sampleId);

if ($data) {
    echo "TOTAL PRICE: " . $data['total_payable'] . "\n";
    echo "CUSTOMER: " . $data['customer']['name'] . "\n";
    echo "DATE OF REQUEST: " . $data['date_of_request'] . "\n";
    echo "\nBREAKDOWN:\n";
    foreach ($data['rows'] as $row) {
        echo "- " . $row['sample_type'] . " | Qty: " . $row['quantity'] . " | Unit: " . $row['unit_price'] . " | Sub: " . $row['sub_total'] . "\n";
        foreach ($row['parameters'] as $p) {
            echo "  * " . $p['name'] . " (" . $p['fee'] . ")\n";
        }
    }

    if (!empty($data['extra_items'])) {
        echo "\nEXTRA ITEMS:\n";
        foreach ($data['extra_items'] as $ex) {
            echo "- " . $ex['name'] . " | Qty: " . $ex['quantity'] . " | Unit: " . $ex['unit_price'] . " | Total: " . $ex['line_total'] . "\n";
        }
    }
} else {
    echo "ERROR FETCHING INVOICE DATA\n";
}
