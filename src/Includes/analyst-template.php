<?php
/**
 * Analyst Information Form (AIF) Template - GOVERNMENT GRADE
 * 
 * IMPROVEMENTS:
 * - Government-standard typography (pt units)
 * - Title: 11pt Bold
 * - Body: 10pt Regular
 * - Tables: 9pt Regular
 * - Footer: 8.5pt Regular
 * - Optimized spacing and heights
 * 
 * @version 2.0 - GOVERNMENT GRADE
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
    <title>Analyst Information Form - <?= htmlspecialchars($data['sample_id']) ?></title>
    <style>
        /* =============================================
           GOVERNMENT-GRADE CSS - ANALYST INFORMATION FORM
           Version: 2.0 - Professional Typography Standard
           ============================================= */
        
        /* ===== PAGE SETUP ===== */
        @page {
            size: A4 portrait;
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
            padding: 10pt;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* ===== FORM CONTAINER ===== */
        .form-container {
            width: 210mm;
            min-height: 270mm;
            margin: 0 auto;
            background-color: #fff;
            padding: 6mm;
            page-break-after: always;
            box-shadow: 0 2pt 8pt rgba(0, 0, 0, 0.1);
        }
        
        /* ===== FORM TITLE - GOVERNMENT STANDARD ===== */
        .form-title {
            text-align: center;
            font-size: 11pt;           /* ✅ Government standard */
            font-weight: bold;
            margin-bottom: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            line-height: 1.4;
            color: #000;
        }
        
        /* ===== BASE TABLE STYLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .main-table {
            margin-bottom: 6pt;
        }
        
        /* ===== TABLE CELLS - GOVERNMENT STANDARD ===== */
        td {
            border: 0.75pt solid #333;
            padding: 6pt;              /* ✅ Converted to pt */
            vertical-align: top;
            font-size: 10pt;           /* ✅ Government body text */
            line-height: 1.4;
            color: #000;
        }
        
        /* ===== COLUMN WIDTHS ===== */
        .left-label {
            width: 30%;
        }
        
        .right-content {
            width: 70%;
        }
        
        /* ===== INNER TABLE (PARAMETERS) ===== */
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .inner-table td {
            border: 0.75pt solid #333;
            padding: 5pt;              /* ✅ Converted to pt */
            font-size: 9pt;            /* ✅ Government table data */
            height: 26pt;              /* ✅ Increased from 22px */
            line-height: 1.3;
            vertical-align: middle;
        }
        
        /* ===== PARAMETER COLUMNS ===== */
        .param-num {
            width: 45%;
        }
        
        .param-value {
            width: 55%;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* ===== PARAMETER HIGHLIGHTING ===== */
        .param-selected {
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* ===== SAMPLE NUMBERS SMALLER TEXT ===== */
        .sample-numbers-text {
            font-size: 9pt;            /* ✅ Slightly smaller */
            margin-top: 3pt;
            line-height: 1.4;
        }
        
        /* ===== AUTHORIZED SECTION ===== */
        .authorized-section {
            margin-top: 8pt;
            margin-bottom: 8pt;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.4;
        }
        
        /* ===== FOOTER TABLE - GOVERNMENT METADATA ===== */
        .footer-table {
            font-size: 8.5pt;          /* ✅ Government footer standard */
            margin-top: 0;
        }
        
        .footer-table td {
            padding: 5pt;              /* ✅ Converted to pt */
            border: 0.75pt solid #333;
            line-height: 1.3;
        }
        
        /* ===== ISSUED BY - FINE PRINT ===== */
        .issued-by {
            font-size: 8pt;            /* ✅ Government fine print */
            color: #666;
            margin-top: 3pt;
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
            
            table, td {
                border-color: #000 !important;
            }
            
            /* Ensure exact sizes in print */
            .form-title {
                font-size: 11pt !important;
            }
            
            td {
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
        <div class="form-title">Analyst Information Form</div>
        
        <table class="main-table">
            <!-- Date & Time -->
            <tr>
                <td class="left-label">
                    Date: <?= htmlspecialchars($data['received_date']) ?>
                </td>
                <td class="right-content">Time: _________________</td>
            </tr>
            
            <!-- Sample Description & Sample Numbers -->
            <tr>
                <td class="left-label" rowspan="2" style="height: 100pt;">
                    Sample description:<br>
                    <div style="margin-top: 5pt;">
                        <?= htmlspecialchars($data['sample_description']) ?>
                    </div>
                </td>
                <td class="right-content" style="height: 60pt;">
                    Sample Nos:<br>
                    <div class="sample-numbers-text">
                        <?= htmlspecialchars($data['sample_numbers']) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="right-content">
                    Sample storage: QC/REF/05, QC/FRE/05<br>
                    Reserve storage: QC/REF/04, QC/FRE/05<br>
                    Direct analysis / Other &nbsp;&nbsp;&nbsp;&nbsp; Sign / Date: _______________
                </td>
            </tr>
            
            <!-- Received By -->
            <tr>
                <td class="left-label">
                    Received by: <?= htmlspecialchars($data['received_by']) ?>
                </td>
                <td class="right-content" style="font-weight: bold;">
                    Parameters to be analyzed:
                </td>
            </tr>
            
            <!-- Sample Details & Parameter Table -->
            <tr>
                <td class="left-label">
                    <div style="margin-bottom: 18pt;">
                        <strong>Sample Details:</strong><br><br>
                        Volume/ Weight: <?= htmlspecialchars($data['volume_weight']) ?>
                    </div>
                    <div style="margin-bottom: 18pt;">
                        <strong>Sampling date:</strong> <?= htmlspecialchars($data['received_date']) ?>
                    </div>
                    <div>
                        <strong>Remarks:</strong>
                    </div>
                </td>
                <td class="right-content" style="padding: 5pt;">
                    <!-- Dynamic Parameter Table -->
                    <table class="inner-table">
                        <?php foreach ($data['parameters'] as $index => $param): ?>
                        <tr>
                            <td class="param-num <?= $param['is_selected'] ? 'param-selected' : '' ?>">
                                <?= ($index + 1) . '. ' . htmlspecialchars($param['parameter_name']) ?>
                            </td>
                            <td class="param-value <?= $param['is_selected'] ? 'param-selected' : '' ?>">
                                <?= htmlspecialchars($param['methods']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            
            <!-- Analysis Start Date -->
            <tr>
                <td class="left-label">
                    <strong>Analysis to be started on:</strong><br>
                    Date: <br/> By: _______________
                </td>
                <td class="right-content"></td>
            </tr>
            
            <!-- Report Submission Date (EMPTY) -->
            <tr>
                <td class="left-label">Report submission date:</td>
                <td class="right-content"><!-- EMPTY - for manual entry --></td>
            </tr>
        </table>
        
        <!-- Authorized By -->
        <div class="authorized-section">
            Authorized by: _________________________________
            <?php if ($data['issued_by']): ?>
                <div class="issued-by">
                    Form issued by: <?= htmlspecialchars($data['issued_by']) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Metadata -->
        <table class="footer-table">
            <tr>
                <td style="width: 38%;"><strong>Title: Analyst Information Form</strong></td>
                <td style="width: 30%;"><strong>Doc No:</strong> QCm/AIF/01</td>
                <td style="width: 32%;"><strong>Revision No:</strong> 07</td>
            </tr>
            <tr>
                <td><strong>Date of Revision:</strong> 01/01/2026</td>
                <td><strong>Reviewed by:</strong> DQM</td>
                <td><strong>Approved by:</strong> QM &nbsp;&nbsp; <strong>Page:</strong> 1 of 1</td>
            </tr>
        </table>
    </div>
</body>
</html>