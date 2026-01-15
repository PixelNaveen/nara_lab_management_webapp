<?php
/**
 * Analyst Information Form (AIF) Template
 * Based on original AIF.html design
 * Populated with dynamic data from analyst-model.php
 * 
 * CORRECTED:
 * - Inner table: Only horizontal borders
 * - Extra column: Full borders
 * - Selected parameters: Bold & underlined
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
    <title>Analyst Information Form - <?= htmlspecialchars($data['sample_id']) ?></title>
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
        }
        
        .form-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .main-table {
            margin-bottom: 8px;
        }
        
        td {
            border: 1px solid #333;
            padding: 5px;
            vertical-align: top;
            font-size: 11px;
        }
        
        .left-label {
            width: 30%;
        }
        
        .right-content {
            width: 70%;
        }
        
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Inner table with YOUR exact AIF.html styling */
        .inner-table td {
            border: 1px solid #333;
            padding: 4px;
            font-size: 11px;
            height: 22px;
        }
        
        .param-num {
            width: 45%;
        }
        
        .param-value {
            width: 55%;
        }
        
        .authorized-section {
            margin-top: 6px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .footer-table {
            font-size: 10px;
        }
        
        .footer-table td {
            padding: 5px;
            border: 1px solid #333;
        }
        
        .issued-by {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Selected parameter highlighting */
        .param-selected {
            font-weight: bold;
            text-decoration: underline;
        }
        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            
            .form-container {
                border: none;
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
                <td class="left-label" rowspan="2" style="height: 100px;">
                    Sample description:<br>
                    <div style="margin-top: 5px;">
                        <?= htmlspecialchars($data['sample_description']) ?>
                    </div>
                </td>
                <td class="right-content" style="height: 60px;">
                    Sample Nos:<br>
                    <div style="margin-top: 3px; font-size: 10px;">
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
                    <div style="margin-bottom: 20px;">
                        <strong>Sample Details:</strong><br><br>
                        Volume/ Weight: <?= htmlspecialchars($data['volume_weight']) ?>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <strong>Sampling date:</strong> <?= htmlspecialchars($data['received_date']) ?>
                    </div>
                    <div>
                        <strong>Remarks:</strong>
                    </div>
                </td>
                <td class="right-content" style="padding: 5px;">
                    <!-- Dynamic Parameter Table (EXACTLY 2 columns as your HTML) -->
                    <table class="inner-table">
                        <?php foreach ($data['parameters'] as $index => $param): ?>
                        <tr>
                            <td class="param-num <?= $param['is_selected'] ? 'param-selected' : '' ?>">
                                <?= ($index + 1) . '. ' . htmlspecialchars($param['parameter_name']) ?>
                            </td>
                            <td class="param-value <?= $param['is_selected'] ? 'param-selected' : '' ?>" style="word-wrap: break-word; overflow-wrap: break-word;">
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
                    Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; by: _______________
                </td>
                <td class="right-content"></td>
            </tr>
            
            <!-- Report Submission Date (EMPTY - not pre-filled) -->
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
                <td style="width: 32%;"><strong>Revision No:</strong> 06</td>
            </tr>
            <tr>
                <td><strong>Date of Revision:</strong> 15/12/2020</td>
                <td><strong>Reviewed by:</strong> DQM</td>
                <td><strong>Approved by:</strong> QM &nbsp;&nbsp; <strong>Page:</strong> 1 of 1</td>
            </tr>
        </table>
    </div>
</body>
</html>