<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

require_once __DIR__ . '/../Models/InvoiceModel.php';
$model = new InvoiceModel();

$invoiceId = intval($_GET['invoice_id'] ?? 0);
$sampleId = intval($_GET['sample_id'] ?? 0);

$invoiceData = null;

if ($invoiceId > 0) {
    $saved = $model->getInvoiceById($invoiceId);
    if (!$saved) die("Invoice not found.");
    $invoiceData = json_decode($saved['invoice_data_snapshot'], true);
} elseif ($sampleId > 0) {
    $invoiceData = $model->getInvoiceRawData($sampleId);
    if (!$invoiceData) die("Sample data not found.");
    // For preview, provide generic dummy signatory data
    $invoiceData['selected_signatory'] = [
        'full_name' => 'Name',
        'title' => 'Title',
        'division' => 'Division'
    ];
} else {
    die("Invalid request. Missing invoice_id or sample_id.");
}

$pageTitle = htmlspecialchars($invoiceData['invoice_number'] ?? 'Invoice');
$printDate = date('F d, Y'); // Or exact Date object of invoice creation

// Helper to pad number of samples for old invoice snapshots
$totalSamples = 0;
foreach ($invoiceData['rows'] as $row) {
    $totalSamples += $row['quantity'] ?? 0;
}
if (!empty($invoiceData['extra_items'])) {
    foreach ($invoiceData['extra_items'] as $extraItem) {
        $totalSamples += $extraItem['quantity'] ?? 0;
    }
}

$sigName = $invoiceData['selected_signatory']['full_name'] ?? '';
$sigTitle = $invoiceData['selected_signatory']['title'] ?? '';
$sigDiv = $invoiceData['selected_signatory']['division'] ?? '';

// ─── DYNAMIC LAYOUT DENSITY CALCULATION ───
// Sum of parameters across all sample rows + extra items
$invoiceRowCount = 0;
foreach ($invoiceData['rows'] as $row) {
    if (isset($row['parameters']) && is_array($row['parameters'])) {
        $invoiceRowCount += count($row['parameters']);
    } else {
        $invoiceRowCount++;
    }
}
if (!empty($invoiceData['extra_items'])) {
    $invoiceRowCount += count($invoiceData['extra_items']);
}

