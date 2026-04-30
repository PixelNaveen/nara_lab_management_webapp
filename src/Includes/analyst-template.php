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
    <link rel="stylesheet" href="../public/assets/css/analyst-template.css">
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