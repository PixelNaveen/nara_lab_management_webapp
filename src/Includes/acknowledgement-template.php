<?php
/**
 * Sample Acknowledgement Form (SAcF) Template - GOVERNMENT GRADE
 * 
 * IMPROVEMENTS:
 * - Government-standard typography (pt units)
 * - Title: 11pt Bold
 * - Body: 10pt Regular
 * - Tables: 9pt Regular
 * - Footer: 8.5pt Regular
 * - Optimized spacing and heights
 * - Professional government appearance
 * 
 * @version 5.0 - GOVERNMENT GRADE
 */

if (!isset($data)) {
    echo '<h3>Error: No data provided</h3>';
    exit;
}

// Adaptive page size based on parameter count
$paramCount = count($data['parameters']);
$isCompact = ($paramCount <= 13);
$pageSize = $isCompact ? 'A5 landscape' : 'A4 portrait';
$pageWidth = $isCompact ? '210mm' : '210mm';
$pageHeight = $isCompact ? '148mm' : '297mm';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Acknowledgement Form - <?= htmlspecialchars($data['report_ref']) ?></title>
    <style>
        /* =============================================
           GOVERNMENT-GRADE CSS - SAMPLE ACKNOWLEDGEMENT FORM
           Version: 5.0 - Professional Typography Standard
           ============================================= */
        
        /* ===== PAGE SETUP ===== */
        @page {
            size: <?= $pageSize ?>;
            margin: 10mm;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #e8dcc8;
            padding: 10mm;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* ===== FORM CONTAINER ===== */
        .form-container {
            width: <?= $pageWidth ?>;
            height: <?= $pageHeight ?>;
            margin: 0 auto;
            background-color: #fff;
            padding: 8mm;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2pt 8pt rgba(0, 0, 0, 0.1);
        }
        
        /* ===== FORM TITLE - GOVERNMENT STANDARD ===== */
        .form-title {
            text-align: center;
            font-size: 11pt;           /* ✅ Government standard */
            font-weight: bold;
            margin-bottom: 10pt;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            line-height: 1.4;
            color: #000;
        }
        
        /* ===== TABLE WRAPPER ===== */
        .main-table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .main-table-wrapper > table {
            height: 100%;
        }
        
        /* ===== BASE TABLE STYLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3pt;
        }
        
        /* ===== TABLE CELLS - GOVERNMENT STANDARD ===== */
        td, th {
            border: 0.75pt solid #333;
            padding: 6pt 7pt;          /* ✅ Converted to pt */
            vertical-align: top;
            font-size: 10pt;           /* ✅ Government body text */
            line-height: 1.4;
            color: #000;
        }
        
        th {
            font-weight: bold;
            background-color: #fff;
        }
        
        /* ===== LABEL CELLS ===== */
        .label-cell {
            font-weight: normal;
            width: 30%;
            background-color: #fff;
            font-size: 10pt;
        }
        
        /* ===== DATE ROW - ADJUSTED HEIGHT ===== */
        .date-row {
            height: 38pt;              /* ✅ Increased for 10pt text */
        }
        
        .date-row td {
            padding: 7pt 8pt;
            vertical-align: middle;
        }
        
        /* ===== TENTATIVE ROW - ADJUSTED HEIGHT ===== */
        .tentative-row {
            height: 38pt;              /* ✅ Increased for 10pt text */
        }
        
        .tentative-row td {
            padding: 7pt 8pt;
            vertical-align: middle;
        }
        
        /* ===== SAMPLE INFO ROW ===== */
        .sample-info-row {
            height: auto;
        }
        
        /* ===== SAMPLE INFORMATION LABEL ===== */
        .label-cell .sample-info-label {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 6pt;
            display: block;
        }
        
        .label-cell .sample-info-content {
            font-size: 9pt;            /* ✅ Slightly smaller for list */
            line-height: 1.5;
            padding: 2pt;
            white-space: pre-line;
        }
        
        /* ===== PARAMETER TABLE (INNER TABLE) ===== */
        .inner-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }
        
        .inner-table td {
            border: none;              /* ✅ No borders on inner cells */
            padding: 5pt 6pt;          /* ✅ Converted to pt */
            vertical-align: top;
            height: 28pt;              /* ✅ Increased from 26px */
            font-size: 9pt;            /* ✅ Government table data */
            line-height: 1.3;
            color: #000;
        }
        
        /* ===== PARAMETER TABLE COLUMNS ===== */
        .test-col {
            width: 58%;
        }
        
        .standard-col {
            width: 42%;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* ===== CONTENT CELLS ===== */
        .content-cell {
            background-color: #fff;
            font-size: 10pt;           /* ✅ Government body text */
        }
        
        /* ===== BOTTOM SECTION - ADJUSTED HEIGHT ===== */
        .bottom-section td {
            height: 48pt;              /* ✅ Increased for 10pt text */
            background-color: #fff;
            font-size: 10pt;
            padding: 8pt;
            vertical-align: middle;
        }
        
        /* ===== FOOTER TABLE - GOVERNMENT METADATA ===== */
        .footer-table {
            margin-top: 8pt;
            font-size: 8.5pt;          /* ✅ Government footer standard */
        }
        
        .footer-table td {
            padding: 5pt 6pt;          /* ✅ Converted to pt */
            border: 0.75pt solid #333;
            line-height: 1.3;
        }
        
        /* ===== ISSUED BY - FINE PRINT ===== */
        .issued-by {
            font-size: 8pt;            /* ✅ Government fine print */
            color: #666;
            margin-top: 4pt;
            font-style: italic;
            line-height: 1.2;
        }
        
        /* ===== STRONG TEXT ===== */
        strong {
            font-weight: bold;
        }
        
        /* ===== PRINT STYLES ===== */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            
            .form-container {
                border: none;
                box-shadow: none;
                page-break-after: always;
            }
            
            table, th, td {
                border-color: #000 !important;
            }
            
            /* Ensure exact sizes in print */
            .form-title {
                font-size: 11pt !important;
            }
            
            td, th {
                font-size: 10pt !important;
            }
            
            .inner-table td {
                font-size: 9pt !important;
            }
            
            .footer-table {
                font-size: 8.5pt !important;
            }
            
            .issued-by {
                font-size: 8pt !important;
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
                    <strong>Date Received:</strong> <?= htmlspecialchars($data['received_date']) ?>
                </td>
                <td class="content-cell">
                    <strong>Time:</strong> _________________
                </td>
            </tr>
            
            <!-- Sample Information & Parameters -->
            <tr class="sample-info-row">
                <td class="label-cell" style="vertical-align: top; padding: 10pt;">
                    <span class="sample-info-label">Sample Information</span>
                    <div class="sample-info-content">
                        <?php 
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
                <td class="content-cell" colspan="2" style="padding: 6pt;">
                    <!-- Dynamic Parameter Table -->
                    <table class="inner-table">
                        <?php foreach ($data['parameters'] as $index => $param): ?>
                        <tr>
                            <td class="test-col">
                                <strong><?= ($index + 1) ?>.</strong> <?= htmlspecialchars($param['parameter_name']) ?>
                            </td>
                            <td class="standard-col">
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
                    <strong>Tentative date of issuing the report:</strong> <?= htmlspecialchars($data['tentative_date']) ?>
                </td>
                <td class="content-cell">
                    <strong>Test report reference No:</strong> <?= htmlspecialchars($data['report_ref']) ?>
                </td>
            </tr>
            
            <!-- Test Charges & Receipt -->
            <tr class="bottom-section">
                <td class="content-cell" colspan="2">
                    <strong>Test charge:</strong> Rs. <?= number_format($data['test_charges'], 2) ?>
                    <?php if ($data['additional_charges'] > 0): ?>
                        + Rs. <?= number_format($data['additional_charges'], 2) ?>
                    <?php endif; ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>Total:</strong> Rs. <?= number_format($data['total_charges'], 2) ?>
                </td>
                <td class="content-cell">
                    <strong>Receipt no:</strong> <?= htmlspecialchars($data['receipt_no']) ?>
                </td>
            </tr>
            
            <!-- Signature -->
            <tr class="bottom-section">
                <td class="content-cell" colspan="3">
                    <strong>Signature:</strong> _________________________________
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