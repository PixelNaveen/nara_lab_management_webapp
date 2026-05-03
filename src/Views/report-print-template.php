<?php
session_start();
// Basic Auth Check
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

require_once __DIR__ . '/../Models/TestReportModel.php';

$model = new TestReportModel();

// ==================== PARSE REPORT IDS ====================
// Supports: ?report_ids=1,2,3 (multiple) or ?report_id=1 (single) or ?sample_id=1 (preview)
$reportIdsParam = trim($_GET['report_ids'] ?? '');
$singleReportId = intval($_GET['report_id'] ?? 0);
$sampleId = intval($_GET['sample_id'] ?? 0);

$reportEntries = []; // Each entry: ['reportData' => ..., 'savedReport' => ...]

if (!empty($reportIdsParam)) {
    // Multiple report IDs (comma-separated)
    $ids = array_filter(array_map('intval', explode(',', $reportIdsParam)));
    foreach ($ids as $rid) {
        $saved = $model->getSavedReport($rid);
        if ($saved) {
            $reportEntries[] = [
                'reportData'  => $saved['report_data_snapshot'],
                'savedReport' => $saved
            ];
        }
    }
    if (empty($reportEntries)) {
        die("No valid reports found.");
    }
} elseif ($singleReportId > 0) {
    // Single saved report (backward compatible)
    $saved = $model->getSavedReport($singleReportId);
    if (!$saved) {
        die("Report not found or has been deleted.");
    }
    $reportEntries[] = [
        'reportData'  => $saved['report_data_snapshot'],
        'savedReport' => $saved
    ];
} elseif ($sampleId > 0) {
    // Live preview from raw sample data
    $data = $model->getReportData($sampleId);
    if (!$data) {
        die("Report data not found or sample not completed.");
    }
    $reportEntries[] = [
        'reportData'  => $data,
        'savedReport' => null
    ];
} else {
    die("Invalid request. Missing report_ids, report_id, or sample_id.");
}

// Use first report's sample code for the page title
$firstSample = $reportEntries[0]['reportData']['sample'] ?? [];
$pageTitle = htmlspecialchars($firstSample['sample_code'] ?? 'NARA');

