<?php
if (!isset($data)) {
    echo '<h3>Error: No data provided</h3>';
    exit;
}

require_once __DIR__ . '/../Helpers/Functions.php';

// Dynamic Scaling Logic for Single-Page Perfection
$paramCount = is_array($data['parameters'] ?? null) ? count($data['parameters']) : 0;
$scalingClass = 'scale-normal';
if ($paramCount > 20) {
    $scalingClass = 'scale-ultra';
} elseif ($paramCount > 13) {
    $scalingClass = 'scale-dense';
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
           GOVERNMENT-GRADE CSS - SMART SCALING AIF
           Version: 3.0 - High Density Enterprise Standard
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
            height: auto;
            min-height: auto;
            margin: 0 auto;
            background-color: #fff;
            padding: 6mm;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2pt 8pt rgba(0, 0, 0, 0.1);
        }

        /* ===== DYNAMIC SCALING ENGINE ===== */
        
        /* Normal Mode (1-13 params) */
        .scale-normal td { font-size: 10pt; padding: 5pt 6pt; }
        .scale-normal .form-title { font-size: 11pt; margin-bottom: 12pt; }
        .scale-normal .param-num, .scale-normal .param-value { font-size: 9.5pt; height: 26pt; }

        /* Dense Mode (14-20 params) */
        .scale-dense td { font-size: 9.2pt; padding: 4pt 5pt; }
        .scale-dense .form-title { font-size: 10.5pt; margin-bottom: 10pt; }
        .scale-dense .param-num, .scale-dense .param-value { font-size: 8.8pt; height: 24pt; }

        /* Ultra Mode (21-30 params) */
        .scale-ultra td { font-size: 8.4pt; padding: 3pt 4pt; }
        .scale-ultra .form-title { font-size: 10pt; margin-bottom: 8pt; }
        .scale-ultra .param-num, .scale-ultra .param-value { font-size: 8pt; height: 22pt; }
        .scale-ultra .authorized-section { margin-top: 6pt; margin-bottom: 6pt; font-size: 9pt; }

        /* ===== FORM TITLE ===== */
        .form-title {
            text-align: center;
            font-weight: bold;
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
            margin-bottom: 0;
        }

        /* ===== TABLE CELLS - GOVERNMENT STANDARD ===== */
        td {
            border: 0.75pt solid #000; /* Professional Black Fine Line */
            vertical-align: top;
            color: #000;
            line-height: 1.3;
        }

        /* ===== COLUMN WIDTHS ===== */
        .left-label { width: 30%; }
        .right-content { width: 70%; }

        /* ===== PARAMETER COLUMNS ===== */
        .param-num, .param-value {
            vertical-align: middle;
            color: #000;
        }

        .param-value {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ===== PARAMETER HIGHLIGHTING ===== */
        .param-selected {
            font-weight: bold;
        }

        /* ===== HIGH DENSITY SAMPLE LISTS ===== */
        .high-density-list {
            column-count: 2;
            column-gap: 12pt;
            font-size: 8.5pt !important;
        }

        /* ===== SAMPLE NUMBERS (VERTICAL FLOW UPGRADE) ===== */
        .sample-numbers-container {
            display: flex;
            gap: 20pt;
            margin-top: 2pt;
        }

        .sample-column {
            flex: 1;
        }

        .sample-column span {
            display: block;
            font-size: 9pt;
            line-height: 1.3;
            font-weight: bold;
        }

        /* ===== AUTHORIZED SECTION ===== */
        .authorized-section {
            margin-top: 10pt;
            margin-bottom: 10pt;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.4;
        }

        /* ===== FOOTER TABLE ===== */
        .footer-table {
            font-size: 8.5pt;
            margin-top: auto;
        }

        .footer-table td {
            padding: 3pt 5pt;
            border: 0.75pt solid #000;
            line-height: 1.3;
        }

        /* ===== ISSUED BY ===== */
        .issued-by {
            font-size: 8pt;
            color: #444;
            margin-top: 3pt;
            font-style: italic;
            line-height: 1.1;
        }

        strong { font-weight: bold; }

        /* ===== PRINT STYLES ===== */
        @media print {
            body { background-color: #fff; padding: 0; }
            .form-container { border: none; box-shadow: none; page-break-after: avoid; }
            table, td { border-color: #000 !important; }
        }
    </style>
</head>

<body class="<?= $scalingClass ?>">
    <div class="form-container">
        <div class="form-title">Analyst Information Form</div>

        <table class="main-table" style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 35%;">
                <col style="width: 35%;">
            </colgroup>
            <!-- Date & Time -->
            <tr>
                <td class="left-label">
                    Date: <?= htmlspecialchars($data['received_date']) ?>
                </td>
                <td class="right-content" colspan="2">Time: <?= htmlspecialchars(($data['received_time'] ?? '') ?: '_________________') ?></td>
            </tr>

            <!-- Sample Description & Sample Numbers -->
            <tr>
                <td class="left-label" rowspan="2">
                    Sample description:<br><br>
                    <?php
                    $sampleDesc = $data['sample_description'] ?? '';
                    $descNames = !empty($sampleDesc) ? explode(', ', $sampleDesc) : [];
                    $descCount = count($descNames);
                    
                    $densityClass = '';
                    if ($descCount > 20) {
                        $densityClass = 'high-density-list';
                    }
                    ?>
                    <div class="sample-description-text <?= $densityClass ?>">
                        <?php foreach ($descNames as $index => $name): ?>
                            <span style="display: block; break-inside: avoid;">
                                <?= htmlspecialchars($name) . ($index < $descCount - 1 ? ',' : '') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td class="right-content" colspan="2">
                    Sample Nos:<br>
                    <div class="sample-numbers-container">
                        <?php 
                        $allSamples = $data['sample_numbers_list'] ?? [];
                        $totalSamples = count($allSamples);
                        
                        // Smart Split Logic
                        $splitAt = 5; // Default for small batches
                        if ($totalSamples > 10) {
                            $splitAt = ceil($totalSamples / 2);
                        }
                        
                        $col1 = array_slice($allSamples, 0, $splitAt);
                        $col2 = array_slice($allSamples, $splitAt);
                        ?>
                        
                        <div class="sample-column">
                            <?php foreach ($col1 as $sn): ?>
                                <span><?= htmlspecialchars($sn) ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="sample-column">
                            <?php foreach ($col2 as $sn): ?>
                                <span><?= htmlspecialchars($sn) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="right-content" colspan="2">
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
                <td class="right-content" colspan="2" style="font-weight: bold;">
                    Parameters to be analyzed:
                </td>
            </tr>

            <!-- Sample Details & Parameter Table -->
            <?php $paramCount = is_array($data['parameters'] ?? null) ? count($data['parameters']) : 0; ?>
            <tr>
            <td class="left-label" rowspan="<?= max(1, $paramCount) ?>">
                    <div style="margin-bottom: 12pt;">
                        <strong>Sample Details:</strong><br><br>
                        Volume/ Weight: <?= htmlspecialchars($data['volume_weight']) ?>
                    </div>
                    <div style="margin-bottom: 12pt;">
                        <strong>Sampling date:</strong> <?= htmlspecialchars($data['received_date']) ?>
                    </div>
                    <div>
                        <strong>Remarks:</strong>
                    </div>
                </td>
                <?php if ($paramCount > 0): $p = $data['parameters'][0]; ?>
                <td class="param-num">
                    <?= '1. ' . formatParameterWithHighlighting($p['parameter_name'], $p['display_format'] ?? 'normal', $p['is_selected'], $p['selected_variants']) ?>
                </td>
                <td class="param-value <?= $p['is_selected'] ? 'param-selected' : '' ?>">
                    <?php 
                    $methodList = explode(',', $p['methods'] ?? '');
                    $wrappedMethods = [];
                    foreach ($methodList as $m) {
                        $trimmedM = trim($m);
                        if ($trimmedM !== '') {
                            $text = htmlspecialchars($trimmedM);
                            if ($p['is_selected']) $text = "<u>$text</u>";
                            $wrappedMethods[] = '<span style="display: inline-block; white-space: nowrap;">' . $text . '</span>';
                        }
                    }
                    echo implode(', ', $wrappedMethods);
                    ?>
                </td>
                <?php else: ?>
                <td class="param-num"></td>
                <td class="param-value"></td>
                <?php endif; ?>
            </tr>
            <?php for ($i = 1; $i < $paramCount; $i++): $p = $data['parameters'][$i]; ?>
            <tr>
                <td class="param-num">
                    <?= ($i + 1) . '. ' . formatParameterWithHighlighting($p['parameter_name'], $p['display_format'] ?? 'normal', $p['is_selected'], $p['selected_variants']) ?>
                </td>
                <td class="param-value <?= $p['is_selected'] ? 'param-selected' : '' ?>">
                    <?php 
                    $methodList = explode(',', $p['methods'] ?? '');
                    $wrappedMethods = [];
                    foreach ($methodList as $m) {
                        $trimmedM = trim($m);
                        if ($trimmedM !== '') {
                            $text = htmlspecialchars($trimmedM);
                            if ($p['is_selected']) $text = "<u>$text</u>";
                            $wrappedMethods[] = '<span style="display: inline-block; white-space: nowrap;">' . $text . '</span>';
                        }
                    }
                    echo implode(', ', $wrappedMethods);
                    ?>
                </td>
            </tr>
            <?php endfor; ?>

            <!-- Analysis Start Date -->
            <tr>
                <td class="left-label">
                    <strong>Analysis to be started on:</strong><br>
                    Date: <br /> By: _______________
                </td>
                <td class="right-content" colspan="2"></td>
            </tr>

            <!-- Report Submission Date (EMPTY) -->
            <tr>
                <td class="left-label">Report submission date:</td>
                <td class="right-content" colspan="2"><!-- EMPTY - for manual entry --></td>
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

</html>