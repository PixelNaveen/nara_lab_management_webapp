<?php
/**
 * SAF Template - Sample Acceptance Form
 * Version: 2.1 - CORRECTED
 * 
 * FIXED: UTF-8 encoding (proper emojis)
 * FIXED: Payment:: (double colon)
 * FIXED: CSS path handling
 */

if (!isset($data)) {
    echo '<h3>Error: No data provided</h3>';
    exit;
}

$totalPages = $data['total_pages'];
$totalSamples = $data['total_samples'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Acceptance Form - <?= htmlspecialchars($data['acceptance']['report_ref']) ?></title>
    <link rel="stylesheet" href="/public/assets/css/saf-styles.css">
</head>
<body>
    <!-- Controls Panel -->
    <div class="controls">
        <h3>📄 Select Format & Download PDF</h3>
        
        <?php if ($totalPages > 1): ?>
        <div class="multi-page-notice">
            <strong>Multi-Page Form:</strong> This SAF has <?= $totalSamples ?> samples across <?= $totalPages ?> pages.
            All pages will be printed/downloaded together.
        </div>
        <?php endif; ?>
        
        <div class="size-selector">
            <label>
                <input type="radio" name="pageSize" value="half-a4" checked onchange="changePageSize(this.value)">
                <div class="option-content">
                    <div class="option-title">Half A4 Landscape</div>
                    <div class="option-desc">210×148mm on 297×210mm paper • Compact • Form uses left half</div>
                </div>
            </label>
            <label>
                <input type="radio" name="pageSize" value="full-a5" onchange="changePageSize(this.value)">
                <div class="option-content">
                    <div class="option-title">Full A5 Landscape</div>
                    <div class="option-desc">210×148mm on 210×148mm paper • Compact • Fills entire page</div>
                </div>
            </label>
            <label>
                <input type="radio" name="pageSize" value="a4-natural" onchange="changePageSize(this.value)">
                <div class="option-content">
                    <div class="option-title">A4 Natural Portrait</div>
                    <div class="option-desc">210mm width on A4 paper • Comfortable spacing • Professional format</div>
                </div>
            </label>
        </div>
        
        <button class="btn-print" onclick="downloadPDF()">📥 Download as PDF</button>
        <p style="font-size: 12px; color: #666; margin-top: 12px; text-align: center;">
            💡 <?= $totalPages > 1 ? "PDF will include all $totalPages pages automatically" : "Click button above to download PDF" ?>
        </p>
    </div>

    <!-- SAF Form Container (All Pages) -->
    <div id="formContainer">
        <?php 
        // Loop through each page
        foreach ($data['pages'] as $pageIndex => $pageItems): 
            $currentPage = $pageIndex + 1;
            $isFirstPage = ($pageIndex === 0);
        ?>
        
        <!-- Page <?= $currentPage ?> -->
        <div class="form-container half-a4" style="<?= !$isFirstPage ? 'margin-top: 30px;' : '' ?>">
            <div class="form-title">
                Sample Acceptance Form
                <?php if ($totalPages > 1): ?>
                    - Page <?= $currentPage ?> of <?= $totalPages ?>
                <?php endif; ?>
            </div>
            
            <!-- Header Table -->
            <table>
                <tr class="header-row">
                    <td style="width: 35%;">Received by: <?= htmlspecialchars($data['acceptance']['received_by']) ?></td>
                    <td style="width: 25%;">Date: <?= htmlspecialchars($data['acceptance']['date']) ?></td>
                    <td style="width: 40%;">Time arrived at lab:</td>
                </tr>
                <tr class="header-row">
                    <td colspan="3">Client / address: <?= htmlspecialchars($data['client']['full_address']) ?></td>
                </tr>
            </table>

            <!-- Sample Items Table (10 rows per page) -->
            <table>
                <tr>
                    <th style="width: 28%;">Sample/s</th>
                    <th style="width: 16%;">Sample Code</th>
                    <th style="width: 16%;">Wt./ vol./<br>size/ area</th>
                    <th style="width: 12%;">Container<br>damage</th>
                    <th style="width: 16%;">Ambient/Chill/<br>frozen</th>
                    <th style="width: 12%;">Validity</th>
                </tr>
                
                <?php foreach ($pageItems as $item): ?>
                    <tr class="sample-row">
                        <td><?= $item['display_number'] . '. ' . htmlspecialchars($item['sample_name']) ?></td>
                        <td><?= htmlspecialchars($item['sample_code']) ?></td>
                        <td><?= htmlspecialchars($item['weight_volume']) ?></td>
                        <td><?= htmlspecialchars($item['container_damage']) ?></td>
                        <td><?= htmlspecialchars($item['temperature']) ?></td>
                        <td><?= htmlspecialchars($item['validity']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Payment Row - FIXED: Payment:: (double colon) -->
                <tr class="payment-row">
                    <td colspan="3" style="width: 60%;">Payment:: Test Charge: Rs. <?= number_format($data['acknowledgement']['test_charges'], 2) ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total charge: Rs. <?= number_format($data['acknowledgement']['total_charges'], 2) ?></td>
                    <td colspan="2" style="width: 28%;">Tentative date of issuing: <?= htmlspecialchars($data['acceptance']['tentative_date']) ?></td>
                    <td style="width: 12%;">Test report reference number: <?= htmlspecialchars($data['acceptance']['report_ref']) ?></td>
                </tr>
            </table>

            <!-- Decision Section -->
            <div class="decision-section">
                1. Resources are <strong>Available / Not Available</strong> to carry out the analysis<br>
                2. <strong>Accept / Do not Accept</strong> the sample/s
            </div>

            <!-- Remarks Section -->
            <div class="remarks-section">
                <strong>Remarks:</strong>
                <?php if (!empty($data['acceptance']['remarks'])): ?>
                    <?= nl2br(htmlspecialchars($data['acceptance']['remarks'])) ?>
                <?php endif; ?>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <strong>Signature:</strong>
            </div>

            <!-- Footer Metadata -->
            <table class="footer-table">
                <tr>
                    <td colspan="2" style="width: 44%;"><strong>Title:</strong> Sample Acceptance Form</td>
                    <td style="width: 28%;"><strong>Doc No:</strong> QCm/SAF/01</td>
                    <td style="width: 28%;"><strong>Revision No:</strong> 09</td>
                </tr>
                <tr>
                    <td style="width: 22%;"><strong>Date of Revision:</strong> 10/09/2014</td>
                    <td style="width: 22%;"><strong>Reviewed by:</strong> DQM</td>
                    <td style="width: 28%;"><strong>Approved by:</strong> QM</td>
                    <td style="width: 28%;"><strong>Page:</strong> <?= $currentPage ?> of <?= $totalPages ?></td>
                </tr>
            </table>
        </div>
        <!-- End Page <?= $currentPage ?> -->

        <?php endforeach; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="/public/assets/libs/html2pdf.bundle.min.js"></script>
    <script src="/public/assets/js/saf-handler.js"></script>
</body>
</html>