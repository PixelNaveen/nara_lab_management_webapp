<?php
/**
 * Sample Acknowledgement Form (SAcF) Template
 * Based on original SAF.html design
 * Populated with dynamic data from acknowledgement-model.php
 * 
 * @version 1.0
 */

if (!isset($data)) {
    echo '<h3>Error: No data provided</h3>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Acknowledgement Form - <?= htmlspecialchars($data['report_ref']) ?></title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #e8dcc8;
            padding: 10px;
            margin: 0;
        }
        
        .form-container {
            width: 210mm;
            height: 148mm;
            margin: 0 auto;
            background-color: #fff;
            padding: 6mm;
            page-break-after: always;
            display: flex;
            flex-direction: column;
        }
        
        .form-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .main-table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .main-table-wrapper > table {
            height: 100%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        
        td, th {
            border: 1px solid #333;
            padding: 5px;
            vertical-align: top;
            font-size: 11px;
        }
        
        .label-cell {
            font-weight: normal;
            width: 30%;
            background-color: #fff;
        }
        
        .date-row {
            height: 32px;
        }
        
        .tentative-row {
            height: 32px;
        }
        
        .sample-info-row {
            height: auto;
        }
        
        .inner-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }
        
        .inner-table td {
            /* NO borders - exactly as your SAF.html */
            /* border: 1px solid #333; */
            padding: 4px 5px;
            vertical-align: top;
            height: 24px;
            font-size: 11px;
        }
        
        .test-col {
            width: 58%;
        }
        
        .standard-col {
            width: 42%;
            text-align: left;
        }
        
        .content-cell {
            background-color: #fff;
            font-size: 11px;
        }
        
        .bottom-section td {
            height: 42px;
            background-color: #fff;
            font-size: 11px;
        }
        
        .footer-table {
            margin-top: 8px;
            font-size: 10px;
        }
        
        .footer-table td {
            padding: 5px;
            border: 1px solid #333;
        }
        
        .issued-by {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            
            .form-container {
                border: none;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-title">Sample Acknowledgement Form</div>
        
        <div class="main-table-wrapper">
        <table>
            <!-- Date Received & Time -->
            <tr class="date-row">
                <td class="label-cell" colspan="2">
                    Date Received: <?= htmlspecialchars($data['received_date']) ?>
                </td>
                <td class="content-cell">Time: _________________</td>
            </tr>
            
            <!-- Sample Information with Sample Names in Label Cell and Parameter Table -->
            <tr class="sample-info-row">
                <td class="label-cell" style="vertical-align: top; padding: 8px;">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 6px;">
                        Sample Information
                    </div>
                    <div style="font-size: 10px; line-height: 1.6; padding: 2px; white-space: pre-line;">
                        <?php 
                        // Split by comma, add commas and line breaks
                        $sampleNames = explode(', ', $data['sample_information']);
                        foreach ($sampleNames as $index => $name) {
                            echo htmlspecialchars($name);
                            if ($index < count($sampleNames) - 1) {
                                echo ',<br>';
                            }
                        }
                        ?>
                    </div>
                </td>
                <td class="content-cell" colspan="2" style="padding: 5px;">
                    <!-- Dynamic Parameter Table (NO borders on inner cells) -->
                    <table class="inner-table">
                        <?php foreach ($data['parameters'] as $index => $param): ?>
                        <tr>
                            <td class="test-col">
                                <?= ($index + 1) . '. ' . htmlspecialchars($param['parameter_name']) ?>
                            </td>
                            <td class="standard-col" style="word-wrap: break-word; overflow-wrap: break-word;">
                                <?= htmlspecialchars($param['methods']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            
            <!-- Tentative Date & Report Reference -->
            <tr class="tentative-row">
                <td class="content-cell" colspan="2">
                    Tentative date of issuing the report: <?= htmlspecialchars($data['tentative_date']) ?>
                </td>
                <td class="content-cell">
                    Test report reference No: <?= htmlspecialchars($data['report_ref']) ?>
                </td>
            </tr>
            
            <!-- Test Charges & Receipt -->
            <tr class="bottom-section">
                <td class="content-cell" colspan="2">
                    Test charge: Rs. <?= number_format($data['test_charges'], 2) ?>
                    <?php if ($data['additional_charges'] > 0): ?>
                        + Rs. <?= number_format($data['additional_charges'], 2) ?>
                    <?php endif; ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Total: Rs. <?= number_format($data['total_charges'], 2) ?>
                </td>
                <td class="content-cell">
                    Receipt no: <?= htmlspecialchars($data['receipt_no']) ?>
                </td>
            </tr>
            
            <!-- Signature -->
            <tr class="bottom-section">
                <td class="content-cell" colspan="3">
                    Signature: _________________________________
                    <?php if ($data['issued_by']): ?>
                        <div class="issued-by">
                            Issued by: <?= htmlspecialchars($data['issued_by']) ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        </div>
        
        <!-- Footer Metadata -->
        <table class="footer-table">
            <tr>
                <td style="width: 38%;"><strong>Title: Sample Acknowledgement Form</strong></td>
                <td style="width: 30%;"><strong>Doc No:</strong> QCm/AF/01</td>
                <td style="width: 32%;"><strong>Revision No:</strong> 05</td>
            </tr>
            <tr>
                <td><strong>Date of Revision:</strong> 15/07/2025</td>
                <td><strong>Reviewed by:</strong> DQM</td>
                <td><strong>Approved by:</strong> QM &nbsp; <strong>Page:</strong> 1 of 1</td>
            </tr>
        </table>
    </div>
</body>
</html>