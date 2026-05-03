<?php

/**
 * Test Report Model
 * Laboratory Management System
 *
 * Handles all database operations for final test report generation:
 * - Loading completed samples eligible for reports
 * - Aggregating sample + items + tests + results + units + methods
 * - Category-aware unit/method resolution
 * - Accreditation status determination
 * - Report generation with data snapshots
 * - Report retrieval for printing/reprinting
 *
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class TestReportModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ==================== COMPLETED SAMPLES LIST ====================

    /**
     * Get all samples eligible for report generation.
     * Shows only Completed samples with all results entered.
     *
     * @param array $filters Optional search, date filters
     * @return array
     */
    public function getCompletedSamples($filters = [])
    {
        $sql = "SELECT 
                    s.sample_id,
                    s.sample_code,
                    s.report_ref,
                    s.status,
                    s.received_date,
                    s.analysis_end_date,
                    s.submission_type,
                    c.client_name,
                    c.address_line1 AS client_address,
                    COUNT(DISTINCT si.sample_item_id) AS item_count,
                    COUNT(DISTINCT st.sample_test_id) AS test_count,
                    COUNT(DISTINCT str.result_id) AS result_count,
                    -- Check if reports already exist (may be multiple for mixed/separate)
                    GROUP_CONCAT(DISTINCT ftr.report_id ORDER BY ftr.report_id ASC) AS existing_report_ids,
                    MAX(ftr.generated_at) AS last_generated
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                INNER JOIN sample_items si ON si.sample_id = s.sample_id
                INNER JOIN sample_tests st ON st.sample_item_id = si.sample_item_id
                LEFT JOIN sample_test_results str ON str.sample_test_id = st.sample_test_id
                LEFT JOIN final_test_reports ftr ON ftr.sample_id = s.sample_id AND ftr.is_deleted = 0
                WHERE s.status = 'Completed'";

        $params = [];
        $types = '';

        // Search filter
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (s.sample_code LIKE ? OR c.client_name LIKE ? OR s.report_ref LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        // Date filter
        if (!empty($filters['date_preset'])) {
            switch ($filters['date_preset']) {
                case 'today':
                    $sql .= " AND DATE(s.received_date) = CURDATE()";
                    break;
                case 'last7':
                    $sql .= " AND s.received_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'last30':
                    $sql .= " AND s.received_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
        }

        $sql .= " GROUP BY s.sample_id
                   HAVING result_count = test_count
                   ORDER BY s.sample_id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $samples = [];
        while ($row = $result->fetch_assoc()) {
            $samples[] = [
                'sample_id'          => (int) $row['sample_id'],
                'sample_code'        => $row['sample_code'],
                'report_ref'         => $row['report_ref'],
                'status'             => $row['status'],
                'received_date'      => $row['received_date'],
                'submission_type'    => $row['submission_type'],
                'client_name'        => $row['client_name'],
                'client_address'     => $row['client_address'],
                'item_count'         => (int) $row['item_count'],
                'test_count'         => (int) $row['test_count'],
                'result_count'       => (int) $row['result_count'],
                'existing_report_ids' => $row['existing_report_ids'] ?? null,
                'last_generated'     => $row['last_generated']
            ];
        }

        return $samples;
    }

    /**
     * Get the name of the currently active accreditation certificate.
     * 
     * @return string
     */
    public function getActiveCertificateName()
    {
        try {
            $sql = "SELECT certificate_name FROM accreditation_certificates WHERE is_current = 1 AND is_deleted = 0 LIMIT 1";
            $result = $this->conn->query($sql);
            if ($row = $result->fetch_assoc()) {
                return $row['certificate_name'];
            }
            return 'NARA Accreditation'; // Fallback
        } catch (Exception $e) {
            return 'NARA Accreditation';
        }
    }

    // ==================== REPORT DATA AGGREGATION ====================

    /** 
     * Get complete report data for a sample.
     * Aggregates all info needed to render the report template.
     *
     * @param int $sampleId
     * @return array|null
     */
    public function getReportData($sampleId)
    {
        // 1. Load sample header with client info
        $headerSql = "SELECT 
                        s.sample_id, s.sample_code, s.form_number, 
                        s.report_ref, s.status, s.submission_type,
                        s.received_date, s.received_time, s.tentative_date,
                        s.sample_collected_date, s.sample_collected_time,
                        s.analysis_start_date, s.analysis_end_date,
                        s.is_drawn_by_nara,
                        c.client_name, c.address_line1 AS client_address, 
                        c.phone_primary AS client_phone,
                        ci.city_name
                      FROM samples s
                      INNER JOIN clients c ON s.client_id = c.client_id
                      LEFT JOIN cities ci ON c.city_id = ci.city_id
                      WHERE s.sample_id = ? AND s.status = 'Completed'";

        $stmt = $this->conn->prepare($headerSql);
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();

        if (!$header) {
            return null;
        }

        // Load active certificate name
        $header['active_certificate_name'] = $this->getActiveCertificateName();

        // 2. Load sample items with category info, container, and accreditation status
        $itemsSql = "SELECT 
                        si.sample_item_id,
                        si.sample_name,
                        si.value AS sample_value,
                        si.unit AS sample_unit,
                        si.client_sample_code,
                        si.sampling_location,
                        si.container_damage,
                        si.temperature_condition,
                        si.temperature_value,
                        si.container_item_id,
                        ei.item_name AS container_name,
                        si.sequence_number,
                        si.sample_category_id,
                        COALESCE(stc.category_name, 'Unknown') AS category_name,
                        COALESCE(stc.category_code, 'UNK') AS category_code,
                        COALESCE(stc.base_category_id, NULL) AS base_category_id,
                        COALESCE(buc.category_name, '') AS base_category_name,
                        -- Accreditation status from sample_names
                        COALESCE(sn.is_slab_accredited, 0) AS is_slab_accredited
                     FROM sample_items si
                     LEFT JOIN extra_items ei ON si.container_item_id = ei.item_id
                     LEFT JOIN sample_type_categories stc ON si.sample_category_id = stc.category_id
                     LEFT JOIN base_unit_categories buc ON stc.base_category_id = buc.base_category_id
                     LEFT JOIN sample_names sn ON si.sample_name = sn.sample_name
                     WHERE si.sample_id = ?
                     ORDER BY si.sequence_number ASC";

        $stmt2 = $this->conn->prepare($itemsSql);
        $stmt2->bind_param('i', $sampleId);
        $stmt2->execute();
        $itemsResult = $stmt2->get_result();

        $items = [];
        $allTestParams = []; // Collect all unique parameter+variant combos across items

        while ($item = $itemsResult->fetch_assoc()) {
            // Resolve category if NULL
            if (empty($item['base_category_id'])) {
                $catLookup = $this->resolveCategoryForItem($item['sample_name']);
                if ($catLookup) {
                    $item['category_name'] = $catLookup['category_name'];
                    $item['category_code'] = $catLookup['category_code'];
                    $item['base_category_id'] = $catLookup['base_category_id'];
                    $item['base_category_name'] = $catLookup['base_category_name'] ?? '';
                }
            }

            // Load tests with results for this item
            $item['tests'] = $this->getTestsForReport(
                $item['sample_item_id'],
                $item['base_category_id'],
                $itemsResult->num_rows
            );

            $items[] = $item;
        }

        // 3. Semantic Sorting: Group identical samples together for professional reports.
        //    Items with the same name+temperature cluster together, so when paginated,
        //    all "Drinking Water" samples appear on the same page(s) rather than scattered.
        usort($items, function ($a, $b) {
            $nameA = strtolower(trim($a['sample_name'] ?? ''));
            $nameB = strtolower(trim($b['sample_name'] ?? ''));
            if ($nameA !== $nameB) {
                return strcmp($nameA, $nameB);
            }
            $tempA = strtolower(trim($a['temperature_condition'] ?? ''));
            $tempB = strtolower(trim($b['temperature_condition'] ?? ''));
            if ($tempA !== $tempB) {
                return strcmp($tempA, $tempB);
            }
            return ($a['sequence_number'] ?? 0) - ($b['sequence_number'] ?? 0);
        });

        // 4. Determine report type
        $reportType = $this->determineReportType($items);

        // 4. Get logos
        $logos = $this->getLogosForReport($reportType);

        // 5. Get certificate info
        $certificate = $this->getActiveCertificate();

        // 6. Build customer's request text
        $customerRequest = $this->buildCustomerRequestText($items);

        // 7. Build sample details text
        $sampleDetails = $this->buildSampleDetailsText($items);

        // 8. Build per-item isolated data for Separate report mode
        //    Each item gets its OWN customer_request and sample_details,
        //    so that when layout_type='single', each page only sees its own data.
        foreach ($items as &$itemRef) {
            $itemRef['isolated_customer_request'] = $this->buildCustomerRequestText([$itemRef]);
            $itemRef['isolated_sample_details']   = $this->buildSampleDetailsText([$itemRef]);
        }
        unset($itemRef); // Break the reference to avoid accidental mutation

        return [
            'sample'           => $header,
            'items'            => $items,
            'report_type'      => $reportType,
            'logos'             => $logos,
            'certificate'      => $certificate,
            'customer_request' => $customerRequest,
            'sample_details'   => $sampleDetails
        ];
    }

    /**
     * Get tests with results for a sample item, resolved by category.
     * This is the core query for report data — resolves units and methods
     * based on the item's base_category_id.
     *
     * @param int $sampleItemId
     * @param int|null $baseCategoryId
     * @param int $itemCount Number of items in the report (for abbreviation logic)
     * @return array
     */
    private function getTestsForReport($sampleItemId, $baseCategoryId, $itemCount = 1)
    {
        $sql = "SELECT 
                    st.sample_test_id,
                    st.parameter_id,
                    st.variant_id,
                    st.test_method_id,
                    st.is_swab,

                    -- Parameter info
                    tp.parameter_name,
                    tp.parameter_code,
                    tp.short_name,
                    tp.display_format,
                    tp.result_mode,
                    tp.espc_applicable,

                    -- Variant info
                    pv.variant_name,

                    -- Method from sample_tests (selected at submission)
                    tm.method_name AS submitted_method,

                    -- Category-resolved unit and method
                    bu.unit_name AS category_unit,
                    pbc.is_slab_accredited AS param_accredited,
                    
                    -- Category-resolved method (may differ from submitted)
                    pcm.method_id AS category_method_id,
                    tm_cat.method_name AS category_method,
                    tm_cat.method_abbreviation AS category_method_abbr,

                    -- Results
                    str.result_id,
                    str.result_mode AS saved_result_mode,
                    str.result_value,
                    str.has_espc,
                    str.result_display

                FROM sample_tests st
                INNER JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                LEFT JOIN parameter_variants pv ON st.variant_id = pv.variant_id
                LEFT JOIN test_methods tm ON st.test_method_id = tm.method_id";

        // Category-resolved joins
        if ($baseCategoryId) {
            $sql .= " LEFT JOIN parameter_base_unit_config pbc 
                        ON pbc.parameter_id = tp.parameter_id 
                        AND pbc.base_category_id = ?
                        AND pbc.is_active = 1
                      LEFT JOIN base_units bu ON pbc.base_unit_id = bu.base_unit_id
                      LEFT JOIN parameter_category_methods pcm 
                        ON pcm.config_id = pbc.config_id
                        AND pcm.is_primary = 1
                      LEFT JOIN test_methods tm_cat ON pcm.method_id = tm_cat.method_id";
        } else {
            $sql .= " LEFT JOIN parameter_base_unit_config pbc ON 0=1
                      LEFT JOIN base_units bu ON 0=1
                      LEFT JOIN parameter_category_methods pcm ON 0=1
                      LEFT JOIN test_methods tm_cat ON 0=1";
        }

        $sql .= " LEFT JOIN sample_test_results str ON str.sample_test_id = st.sample_test_id
                   WHERE st.sample_item_id = ?
                   ORDER BY tp.parameter_name ASC, pv.variant_name ASC";

        $stmt = $this->conn->prepare($sql);
        if ($baseCategoryId) {
            $stmt->bind_param('ii', $baseCategoryId, $sampleItemId);
        } else {
            $stmt->bind_param('i', $sampleItemId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $tests = [];

        while ($row = $result->fetch_assoc()) {
            // Handle common technical abbreviations automatically
            $originalName = $row['parameter_name'];
            if (empty($row['short_name'])) {
                if (stripos($originalName, 'Aerobic Plate Count') !== false) $row['short_name'] = 'APC';
                if (stripos($originalName, 'Escherichia coli') !== false) $row['short_name'] = 'E. coli';
            }

            // Apply table-density abbreviations to the display name
            if (stripos($originalName, 'Aerobic Plate Count') !== false) {
                $row['parameter_name'] = str_ireplace('Aerobic Plate Count', 'APC', $originalName);
            }
            if (stripos($originalName, 'Escherichia coli') !== false) {
                $row['parameter_name'] = str_ireplace('Escherichia coli', 'E. coli', $originalName);
            }

            // Logic for Method Abbreviation based on density
            $fullMethod = $row['category_method'] ?: $row['submitted_method'];
            $abbrMethod = $row['category_method_abbr'] ?? '';

            $methodName = $fullMethod;
            if ($itemCount >= 7) {
                $methodName = $abbrMethod ?: $fullMethod;
            } elseif ($itemCount >= 5) {
                $methodName = ($abbrMethod && strlen($fullMethod) > 20) ? $abbrMethod : $fullMethod;
            }

            // Build display label for parameter
            $paramLabel = $this->buildParameterLabel($row, $itemCount);

            // Format result for display
            $resultDisplay = $this->formatResultForReport($row);

            $tests[] = [
                'sample_test_id'    => (int) $row['sample_test_id'],
                'parameter_id'      => (int) $row['parameter_id'],
                'parameter_code'    => $row['parameter_code'],
                'parameter_name'    => $originalName, // Full Name for footer
                'display_name'      => $row['parameter_name'], // Abbreviated for table
                'short_name'        => $row['short_name'] ?? '',
                'parameter_label'   => $paramLabel,
                'display_format'    => $row['display_format'] ?: 'normal',
                'variant_name'      => $row['variant_name'] ?: '',
                'method_name'       => $methodName ?: '',
                'unit_name'         => $row['category_unit'] ?: '',
                'is_accredited'     => (int) ($row['param_accredited'] ?? 0),
                'result_mode'       => $row['result_mode'],
                'espc_applicable'   => (int) ($row['espc_applicable'] ?? 0),
                'is_swab'           => (int) ($row['is_swab'] ?? 0),
                'result'            => [
                    'result_id'      => $row['result_id'] ? (int) $row['result_id'] : null,
                    'result_value'   => $row['result_value'],
                    'has_espc'       => (int) ($row['has_espc'] ?? 0),
                    'result_display' => $row['result_display'] ?: '',
                    'formatted'      => $resultDisplay
                ]
            ];
        }

        return $tests;
    }

    private function buildParameterLabel($row, $itemCount = 1)
    {
        // Use short_name if density is high (4+ columns)
        $name = ($itemCount >= 4 && !empty($row['short_name'])) ? $row['short_name'] : $row['parameter_name'];
        $unit = $row['category_unit'] ?: '';
        $variant = $row['variant_name'] ?: '';

        // Build: "Parameter Name (Unit)"
        $label = $name;
        if ($unit) {
            $label .= ' (' . $unit . ')';
        }

        // Add variant: " 37 °C"
        if ($variant) {
            $label .= ' ' . $variant;
        }

        // Convert ^X notation to HTML superscripts
        return $this->formatSuperscripts($label);
    }

    /**
     * Convert caret notation to HTML superscripts.
     * e.g., "cm^2" → "cm<sup>2</sup>", "10^3" → "10<sup>3</sup>"
     */
    private function formatSuperscripts($text)
    {
        return preg_replace('/\^(\d+)/', '<sup>$1</sup>', $text);
    }

    /**
     * Format a result value for the report.
     *
     * @param array $row Database row with result info
     * @return string
     */
    private function formatResultForReport($row)
    {
        if (!$row['result_id']) {
            return '—'; // No result yet
        }

        $resultMode = $row['result_mode'] ?: $row['saved_result_mode'];

        if ($resultMode === 'present_or_absent') {
            return $row['result_value'] ?: '—';
        }

        // numeric_or_ND mode — check if result_value indicates ND
        $value = $row['result_value'] ?? '';
        if (strtoupper(trim($value)) === 'ND' || $value === '') {
            return 'ND';
        }

        $display = $row['result_display'] ?: $value ?: '—';

        // Aggressively remove all whitespace around 'x' or '×' for compact scientific notation
        $display = preg_replace('/\s*([x×])\s*/u', '$1', $display);

        // Append ESPC if applicable (Only if not already present in display)
        if ((int) ($row['has_espc'] ?? 0) && strpos($display, 'ESPC') === false) {
            $display = $display . '<sup class="espc-sup">ESPC</sup>';
        }

        return $display;
    }

    // ==================== REPORT TYPE DETERMINATION ====================

    /**
     * Determine if report is accredited or non-accredited.
     * Based on sample_names.is_slab_accredited for the items.
     *
     * @param array $items Array of sample items with is_slab_accredited field
     * @return string 'accredited' or 'non_accredited'
     */
    public function determineReportType($items)
    {
        foreach ($items as $item) {
            if ((int) ($item['is_slab_accredited'] ?? 0)) {
                return 'accredited';
            }
        }
        return 'non_accredited';
    }

    // ==================== LOGOS ====================

    /**
     * Get logos for a specific report type.
     *
     * @param string $reportType 'accredited' or 'non_accredited'
     * @return array
     */
    public function getLogosForReport($reportType)
    {
        $sql = "SELECT logo_id, logo_name, logo_type, file_path, display_order
                FROM report_logos
                WHERE is_active = 1";

        if ($reportType === 'non_accredited') {
            $sql .= " AND is_for_accredited = 0";
        }

        $sql .= " ORDER BY display_order ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $logos = [];
        while ($row = $result->fetch_assoc()) {
            $logos[] = $row;
        }
        return $logos;
    }

    // ==================== CERTIFICATE ====================

    /**
     * Get the currently active accreditation certificate.
     *
     * @return array|null
     */
    public function getActiveCertificate()
    {
        $sql = "SELECT certificate_id, certificate_code AS certificate_number, 
                       scope_description, valid_from, valid_to AS valid_until
                FROM accreditation_certificates
                WHERE is_current = 1 AND status = 'active'
                ORDER BY valid_from DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ==================== HELPER: Category Resolution ====================

    /**
     * Resolve category for an item by looking up sample_names → sample_type_categories.
     *
     * @param string $sampleName
     * @return array|null
     */
    private function resolveCategoryForItem($sampleName)
    {
        $sql = "SELECT 
                    stc.category_name, stc.category_code, stc.base_category_id,
                    buc.category_name AS base_category_name
                FROM sample_names sn
                INNER JOIN sample_type_categories stc ON sn.category_id = stc.category_id
                LEFT JOIN base_unit_categories buc ON stc.base_category_id = buc.base_category_id
                WHERE sn.sample_name = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $sampleName);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ==================== HELPER: Build Text Fields ====================

    public function buildCustomerRequestText($items)
    {
        // 1. Group variants by parameter
        $paramGroups = [];
        foreach ($items as $item) {
            if (empty($item['tests'])) continue;
            foreach ($item['tests'] as $test) {
                $pName = trim($test['parameter_name']);
                $sName = trim($test['short_name'] ?? '');
                $vName = trim($test['variant_name'] ?? '');

                if (!isset($paramGroups[$pName])) {
                    $paramGroups[$pName] = [
                        'short_name' => $sName,
                        'display_format' => $test['display_format'] ?? 'normal',
                        'variants'   => []
                    ];
                }
                if ($vName !== '' && !in_array($vName, $paramGroups[$pName]['variants'])) {
                    $paramGroups[$pName]['variants'][] = $vName;
                }
            }
        }

        if (empty($paramGroups)) {
            return '';
        }

        // 2. Format each parameter group
        $formattedParams = [];
        foreach ($paramGroups as $pName => $info) {
            $label = htmlspecialchars($pName);
            if ($info['display_format'] === 'scientific') {
                $label = '<em>' . $label . '</em>';
            }

            // Handle variants first
            if (!empty($info['variants'])) {
                $vStr = '';
                $count = count($info['variants']);
                if ($count === 1) {
                    $vStr = htmlspecialchars($info['variants'][0]);
                } else {
                    $escapedVariants = array_map('htmlspecialchars', $info['variants']);
                    $last = array_pop($escapedVariants);
                    $vStr = implode(', ', $escapedVariants) . ' and ' . $last;
                }
                $label .= ' (' . $vStr . ')';
            }
            // Only add short name if NO variants (to avoid double parentheses clutter)
            elseif ($info['short_name'] !== '') {
                $label .= ' (' . htmlspecialchars($info['short_name']) . ')';
            }

            $formattedParams[] = $label;
        }

        // 3. Combine into final sentence
        $finalStr = '';
        $pCount = count($formattedParams);
        if ($pCount === 1) {
            $finalStr = $formattedParams[0];
        } else {
            $lastP = array_pop($formattedParams);
            $finalStr = implode(', ', $formattedParams) . ' and ' . $lastP;
        }

        return 'To test samples for ' . $finalStr . '.';
    }

    /**
     * Build dynamic sample descriptions and client sample codes table.
     * Enforces the Single, Plural, and Mixed Category layout scenarios.
     *
     * @param array $items
     * @return array [ 'descriptions' => string[], 'codes_table' => array ]
     */
    public function buildSampleDetailsText($items)
    {
        if (empty($items)) {
            return ['descriptions' => [], 'codes_table' => []];
        }

        $groups = [];
        $codesTable = [];
        $hasAnyCodes = false;
        $isSwab = false;

        // Detect if this is a swab submission by checking all items' categories
        $swabLocationCounts = [];
        foreach ($items as $it) {
            $cat = trim($it['base_category_name'] ?? '');
            if ($cat === 'Surface Swab') {
                $isSwab = true;
                $loc = trim($it['sampling_location'] ?? '');
                if ($loc !== '') {
                    $swabLocationCounts[$loc] = ($swabLocationCounts[$loc] ?? 0) + 1;
                }
            }
        }

        // For swab: find the most common sampling_location
        $swabSourceLocation = '';
        if ($isSwab && !empty($swabLocationCounts)) {
            arsort($swabLocationCounts);
            $swabSourceLocation = array_key_first($swabLocationCounts);
        }

        foreach ($items as $index => $it) {
            $name = trim($it['sample_name']);
            $tempCond = trim($it['temperature_condition'] ?? '');
            $category = trim($it['base_category_name'] ?? 'Water and Ice');

            // Use composite key: name + temperature to isolate different conditions
            // e.g., "Croaker fish_Chilled" vs "Croaker fish_Frozen"
            $groupKey = $name . '_' . $tempCond;

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'name'        => $name,
                    'count'       => 0,
                    'value'       => $it['sample_value'],
                    'unit'        => $it['sample_unit'],
                    'container'   => strtolower($it['container_name'] ?: 'container'),
                    'temperature' => $tempCond,
                    'category'    => $category
                ];
            }
            $groups[$groupKey]['count']++;

            // Build the code table row — null if no client code
            $clientCode = trim($it['client_sample_code'] ?? '');
            $codeValue = $clientCode !== '' ? $clientCode : null;
            if ($codeValue !== null) {
                $hasAnyCodes = true;
            }
            $codesTable[] = [
                'index'    => $index + 1,
                'name'     => $name,
                'code'     => $codeValue,
                'location' => $name  // For swabs: sample_name IS the swabbing location
            ];
        }

        $isMultiple = count($items) > 1;
        $descriptions = [];

        // Build descriptions based on group counts and category type
        foreach ($groups as $groupKey => $g) {
            $count = $g['count'];
            $name = $g['name'];
            $valueStr = ($g['value'] && $g['unit']) ? "(~ {$g['value']} {$g['unit']})" : "";
            $category = $g['category'];

            if ($category === 'Surface Swab') {
                // ==================== SURFACE SWAB CATEGORY ====================
                // Format: "[Count] swab samples from a [most_common_location]."
                // Single: "Swab sample from a [location]."
                $fromStr = !empty($swabSourceLocation)
                    ? ' from a ' . strtolower($swabSourceLocation)
                    : '';

                if ($count === 1) {
                    $desc = "Swab sample{$fromStr}.";
                } else {
                    $word = $this->numberToWord($count);
                    $desc = "{$word} swab samples{$fromStr}.";
                }
                $desc = ucfirst(trim($desc));
            } elseif ($category === 'Food') {
                // ==================== FOOD CATEGORY ====================
                $tempRaw = strtolower($g['temperature']);
                $tempStr = !empty($tempRaw) ? $tempRaw . ' ' : '';

                if ($count === 1) {
                    $desc = "{$tempStr}{$name} sample {$valueStr} in a {$g['container']}.";
                } else {
                    $word = $this->numberToWord($count);
                    $pluralContainer = $this->pluralizeContainer($g['container']);
                    $desc = "{$word} {$tempStr}{$name} samples {$valueStr} in {$pluralContainer}.";
                }
                $desc = ucfirst(trim($desc));
            } else {
                // ==================== WATER AND ICE / DEFAULT ====================
                if ($count === 1) {
                    $desc = ucfirst(strtolower($name)) . " sample {$valueStr} in a {$g['container']}.";
                } else {
                    $word = $this->numberToWord($count);
                    $pluralContainer = $this->pluralizeContainer($g['container']);
                    $desc = "$word " . strtolower($name) . " samples {$valueStr} in {$pluralContainer}.";
                    $desc = ucfirst($desc);
                }
            }

            // Clean up any double spaces that might occur if valueStr is empty
            $descriptions[] = preg_replace('/\s+/', ' ', $desc);
        }

        return [
            'descriptions'  => $descriptions,
            'codes_table'   => $codesTable,
            'has_any_codes' => $hasAnyCodes,
            'is_multiple'   => $isMultiple,
            'is_swab'       => $isSwab
        ];
    }

    // ==================== SIGNATORIES ====================

    /**
     * Get active signatories.
     *
     * @param string|null $roleType Filter by 'scientist' or 'head'
     * @return array
     */
    public function getSignatories($roleType = null)
    {
        $sql = "SELECT signatory_id, full_name, title, division, role_type, 
                       is_default, display_order
                FROM report_signatories
                WHERE is_active = 1 AND is_deleted = 0";

        if ($roleType) {
            $sql .= " AND role_type = ?";
        }
        $sql .= " ORDER BY display_order ASC, full_name ASC";

        $stmt = $this->conn->prepare($sql);
        if ($roleType) {
            $stmt->bind_param('s', $roleType);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $signatories = [];
        while ($row = $result->fetch_assoc()) {
            $signatories[] = $row;
        }
        return $signatories;
    }

    /**
     * Get default signatories (pre-selected for new reports).
     *
     * @return array ['scientist' => ..., 'head' => ...]
     */
    public function getDefaultSignatories()
    {
        $all = $this->getSignatories();
        $defaults = ['scientist' => null, 'head' => null];

        foreach ($all as $sig) {
            if ($sig['is_default'] && !$defaults[$sig['role_type']]) {
                $defaults[$sig['role_type']] = $sig;
            }
        }

        return $defaults;
    }

    // ==================== REPORT GENERATION ====================

    /**
     * Generate and save a new report.
     *
     * @param array $data [sample_id, report_type, layout_type, signatory_left_id, 
     *                      signatory_right_id, item_positions, generated_by]
     * @return int[]|false Array of generated report IDs, or false on failure
     */
    public function generateReport($data)
    {
        $this->conn->begin_transaction();

        try {
            $sampleId = (int) $data['sample_id'];
            $layoutType = $data['layout_type'] ?? 'single';
            $leftId = !empty($data['signatory_left_id']) ? (int) $data['signatory_left_id'] : null;
            $rightId = !empty($data['signatory_right_id']) ? (int) $data['signatory_right_id'] : null;
            $generatedBy = (int) $data['generated_by'];

            // Get report reference from sample and prefix with QC/
            $refSql = "SELECT sample_code FROM samples WHERE sample_id = ?";
            $refStmt = $this->conn->prepare($refSql);
            $refStmt->bind_param('i', $sampleId);
            $refStmt->execute();
            $refRow = $refStmt->get_result()->fetch_assoc();
            $reportNumber = $refRow['sample_code'] ?? 'TR-' . $sampleId;

            // Build signatory snapshot
            $sigSnapshot = $this->conn->real_escape_string(''); // placeholder
            $sigSnapshot = $this->buildSignatorySnapshot($leftId, $rightId);

            // Update analysis dates if provided
            if (!empty($data['analysis_start_date']) || !empty($data['analysis_end_date'])) {
                $updateSql = "UPDATE samples SET 
                                analysis_start_date = COALESCE(?, analysis_start_date),
                                analysis_end_date = COALESCE(?, analysis_end_date),
                                is_drawn_by_nara = ?
                              WHERE sample_id = ?";
                $updateStmt = $this->conn->prepare($updateSql);
                $startDate = $data['analysis_start_date'] ?: null;
                $endDate = $data['analysis_end_date'] ?: null;
                $drawnByNara = (int) ($data['is_drawn_by_nara'] ?? 0);
                $updateStmt->bind_param('ssii', $startDate, $endDate, $drawnByNara, $sampleId);
                $updateStmt->execute();
            }

            // Build full data snapshot for reprint (MUST BE DONE AFTER UPDATE)
            $reportData = $this->getReportData($sampleId);

            // --- Enable Regeneration: Wipe ALL old reports for this sample ---
            $findOldReportSql = "SELECT report_id FROM final_test_reports WHERE sample_id = ?";
            $findStmt = $this->conn->prepare($findOldReportSql);
            $findStmt->bind_param('i', $sampleId);
            $findStmt->execute();
            $oldReportResult = $findStmt->get_result();
            while ($oldReport = $oldReportResult->fetch_assoc()) {
                $oldReportId = $oldReport['report_id'];

                // Clear old items
                $delItemsSql = "DELETE FROM report_items WHERE report_id = ?";
                $delItemsStmt = $this->conn->prepare($delItemsSql);
                $delItemsStmt->bind_param('i', $oldReportId);
                $delItemsStmt->execute();

                // Clear old report
                $delReportSql = "DELETE FROM final_test_reports WHERE report_id = ?";
                $delReportStmt = $this->conn->prepare($delReportSql);
                $delReportStmt->bind_param('i', $oldReportId);
                $delReportStmt->execute();
            }

            // ==================== REPORT GENERATION LOGIC ====================
            $items = $reportData['items'] ?? [];
            $generatedReportIds = [];

            if ($layoutType === 'single') {
                // ==================== SEPARATE MODE ====================
                // Each item gets its own independent report record.
                // Each item's accreditation status determines its report_type.
                $romanNumerals = [
                    'I',
                    'II',
                    'III',
                    'IV',
                    'V',
                    'VI',
                    'VII',
                    'VIII',
                    'IX',
                    'X',
                    'XI',
                    'XII',
                    'XIII',
                    'XIV',
                    'XV',
                    'XVI',
                    'XVII',
                    'XVIII',
                    'XIX',
                    'XX'
                ];

                foreach ($items as $idx => $item) {
                    $isAcc = (int) ($item['is_slab_accredited'] ?? 0);
                    $itemReportType = $isAcc ? 'accredited' : 'non_accredited';

                    // Build isolated report data for this single item
                    $itemReportData = $reportData;
                    $itemReportData['items'] = [$item];
                    $itemReportData['report_type'] = $itemReportType;
                    $itemReportData['logos'] = $this->getLogosForReport($itemReportType);
                    $itemReportData['customer_request'] = $item['isolated_customer_request']
                        ?? $this->buildCustomerRequestText([$item]);
                    $itemReportData['sample_details'] = $item['isolated_sample_details']
                        ?? $this->buildSampleDetailsText([$item]);

                    // Report number suffix: /I, /II, /III...
                    $suffix = $romanNumerals[$idx] ?? ($idx + 1);
                    $itemReportNumber = $reportNumber . '/' . $suffix;
                    $itemSnapshot = json_encode($itemReportData, JSON_UNESCAPED_UNICODE);

                    $itemReportId = $this->insertSingleReport(
                        $sampleId,
                        $itemReportNumber,
                        $itemReportType,
                        'single',
                        $leftId,
                        $rightId,
                        $sigSnapshot,
                        $itemSnapshot,
                        $generatedBy,
                        [$item],
                        $data['item_positions'] ?? []
                    );
                    $generatedReportIds[] = $itemReportId;
                }
            } else {
                // ==================== COMBINED MODE ====================
                // Detect mixed accreditation: split into 2 groups if mixed.
                $accreditedItems = [];
                $nonAccreditedItems = [];

                foreach ($items as $item) {
                    if ((int) ($item['is_slab_accredited'] ?? 0)) {
                        $accreditedItems[] = $item;
                    } else {
                        $nonAccreditedItems[] = $item;
                    }
                }

                $hasAcc = !empty($accreditedItems);
                $hasNonAcc = !empty($nonAccreditedItems);
                $isMixed = ($hasAcc && $hasNonAcc);

                if ($isMixed) {
                    // --- Report 1: Accredited items only ---
                    $accReportData = $reportData;
                    $accReportData['items'] = $accreditedItems;
                    $accReportData['report_type'] = 'accredited';
                    $accReportData['logos'] = $this->getLogosForReport('accredited');
                    $accReportData['customer_request'] = $this->buildCustomerRequestText($accreditedItems);
                    $accReportData['sample_details'] = $this->buildSampleDetailsText($accreditedItems);

                    $accReportNumber = $reportNumber . '-A';
                    $accSnapshot = json_encode($accReportData, JSON_UNESCAPED_UNICODE);

                    $accReportId = $this->insertSingleReport(
                        $sampleId,
                        $accReportNumber,
                        'accredited',
                        $layoutType,
                        $leftId,
                        $rightId,
                        $sigSnapshot,
                        $accSnapshot,
                        $generatedBy,
                        $accreditedItems,
                        $data['item_positions'] ?? []
                    );
                    $generatedReportIds[] = $accReportId;

                    // --- Report 2: Non-accredited items only ---
                    $nonAccReportData = $reportData;
                    $nonAccReportData['items'] = $nonAccreditedItems;
                    $nonAccReportData['report_type'] = 'non_accredited';
                    $nonAccReportData['logos'] = $this->getLogosForReport('non_accredited');
                    $nonAccReportData['customer_request'] = $this->buildCustomerRequestText($nonAccreditedItems);
                    $nonAccReportData['sample_details'] = $this->buildSampleDetailsText($nonAccreditedItems);

                    $nonAccReportNumber = $reportNumber . '-NA';
                    $nonAccSnapshot = json_encode($nonAccReportData, JSON_UNESCAPED_UNICODE);

                    $nonAccReportId = $this->insertSingleReport(
                        $sampleId,
                        $nonAccReportNumber,
                        'non_accredited',
                        $layoutType,
                        $leftId,
                        $rightId,
                        $sigSnapshot,
                        $nonAccSnapshot,
                        $generatedBy,
                        $nonAccreditedItems,
                        $data['item_positions'] ?? []
                    );
                    $generatedReportIds[] = $nonAccReportId;
                } else {
                    // --- Standard: all items in one report ---
                    $reportType = $data['report_type'];
                    $dataSnapshot = json_encode($reportData, JSON_UNESCAPED_UNICODE);

                    $reportId = $this->insertSingleReport(
                        $sampleId,
                        $reportNumber,
                        $reportType,
                        $layoutType,
                        $leftId,
                        $rightId,
                        $sigSnapshot,
                        $dataSnapshot,
                        $generatedBy,
                        $items,
                        $data['item_positions'] ?? []
                    );
                    $generatedReportIds[] = $reportId;
                }
            }

            $this->conn->commit();
            return $generatedReportIds; // Always returns array of IDs
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("TestReportModel - generateReport Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert a single report row + its item positions.
     * Extracted as a helper so it can be called once (standard) or twice (mixed).
     *
     * @return int The newly inserted report_id
     */
    private function insertSingleReport(
        $sampleId,
        $reportNumber,
        $reportType,
        $layoutType,
        $leftId,
        $rightId,
        $sigSnapshot,
        $dataSnapshot,
        $generatedBy,
        $reportItems,
        $allItemPositions
    ) {
        $insertSql = "INSERT INTO final_test_reports 
                      (sample_id, report_number, report_type, layout_type,
                       signatory_left_id, signatory_right_id, signatory_snapshot,
                       report_data_snapshot, generated_by)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $insertStmt = $this->conn->prepare($insertSql);
        $sigSnapshotJson = json_encode($sigSnapshot, JSON_UNESCAPED_UNICODE);
        $insertStmt->bind_param(
            'isssiissi',
            $sampleId,
            $reportNumber,
            $reportType,
            $layoutType,
            $leftId,
            $rightId,
            $sigSnapshotJson,
            $dataSnapshot,
            $generatedBy
        );
        $insertStmt->execute();
        $reportId = $this->conn->insert_id;

        // Insert report items (only positions belonging to THIS report's items)
        $reportItemIds = array_column($reportItems, 'sample_item_id');
        if (!empty($allItemPositions) && is_array($allItemPositions)) {
            $itemSql = "INSERT INTO report_items 
                        (report_id, sample_item_id, page_number, column_position) 
                        VALUES (?, ?, ?, ?)";
            $itemStmt = $this->conn->prepare($itemSql);

            $colCounter = 0;
            foreach ($allItemPositions as $pos) {
                $siId = (int) $pos['sample_item_id'];
                // Only insert positions that belong to this report's items
                if (in_array($siId, $reportItemIds)) {
                    $colCounter++;
                    $page = (int) (ceil($colCounter / 5)); // Re-paginate for split
                    $col = (int) ((($colCounter - 1) % 5) + 1);
                    $itemStmt->bind_param('iiii', $reportId, $siId, $page, $col);
                    $itemStmt->execute();
                }
            }
        }

        return $reportId;
    }

    /**
     * Build signatory snapshot for storage.
     */
    private function buildSignatorySnapshot($leftId, $rightId)
    {
        $snapshot = ['left' => null, 'right' => null];

        if ($leftId) {
            $sql = "SELECT full_name, title, division, role_type FROM report_signatories WHERE signatory_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $leftId);
            $stmt->execute();
            $snapshot['left'] = $stmt->get_result()->fetch_assoc();
        }

        if ($rightId) {
            $sql = "SELECT full_name, title, division, role_type FROM report_signatories WHERE signatory_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $rightId);
            $stmt->execute();
            $snapshot['right'] = $stmt->get_result()->fetch_assoc();
        }

        return $snapshot;
    }

    // ==================== RETRIEVE SAVED REPORTS ====================

    /**
     * Get a saved report by ID.
     *
     * @param int $reportId
     * @return array|null
     */
    public function getSavedReport($reportId)
    {
        $sql = "SELECT ftr.*, 
                       u.fullname AS generated_by_name,
                       s.sample_code, s.report_ref
                FROM final_test_reports ftr
                INNER JOIN users u ON ftr.generated_by = u.user_id
                INNER JOIN samples s ON ftr.sample_id = s.sample_id
                WHERE ftr.report_id = ? AND ftr.is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();

        if ($report) {
            $report['signatory_snapshot'] = json_decode($report['signatory_snapshot'], true);
            $report['report_data_snapshot'] = json_decode($report['report_data_snapshot'], true);
        }

        return $report;
    }

    /**
     * Increment print count for a report.
     *
     * @param int $reportId
     * @return bool
     */
    public function incrementPrintCount($reportId)
    {
        $sql = "UPDATE final_test_reports 
                SET print_count = print_count + 1, last_printed_at = NOW() 
                WHERE report_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $reportId);
        return $stmt->execute();
    }

    // ==================== DYNAMIC DESCRIPTION HELPERS ====================

    /**
     * Convert numbers 1-30 to capitalized English words.
     */
    private function numberToWord($num)
    {
        $words = [
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            21 => 'Twenty-one',
            22 => 'Twenty-two',
            23 => 'Twenty-three',
            24 => 'Twenty-four',
            25 => 'Twenty-five',
            26 => 'Twenty-six',
            27 => 'Twenty-seven',
            28 => 'Twenty-eight',
            29 => 'Twenty-nine',
            30 => 'Thirty'
        ];
        return isset($words[$num]) ? $words[$num] : (string)$num;
    }

    /**
     * Convert container names to their plural equivalents.
     */
    private function pluralizeContainer($containerText)
    {
        $text = strtolower(trim($containerText));
        // Add known plural replacements here
        $replacements = [
            'bottle' => 'bottles',
            'bag'    => 'bags',
            'box'    => 'boxes',
            'packet' => 'packets',
            'tube'   => 'tubes',
            'jar'    => 'jars',
            'vial'   => 'vials'
        ];

        foreach ($replacements as $singular => $plural) {
            // Replaces the exact word boundary
            $pattern = '/\b' . preg_quote($singular, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                return preg_replace($pattern, $plural, $text);
            }
        }

        // Fallback: If no recognized singular noun, append an 's' to the very end
        return $text . 's';
    }

    // ==================== DESTRUCTOR ====================

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