$densityClass = 'layout-normal';
if ($invoiceRowCount <= 5) {
    $densityClass = 'layout-sparse';
} elseif ($invoiceRowCount <= 10) {
    $densityClass = 'layout-relaxed';
} elseif ($invoiceRowCount <= 16) {
    $densityClass = 'layout-normal';
} else {
    $densityClass = 'layout-compact';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NARA Invoice – <?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../../public/assets/css/invoice-print.css?v=<?php echo time(); ?>">
</head>

<body>

    <!-- Floating Print Button (Hidden in Print) -->
    <button class="floating-print-btn no-print" onclick="window.print()">
        Print Invoice
    </button>

    <div class="a4-page <?php echo $densityClass; ?>">


        <div style="text-align: right; margin-bottom: 5px;">
            <strong>Date:</strong> <span id="currentDate"></span>
        </div>
        <div class="invoice-title">INVOICE</div>


        <!-- ══════════════════════════════════════════════
       CUSTOMER + INVOICE META
  ══════════════════════════════════════════════ -->
        <div class="customer-section">

            <div class="customer-block">
                <div class="customer-label">Customer:</div>
                <div class="customer-details">
                    <div class="customer-name"><?php echo htmlspecialchars($invoiceData['customer']['name'] ?? 'N/A'); ?>,</div>
                    <div class="customer-address">
                        <?php echo nl2br(htmlspecialchars($invoiceData['customer']['address'] ?? 'N/A')); ?><br />
                        <?php echo htmlspecialchars($invoiceData['customer']['city'] ?? ''); ?>
                    </div>
                </div>
            </div>

            <div class="invoice-meta">
                <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoiceData['invoice_number']); ?></p>
                <p><strong>Report No.:</strong> <?php echo htmlspecialchars($invoiceData['report_number']); ?></p>
                <p class="division-line"><strong>Divisions:</strong> <?php echo htmlspecialchars($sigDiv); ?></p>
            </div>

        </div><!-- /.customer-section -->


        <!-- ══════════════════════════════════════════════
       DATE OF REQUEST
  ══════════════════════════════════════════════ -->
        <div class="date-request">
            Date of Request: &nbsp;<strong><?php echo htmlspecialchars($invoiceData['date_of_request']); ?></strong>
        </div>


        <!-- ══════════════════════════════════════════════
       INVOICE TABLE
  ══════════════════════════════════════════════ -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th class="col-sample">Sample Type</th>
                    <th class="col-params">Parameters</th>
                    <th class="col-fee">Testing Fee (Rs.)</th>
                    <th class="col-unit">Unit Price (Rs.)</th>
                    <th class="col-nos">No. of Samples</th>
                    <th class="col-sub">Sub Total (Rs.)</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($invoiceData['rows'] as $row): ?>
                    <tr>
                        <td class="cell-sample"><?php echo htmlspecialchars($row['sample_type']); ?></td>
                        <td class="cell-params">
                            <?php foreach ($row['parameters'] as $param): ?>
                                <?php
                                // Basic italicizing of standard scientific names if possible, else rely on pure HTML if needed.
                                // We'll leave it as plain text for safety unless specific italic matching is requested.
                                // For APC* logic:
                                $name = htmlspecialchars($param['name']);
                                if (strpos($name, 'APC') !== false && strpos($name, 'APC*') === false) {
                                    $name = str_replace('APC', 'APC<sup>*</sup>', $name);
                                }
                                // Italicize E.coli, Salmonella, Vibrio, Listeria
                                $italics = ['E.coli', 'Salmonella', 'Vibrio parahaemolyticus', 'Listeria monocytogenes', 'Listeria'];
                                foreach ($italics as $it) {
                                    $name = preg_replace('/\b' . preg_quote($it, '/') . '\b/i', '<em>$0</em>', $name);
                                }
                                ?>
                                <p><?php echo $name; ?></p>
                            <?php endforeach; ?>
                        </td>
                        <td class="cell-fee">
                            <?php foreach ($row['parameters'] as $param): ?>
                                <p><?php echo rtrim(rtrim(number_format($param['fee'], 2, '.', ''), '0'), '.'); // Trim useless .00 
                                    ?></p>
                            <?php endforeach; ?>
                        </td>
                        <td class="cell-unit" style="vertical-align:middle; text-align:center;">
                            <?php echo number_format($row['unit_price'], 2, '.', ''); ?>
                        </td>
                        <td class="cell-nos" style="vertical-align:middle; text-align:center;">
                            <?php echo str_pad($row['quantity'], 2, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="cell-sub" style="vertical-align:middle; text-align:right; padding-right:10px;">
                            <?php echo number_format($row['sub_total'], 2, '.', ''); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Extra Items / Additional Charges -->
                <?php if (!empty($invoiceData['extra_items'])): ?>
                    <?php foreach ($invoiceData['extra_items'] as $extra): ?>
                        <tr>
                            <td class="cell-sample">Additional Charges</td>
                            <td class="cell-params">
                                <p><?php echo htmlspecialchars($extra['name']) . ($extra['quantity'] > 1 ? " (" . $extra['quantity'] . ")" : ""); ?></p>
                            </td>
                            <td class="cell-fee">
                                <p>-</p>
                            </td>
                            <td class="cell-unit" style="vertical-align:middle; text-align:center;">
                                <?php echo number_format($extra['unit_price'], 2, '.', ''); ?>
                            </td>
                            <td class="cell-nos" style="vertical-align:middle; text-align:center;">
                                -
                            </td>
                            <td class="cell-sub" style="vertical-align:middle; text-align:right; padding-right:10px;">
                                <?php echo number_format($extra['line_total'], 2, '.', ''); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Empty spacer row -->
                <!-- <tr>
                    <td style="border-left:1px solid #111; border-right:1px solid #111; border-top:none; border-bottom:none; height:10px;" colspan="2"></td>
                    <td style="border:none; height:10px;"></td>
                    <td style="border:none; height:10px;"></td>
                    <td style="border:none; height:10px;"></td>
                    <td style="border-left:1px solid #111; border-right:1px solid #111; border-top:none; border-bottom:none; height:10px;"></td>
                </tr> -->

                <!-- Total row -->
                <tr class="total-row">
                    <td colspan="4" style="text-align:right; border-top:1px solid #111; border-bottom:1px solid #111; padding-right:10px;">
                        <!-- <strong>Total</strong> -->
                    </td>
                    <td style="border:1px solid #111; text-align:center; vertical-align:middle;">
                        <strong><?php echo str_pad($invoiceData['total_items_count'] ?? $totalSamples, 2, '0', STR_PAD_LEFT); ?></strong>
                    </td>
                    <td style="border-top:1px solid #111; border-bottom:1px solid #111; text-align:right; padding-right:10px; vertical-align:middle;">
                        <strong><?php echo number_format($invoiceData['total_payable'], 2, '.', ''); ?></strong>
                    </td>
                </tr>

                <!-- Total Payable row -->
                <tr class="total-payable-row">
                    <td colspan="5" style="text-align:right; border-bottom:1px solid #111; padding-right:10px; height: 30px; vertical-align:middle;">
                        <strong>Total Payable (Rs.)</strong>
                    </td>
                    <td style="border-bottom:1px solid #111; border-left:1px solid #111; text-align:right; padding-right:10px; vertical-align:middle;">
                        <strong><?php echo number_format($invoiceData['total_payable'], 2, '.', ''); ?></strong>
                    </td>
                </tr>

            </tbody>
        </table><!-- /.invoice-table -->


        <!-- ══════════════════════════════════════════════
       FOOTNOTE
  ══════════════════════════════════════════════ -->
        <div class="footnote">
            <?php
            if (!empty($invoiceData['footnotes'])) {
                foreach ($invoiceData['footnotes'] as $short => $full) {
                    echo "<div>* {$short} – {$full}</div>";
                }
            }
            ?>
        </div>


        <!-- ══════════════════════════════════════════════
       PAYMENT SECTION
  ══════════════════════════════════════════════ -->
        <div class="payment-section">
            <div class="payment-title">Payment</div>
            <div class="payment-body">
                The fee for the Testing Services must be paid either by a cheque (written in favour of Director General, NARA)
                or in cash to Finance Division between 0900 – 1500&nbsp;h on any weekday.
            </div>
        </div>


        <!-- ══════════════════════════════════════════════
       SIGNATURE BLOCK
  ══════════════════════════════════════════════ -->
        <div class="signature-block">
            <div class="signature-space"></div>
            <div class="sig-name"><?php echo htmlspecialchars($sigName); ?></div>
            <div class="sig-title"><?php echo htmlspecialchars($sigTitle); ?></div>
            <div class="sig-div"><?php echo htmlspecialchars($sigDiv); ?></div>
        </div>

        <!-- ══════════════════════════════════════════════
       FOOTER (CONTACT)
  ══════════════════════════════════════════════ -->


    </div><!-- /.a4-page -->
    <script>
        // Set the current date dynamically based on client system
        document.addEventListener('DOMContentLoaded', function() {
            const dateSpan = document.getElementById('currentDate');
            if (dateSpan) {
                const now = new Date();
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                dateSpan.textContent = now.toLocaleDateString('en-US', options);
            }
        });
    </script>
</body>

</html>