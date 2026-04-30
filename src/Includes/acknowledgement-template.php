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

// Adaptive page size (Now forced to A4 Portrait as per user request for perfection)
$pageSize = 'A4 portrait';
$pageWidth = '210mm';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Acknowledgement Form - <?= htmlspecialchars($data['report_ref']) ?></title>
    <link rel="stylesheet" href="../public/assets/css/acknowledgement-template.css">
</head>

<body class="<?= $scalingClass ?>">
    <div class="form-container">
        <div class="form-title">Sample Acknowledgement Form</div>

        <div class="main-table-wrapper">
            <table style="table-layout: fixed; width: 100%;">
                <colgroup>
                    <col style="width: 30%;">
                    <col style="width: 35%;">
                    <col style="width: 35%;">
                </colgroup>
                <!-- Date Received & Time -->
                <tr class="date-row">
                    <td class="label-cell" colspan="2">
                        <strong>Date Received:</strong> <?= htmlspecialchars($data['received_date']) ?>
                    </td>
                    <td class="content-cell">
                        <strong>Time:</strong> <?= htmlspecialchars(($data['received_time'] ?? '') ?: '_________________') ?>
                    </td>
                </tr>

                <!-- Sample Details & Parameter Table -->
                <?php $paramCount = max(1, is_array($data['parameters'] ?? null) ? count($data['parameters']) : 0); ?>
                <tr class="sample-info-row">
                    <td class="label-cell" rowspan="<?= $paramCount ?>">
                        <span class="sample-info-label">Sample Information</span>
                        <?php
                        $sampleInfo = $data['sample_information'] ?? '';
                        $sampleNames = !empty($sampleInfo) ? explode(', ', $sampleInfo) : [];
                        $sampleCount = count($sampleNames);

                        $densityClass = '';
                        if ($sampleCount > 20) {
                            $densityClass = 'high-density-list'; // 2 columns for 21-30 samples
                        }
                        ?>
                        <div class="sample-info-content <?= $densityClass ?>">
                            <?php foreach ($sampleNames as $index => $name): ?>
                                <span style="display: block; break-inside: avoid;">
                                    <?= htmlspecialchars($name) . ($index < $sampleCount - 1 ? ',' : '') ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <?php if (!empty($data['parameters'])): $p = $data['parameters'][0]; ?>
                        <td class="content-cell param-col">
                            <strong>1.</strong> <?= formatParameterWithHighlighting($p['parameter_name'], $p['display_format'] ?? 'normal', $p['is_selected'], $p['selected_variants']) ?>
                        </td>
                        <td class="content-cell method-col <?= $p['is_selected'] ? 'param-selected' : '' ?>">
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
                        <td class="content-cell" colspan="2"></td>
                    <?php endif; ?>
                </tr>

                <?php for ($i = 1; $i < $paramCount; $i++): $p = $data['parameters'][$i]; ?>
                    <tr class="sample-info-row">
                        <td class="content-cell param-col">
                            <strong><?= ($i + 1) ?>.</strong> <?= formatParameterWithHighlighting($p['parameter_name'], $p['display_format'] ?? 'normal', $p['is_selected'], $p['selected_variants']) ?>
                        </td>
                        <td class="content-cell method-col <?= $p['is_selected'] ? 'param-selected' : '' ?>">
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
                        <strong>Receipt No:</strong> <?= htmlspecialchars($data['receipt_no']) ?>
                        <?php if (!empty($data['payment_date'])): ?>
                            <br>
                            <strong>Date:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($data['payment_date']))) ?>
                        <?php endif; ?>
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