// Always use the current server date as the "printed" date
$reportDate = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/assets/css/report-print.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Floating Print Button (Hidden in Print) -->
    <button class="floating-print-btn no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print Report
    </button>

    <?php foreach ($reportEntries as $reportEntryIndex => $reportEntry):
        $reportData = $reportEntry['reportData'];
        $savedReport = $reportEntry['savedReport'];

        if (!$reportData || !isset($reportData['sample'])) continue;

        $sample = $reportData['sample'];
        $customerName = htmlspecialchars($sample['client_name'] ?? 'N/A');
        $customerAddress = htmlspecialchars($sample['client_address'] ?? 'N/A');
        $customerCity = htmlspecialchars($sample['city_name'] ?? '');

        // Extract dynamic sample descriptions and codes table
        $sampleDetails = $reportData['sample_details'] ?? ['descriptions' => [], 'codes_table' => []];
        $sampleDescriptions = $sampleDetails['descriptions'] ?? [];
        $sampleCodesTable = $sampleDetails['codes_table'] ?? [];
        $sampleHasAnyCodes = $sampleDetails['has_any_codes'] ?? false;
        $sampleIsMultiple = $sampleDetails['is_multiple'] ?? false;
        $sampleIsSwab = $sampleDetails['is_swab'] ?? false;

        // Signatories logic
        $signatories = [];
        if (!empty($savedReport['signatory_snapshot'])) {
            $signatories = $savedReport['signatory_snapshot'];
        } else {
            // Preview mode: fetch from default signatories in reportData
            $signatories = [
                'left'  => $reportData['signatories']['defaults']['scientist'] ?? null,
                'right' => $reportData['signatories']['defaults']['head'] ?? null
            ];
        }

        // Determine report type (accredited vs non_accredited)
        $reportType = $savedReport['report_type'] ?? $reportData['report_type'] ?? 'accredited';
        $isNonAccredited = ($reportType === 'non_accredited');

        // Determine layout type
        $layoutType = $savedReport['layout_type'] ?? 'combined';
        if ($layoutType === 'multi_column') {
            $layoutType = 'combined';
        }

        // Pagination
        $allItems = $reportData['items'] ?? [];
        $totalItems = count($allItems);
        if ($layoutType === 'single') {
            $itemsPerPage = 1;
            $totalPages = $totalItems;
        } else {
            $itemsPerPage = 5;
            $totalPages = max(1, ceil($totalItems / $itemsPerPage));
        }

        // Date formatting
        $samplingDate = !empty($sample['sample_collected_date']) ? date('M d, Y', strtotime($sample['sample_collected_date'])) : 'N/A';
        $receiptDate = !empty($sample['received_date']) ? date('M d, Y', strtotime($sample['received_date'])) : 'N/A';

        $analysisStart = !empty($sample['analysis_start_date']) ? strtotime($sample['analysis_start_date']) : null;
        $analysisEnd = !empty($sample['analysis_end_date']) ? strtotime($sample['analysis_end_date']) : null;

        $analysisDateStr = 'N/A';
        if ($analysisStart && $analysisEnd) {
            if (date('Y-m-d', $analysisStart) === date('Y-m-d', $analysisEnd)) {
                $analysisDateStr = date('M d, Y', $analysisStart);
            } elseif (date('Y-m', $analysisStart) === date('Y-m', $analysisEnd)) {
                $analysisDateStr = date('M d', $analysisStart) . ' - ' . date('d, Y', $analysisEnd);
            } elseif (date('Y', $analysisStart) === date('Y', $analysisEnd)) {
                $analysisDateStr = date('M d', $analysisStart) . ' - ' . date('M d, Y', $analysisEnd);
            } else {
                $analysisDateStr = date('M d, Y', $analysisStart) . ' - ' . date('M d, Y', $analysisEnd);
            }
        } elseif ($analysisStart) {
            $analysisDateStr = date('M d, Y', $analysisStart);
        }
    ?>

        <?php for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++):
            // Slice items and codes for this page
            $pageOffset = ($currentPage - 1) * $itemsPerPage;
            $pageItems = array_slice($allItems, $pageOffset, $itemsPerPage);
            $pageCodesTable = array_slice($sampleCodesTable, $pageOffset, $itemsPerPage);

            // === PAGE-AWARE CONTEXT SWITCH ===
            // EVERY page generates its OWN sample descriptions, customer request,
            // and codes table from only the items physically on that page.
            if ($layoutType === 'single' && count($pageItems) === 1) {
                // --- SEPARATE MODE: use pre-computed isolated data from model ---
                $currentItem = $pageItems[0];
                $iso = $currentItem['isolated_sample_details'] ?? null;
                if ($iso) {
                    $pageSampleDescriptions = $iso['descriptions'] ?? [];
                    $pageCodesTable         = $iso['codes_table'] ?? [];
                    $pageSampleHasAnyCodes  = $iso['has_any_codes'] ?? false;
                    $pageSampleIsMultiple   = $iso['is_multiple'] ?? false;
                    $pageSampleIsSwab       = $iso['is_swab'] ?? false;
                } else {
                    $pageSampleDescriptions = $sampleDescriptions;
                    $pageCodesTable         = array_slice($sampleCodesTable, $pageOffset, $itemsPerPage);
                    $pageSampleHasAnyCodes  = $sampleHasAnyCodes;
                    $pageSampleIsMultiple   = $sampleIsMultiple;
                    $pageSampleIsSwab       = $sampleIsSwab;
                }
                $pageCustomerRequest = $currentItem['isolated_customer_request'] ?? ($reportData['customer_request'] ?? '');
            } else {
                // --- COMBINED MODE: dynamically generate page-specific summaries ---
                // Call the same builder functions with ONLY the items on this page.
                // This guarantees the "Samples:" header and "Customer's request:" text
                // exactly match the items physically present on this paper.
                $pageDetails = $model->buildSampleDetailsText($pageItems);
                $pageSampleDescriptions = $pageDetails['descriptions'] ?? [];
                $pageCodesTable         = $pageDetails['codes_table'] ?? [];
                $pageSampleHasAnyCodes  = $pageDetails['has_any_codes'] ?? false;
                $pageSampleIsMultiple   = $pageDetails['is_multiple'] ?? false;
                $pageSampleIsSwab       = $pageDetails['is_swab'] ?? false;
                $pageCustomerRequest    = $model->buildCustomerRequestText($pageItems);
            }

            // Dynamic Layout Density Calculation
            $rowCount = 0;
            $pageUniqueTests = [];
            foreach ($pageItems as $item) {
                if (empty($item['tests'])) continue;
                foreach ($item['tests'] as $test) {
                    $label = $test['parameter_label'] ?? $test['parameter_name'];
                    $method = $test['method_name'] ?? '-';
                    $key = md5($label . '::' . $method);
                    $pageUniqueTests[$key] = true;
                }
            }
            $rowCount = count($pageUniqueTests);

            $densityClass = 'layout-normal';
            if ($rowCount <= 4) {
                $densityClass = 'layout-sparse';
            } elseif ($rowCount <= 8) {
                $densityClass = 'layout-relaxed';
            } elseif ($rowCount <= 13) {
                $densityClass = 'layout-normal';
            } else {
                $densityClass = 'layout-compact';
            }
        ?>
            <?php
            // Page break: add after every page EXCEPT the very last page of the very last report
            $isLastPageOfThisReport = ($currentPage >= $totalPages);
            $isLastReport = ($reportEntryIndex >= count($reportEntries) - 1);
            $needsPageBreak = !($isLastPageOfThisReport && $isLastReport);
            ?>
            <div class="report-container <?php echo $densityClass; ?><?php echo $needsPageBreak ? ' page-break-after' : ''; ?>">
                <header class="report-header"></header>

                <!-- Report Body -->
                <main class="report-body">
                    <div class="page-indicator">Page <?php echo ($layoutType === 'single') ? '1' : $currentPage; ?> of <?php echo ($layoutType === 'single') ? '1' : $totalPages; ?></div>

                    <div class="report-title">TEST REPORT</div>

                    <div class="report-meta">
                        <?php
                        // Use stored report_number for saved reports (e.g. "26/014/001/I" or "26/014/001-A")
                        // Fall back to sample_code for live previews
                        if (!empty($savedReport['report_number'])) {
                            $displaySampleCode = $savedReport['report_number'];
                            // Strip internal -A and -NA suffixes from the visual display
                            $displaySampleCode = preg_replace('/-NA$/', '', $displaySampleCode);
                            $displaySampleCode = preg_replace('/-A$/', '', $displaySampleCode);
                        } else {
                            $displaySampleCode = $sample['sample_code'] ?? '';
                        }
                        
                        // Append Roman numeral if the report spans multiple pages
                        // This applies to multi-page Combined reports AND live previews of Single reports
                        if ($totalPages > 1) {
                            $romanNumerals = [
                                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V',
                                6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
                                11 => 'XI', 12 => 'XII', 13 => 'XIII', 14 => 'XIV', 15 => 'XV',
                                16 => 'XVI', 17 => 'XVII', 18 => 'XVIII', 19 => 'XIX', 20 => 'XX'
                            ];
                            $romanSuffix = $romanNumerals[$currentPage] ?? $currentPage;
                            $displaySampleCode .= '/' . $romanSuffix;
                        }
                        ?>
                        <div class="meta-row"><span class="font-bold lab-ref">LAB Ref.:</span> <?php echo htmlspecialchars($displaySampleCode); ?></div>
                        <div class="meta-row"><?php echo $reportDate; ?></div>
                    </div>

                    <div class="info-section">
                        <div class="info-left">
                            <div class="info-row">
                                <span class="font-bold label customer-section-label">Customer:</span>
                                <div class="info-details customer-section-details">
                                    <span class="customer-name"><?php echo $customerName; ?>,</span><br>
                                    <span class="customer-address">
                                        <?php
                                        $fullAddress = trim(($customerAddress ?? '') . ', ' . ($customerCity ?? ''), ', ');
                                        $addressParts = explode(',', $fullAddress);
                                        echo implode(',<br>', array_map('trim', $addressParts));
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="info-right">
                            <div class="info-row">
                                <span class="font-bold label lab-section-label">Laboratory:</span>
                                <div class="info-details lab-section-details">
                                    <span class="lab-name">Quality Control Laboratory</span><br>
                                    <span class="lab-unit">(Microbiology unit)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section sample-details">
                        <div class="info-left">
                            <div class="info-row">
                                <span class="font-bold label">Samples:</span>
                                <div class="info-details">
                                    <?php if (count($pageSampleDescriptions) === 1): ?>
                                        <?php echo htmlspecialchars($pageSampleDescriptions[0]); ?>
                                    <?php else: ?>
                                        <?php foreach ($pageSampleDescriptions as $di => $desc): ?>
                                            <?php echo ($di + 1) . '.' . htmlspecialchars($desc); ?><br>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($pageSampleIsSwab): ?>
                                <table class="sample-code-table swab-location-table">
                                    <tr class="swab-table-header">
                                        <td><strong>Swabbing location</strong></td>
                                        <td><strong>Sample No.</strong></td>
                                    </tr>
                                    <?php foreach ($pageCodesTable as $codeRow): ?>
                                        <tr>
                                            <td><?php echo $codeRow['index']; ?>.<?php echo htmlspecialchars($codeRow['location']); ?></td>
                                            <td><?php echo str_pad($codeRow['index'], 2, '0', STR_PAD_LEFT); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php elseif ($pageSampleIsMultiple || $pageSampleHasAnyCodes): ?>
                                <table class="sample-code-table">
                                    <?php foreach ($pageCodesTable as $codeRow): ?>
                                        <tr>
                                            <td class="sample-code-bullet">&bull;</td>
                                            <td class="sample-code-name"><?php echo htmlspecialchars($codeRow['name']); ?></td>
                                            <?php if ($codeRow['code'] !== null): ?>
                                                <td class="sample-code-separator">&nbsp;-&nbsp;</td>
                                                <td class="sample-code-value"><?php echo htmlspecialchars($codeRow['code']); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php endif; ?>
                        </div>
                        <div class="info-right">
                            <div class="info-row">
                                <span class="label date-label">Date of sampling:</span>
                                <div class="info-details date-details"><?php echo $samplingDate; ?></div>
                            </div>
                            <div class="info-row">
                                <span class="label date-label">Receipt of sample:</span>
                                <div class="info-details date-details"><?php echo $receiptDate; ?></div>
                            </div>
                            <div class="info-row">
                                <span class="label date-label">Analysis of samples:</span>
                                <div class="info-details date-details"><?php echo $analysisDateStr; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section request-details">
                        <div class="info-left">
                            <div class="info-row">
                                <span class="font-bold label">Customer's request:</span>
                                <div class="info-details"><?php echo $pageCustomerRequest; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="results-section">
                        <div class="results-title">Results:</div>
                        <?php
                        $itemCount = count($pageItems);
                        $colClass = 'cols-' . $itemCount;
                        $tableClass = 'main-results-table ' . $colClass;
                        if ($itemCount === 1) {
                            $tableClass .= ' single-column';
                        }
                        ?>
                        <table class="<?php echo $tableClass; ?>">
                            <thead>
                                <?php
                                if ($itemCount === 1):
                                ?>
                                    <tr>
                                        <th>Parameters</th>
                                        <th>Methods</th>
                                        <th>Results</th>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th rowspan="2">Parameters</th>
                                        <th rowspan="2">Methods</th>
                                        <th colspan="<?php echo $itemCount; ?>">Results</th>
                                    </tr>
                                    <tr>
                                        <?php for ($c = 1; $c <= $itemCount; $c++): ?>
                                            <th><?php echo $c; ?></th>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php
                                // Group unique tests (Parameter + Method) across all items
                                $uniqueTests = [];

                                // Tracking flags for dynamic definitions footer
                                $hasND = false;
                                $hasAccredited = false;
                                $hasESPC = false;
                                $accreditedStandard = $sample['active_certificate_name'] ?? 'Accredited Lab';
                                $pageAbbreviations = []; // Collect used abbreviations for this page only

                                foreach ($pageItems as $item) {
                                    if (empty($item['tests'])) continue;
                                    foreach ($item['tests'] as $test) {
                                        // Unique key based on parameter label and method
                                        $label = $test['parameter_label'] ?? $test['parameter_name'];
                                        $method = $test['method_name'] ?? '-';
                                        $key = md5($label . '::' . $method);

                                         $is_accredited = $test['is_accredited'] ?? 0;
                                        if ($is_accredited && !$isNonAccredited) {
                                            $hasAccredited = true;
                                        }

                                        // Track used short names for footer definitions
                                        if (!empty($test['short_name'])) {
                                            $pageAbbreviations[$test['short_name']] = $test['parameter_name'];
                                        }

                                        if (!isset($uniqueTests[$key])) {
                                            $uniqueTests[$key] = [
                                                'label' => $label,
                                                'method' => $method,
                                                'is_accredited' => $is_accredited,
                                                'display_format' => $test['display_format'] ?? 'normal',
                                                'results' => array_fill(0, $itemCount, '-') // Initialize exact number of blank columns
                                            ];
                                        }
                                    }
                                }

                                // Map results into their respective columns (0 to $itemCount - 1)
                                foreach ($pageItems as $colIndex => $item) {
                                    if (empty($item['tests'])) {
                                        foreach ($uniqueTests as $key => &$testRow) {
                                            $testRow['results'][$colIndex] = 'NT'; // Not Tested
                                        }
                                        continue;
                                    }

                                    foreach ($item['tests'] as $test) {
                                        $label = $test['parameter_label'] ?? $test['parameter_name'];
                                        $method = $test['method_name'] ?? '-';
                                        $key = md5($label . '::' . $method);

                                        $resultFormatted = $test['result']['formatted'] ?? '-';
                                        $uniqueTests[$key]['results'][$colIndex] = $resultFormatted;

                                        // Track ESPC and ND flags
                                        if (($test['result']['has_espc'] ?? 0) || strpos($resultFormatted, 'ESPC') !== false) {
                                            $hasESPC = true;
                                        }
                                        if (strpos($resultFormatted, 'ND') !== false) {
                                            $hasND = true;
                                        }
                                    }
                                }

                                // Ensure final table rows are sorted alphabetically by parameter
                                usort($uniqueTests, function ($a, $b) {
                                    return strcasecmp($a['label'], $b['label']);
                                });

                                if (empty($uniqueTests)): ?>
                                    <tr>
                                        <td colspan="<?php echo 2 + $itemCount; ?>" class="text-center font-bold py-3">No results recorded</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($uniqueTests as $row): ?>
                                        <tr>
                                            <td class="px-2">
                                                <?php 
                                                    $displayLabel = htmlspecialchars($row['label']);
                                                    // Restore specific technical tags after escaping
                                                    $displayLabel = str_replace(
                                                        ['&lt;sup&gt;', '&lt;/sup&gt;', '&lt;sub&gt;', '&lt;/sub&gt;', '&lt;i&gt;', '&lt;/i&gt;', '&lt;em&gt;', '&lt;/em&gt;'],
                                                        ['<sup>', '</sup>', '<sub>', '</sub>', '<i>', '</i>', '<em>', '</em>'],
                                                        $displayLabel
                                                    );
                                                    
                                                    if (($row['display_format'] ?? 'normal') === 'scientific') {
                                                        $displayLabel = '<em>' . $displayLabel . '</em>';
                                                    }
                                                    echo $displayLabel;
                                                    if (!$isNonAccredited && $row['is_accredited']) echo '<sup>*</sup>'; 
                                                ?>
                                            </td>
                                            <td class="px-2">
                                                <?php 
                                                    $displayMethod = htmlspecialchars($row['method']);
                                                    // Restore specific technical tags after escaping
                                                    echo str_replace(
                                                        ['&lt;sup&gt;', '&lt;/sup&gt;', '&lt;sub&gt;', '&lt;/sub&gt;', '&lt;i&gt;', '&lt;/i&gt;', '&lt;em&gt;', '&lt;/em&gt;'],
                                                        ['<sup>', '</sup>', '<sub>', '</sub>', '<i>', '</i>', '<em>', '</em>'],
                                                        $displayMethod
                                                    );
                                                ?>
                                            </td>
                                            <?php for ($i = 0; $i < $itemCount; $i++): ?>
                                                 <td class="text-center"><?php echo str_replace('ESPC', '<sup class="espc-sup">ESPC</sup>', $row['results'][$i]); ?></td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($hasND || $hasAccredited || $hasESPC || !empty($pageAbbreviations)): ?>
                            <div class="results-notes">
                                <?php if ($hasND): ?>
                                    <div>ND - Not Detected</div>
                                <?php endif; ?>
                                <?php if ($hasAccredited): ?>
                                    <div>* - <?php echo htmlspecialchars($accreditedStandard); ?></div>
                                <?php endif; ?>
                                <?php if ($hasESPC): ?>
                                    <div class="espc-note">ESPC - Estimated plate count</div>
                                <?php endif; ?>
                                <?php 
                                // Sort abbreviations alphabetically for a cleaner look
                                ksort($pageAbbreviations);
                                foreach ($pageAbbreviations as $abbr => $full): ?>
                                    <div><?php echo htmlspecialchars($abbr); ?> - <?php echo htmlspecialchars($full); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="results-disclaimer">
                        Results of the analysis specifically refer to the samples <?php echo (!empty($sample['is_drawn_by_nara']) && $sample['is_drawn_by_nara'] == 1) ? 'drawn by' : 'submitted to'; ?> NARA.
                    </div>

                    <div class="signature-section">
                        <?php foreach (['left', 'right'] as $pos): 
                            $sig = $signatories[$pos] ?? null;
                            if (!$sig) continue;
                        ?>
                        <div class="signature-block">
                            <div class="signature-space"></div>
                            <div class="signatory-name"><?php echo htmlspecialchars($sig['full_name']); ?></div>
                            <div class="signatory-title"><?php echo htmlspecialchars($sig['title']); ?></div>
                            <div class="signatory-title"><?php echo htmlspecialchars($sig['division']); ?></div>
                            <?php if (($sig['role_type'] ?? '') === 'scientist'): ?>
                                <div class="signatory-title">(Authorized signatory)</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-content-placeholder">
                        <!-- Additional content if needed -->
                    </div>
                </main>

                <footer class="report-footer"></footer>
            </div>
        <?php endfor; ?>
    <?php endforeach; ?>
</body>

</html>