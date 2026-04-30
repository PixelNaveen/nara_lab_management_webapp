<?php

/**
 * Result Entry Model
 * Laboratory Management System
 *
 * Handles all database operations for test result entry:
 * - Loading samples for the result entry table
 * - Loading form data (items + tests with category matching)
 * - Upserting result rows
 * - Checking completion status
 * - Marking samples as completed
 *
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class ResultEntryModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // ========================================================
    // FETCH SAMPLES FOR TABLE
    // ========================================================

    /**
     * Get all samples for the result entry table.
     * Shows samples that have at least one sample_test.
     *
     * @param array $filters search, status, date_preset
     * @return array
     */
    public function getSamplesForResults($filters = [])
    {
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? 'all';
        $datePreset = $filters['date_preset'] ?? '';

        $sql = "SELECT 
                    s.sample_id,
                    s.sample_code,
                    s.form_number,
                    s.status,
                    s.received_date,
                    s.tentative_date,
                    s.grand_total,
                    c.client_name,
                    ci.city_name,
                    COUNT(DISTINCT si.sample_item_id) AS item_count,
                    COUNT(DISTINCT st.sample_test_id) AS test_count,
                    COUNT(DISTINCT str.result_id) AS result_count
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                LEFT JOIN cities ci ON s.city_id = ci.city_id
                LEFT JOIN sample_items si ON si.sample_id = s.sample_id
                LEFT JOIN sample_tests st ON st.sample_item_id = si.sample_item_id
                LEFT JOIN sample_test_results str ON str.sample_test_id = st.sample_test_id
                WHERE s.status != 'Cancelled'";

        $params = [];
        $types = '';

        // Search filter
        if (!empty($search)) {
            $sql .= " AND (s.sample_code LIKE ? OR c.client_name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // Status filter
        if ($status !== 'all' && $status !== '') {
            $sql .= " AND s.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        // Date preset filter
        if (!empty($datePreset)) {
            switch ($datePreset) {
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

        $sql .= " GROUP BY s.sample_id ORDER BY s.received_date DESC, s.sample_id DESC";

        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $samples = [];

        while ($row = $result->fetch_assoc()) {
            $samples[] = $row;
        }

        return $samples;
    }

    // ========================================================
    // GET FORM DATA (for result entry modal)
    // ========================================================

    /**
     * Load sample header + items + tests for the result entry form.
     * Items include tests with parameter config matched to item category.
     *
     * @param int $sampleId
     * @return array|null
     */
    public function getFormData($sampleId)
    {
        // 1. Load sample header
        $headerSql = "SELECT 
                        s.sample_id, s.sample_code, s.form_number, s.status,
                        s.received_date, s.tentative_date,
                        s.analysis_start_date, s.analysis_end_date,
                        c.client_name, ci.city_name
                      FROM samples s
                      INNER JOIN clients c ON s.client_id = c.client_id
                      LEFT JOIN cities ci ON s.city_id = ci.city_id
                      WHERE s.sample_id = ?";

        $stmt = $this->conn->prepare($headerSql);
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();

        if (!$header) {
            return null;
        }

        // 2. Load sample items
        $itemsSql = "SELECT 
                        si.sample_item_id,
                        si.sample_name,
                        si.sequence_number,
                        si.sample_category_id,
                        COALESCE(stc.category_name, 'Unknown') AS category_name,
                        COALESCE(stc.category_code, 'UNK') AS category_code,
                        COALESCE(stc.base_category_id, NULL) AS base_category_id
                     FROM sample_items si
                     LEFT JOIN sample_type_categories stc ON si.sample_category_id = stc.category_id
                     WHERE si.sample_id = ?
                     ORDER BY si.sequence_number ASC";

        $stmt2 = $this->conn->prepare($itemsSql);
        $stmt2->bind_param('i', $sampleId);
        $stmt2->execute();
        $itemsResult = $stmt2->get_result();

        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            // If sample_category_id is NULL, try to resolve from sample_names
            if (empty($item['base_category_id'])) {
                $catLookup = $this->resolveCategoryForItem($item['sample_name']);
                if ($catLookup) {
                    $item['category_name'] = $catLookup['category_name'];
                    $item['category_code'] = $catLookup['category_code'];
                    $item['base_category_id'] = $catLookup['base_category_id'];
                }
            }

            // 3. Load tests for this item
            $item['tests'] = $this->getTestsForItem(
                $item['sample_item_id'],
                $item['base_category_id']
            );

            $items[] = $item;
        }

        return [
            'sample' => $header,
            'items' => $items
        ];
    }

    /**
     * Resolve the category for a sample item by looking up sample_names.
     * Used when sample_category_id is NULL (old submissions).
     *
     * @param string $sampleName
     * @return array|null
     */
    private function resolveCategoryForItem($sampleName)
    {
        $sql = "SELECT 
                    stc.category_name,
                    stc.category_code,
                    stc.base_category_id
                FROM sample_names sn
                INNER JOIN sample_type_categories stc ON sn.category_id = stc.category_id
                WHERE LOWER(sn.sample_name) = LOWER(?)
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $sampleName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    /**
     * Get all tests for a sample_item, with parameter details,
     * unit info from parameter_base_unit_config (matched by base_category_id),
     * and any existing result from sample_test_results.
     *
     * @param int $sampleItemId
     * @param int|null $baseCategoryId
     * @return array
     */
    private function getTestsForItem($sampleItemId, $baseCategoryId)
    {
        $sql = "SELECT 
                    st.sample_test_id,
                    st.sample_item_id,
                    st.parameter_id,
                    st.variant_id,
                    st.test_method_id,
                    st.charge,

                    -- Parameter info
                    tp.parameter_name,
                    tp.short_name,
                    tp.display_format,
                    tp.result_mode,
                    tp.espc_applicable,

                    -- Variant info
                    pv.variant_name,

                    -- Method info
                    tm.method_name,

                    -- Unit info (from category config)
                    bu.unit_name AS category_unit_name,
                    pbc.is_slab_accredited AS config_slab_accredited,

                    -- Existing result
                    str.result_id,
                    str.result_mode AS saved_result_mode,
                    str.result_value,
                    str.has_espc AS saved_has_espc,
                    str.result_display

                FROM sample_tests st
                INNER JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                LEFT JOIN parameter_variants pv ON st.variant_id = pv.variant_id
                LEFT JOIN test_methods tm ON st.test_method_id = tm.method_id";

        // Join parameter_base_unit_config only if we have a category
        if ($baseCategoryId) {
            $sql .= " LEFT JOIN parameter_base_unit_config pbc 
                        ON pbc.parameter_id = tp.parameter_id 
                        AND pbc.base_category_id = ?
                        AND pbc.is_active = 1
                      LEFT JOIN base_units bu ON pbc.base_unit_id = bu.base_unit_id";
        } else {
            $sql .= " LEFT JOIN parameter_base_unit_config pbc ON 0=1
                      LEFT JOIN base_units bu ON 0=1";
        }

        $sql .= " LEFT JOIN sample_test_results str ON str.sample_test_id = st.sample_test_id
                   WHERE st.sample_item_id = ?
                   ORDER BY tp.parameter_code ASC, pv.variant_name ASC";

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
            $tests[] = [
                'sample_test_id'    => (int) $row['sample_test_id'],
                'sample_item_id'    => (int) $row['sample_item_id'],
                'parameter_id'      => (int) $row['parameter_id'],
                'variant_id'        => $row['variant_id'] ? (int) $row['variant_id'] : null,
                'parameter_name'    => $row['parameter_name'],
                'short_name'        => $row['short_name'] ?: '',
                'display_format'    => $row['display_format'] ?: 'normal',
                'variant_name'      => $row['variant_name'] ?: '',
                'method_name'       => $row['method_name'] ?: '',
                'unit_name'         => $row['category_unit_name'] ?: '',
                'result_mode'       => $row['result_mode'] ?: 'numeric_or_ND',
                'espc_applicable'   => (int) ($row['espc_applicable'] ?? 0),
                'existing_result'   => $row['result_id'] ? [
                    'result_id'          => (int) $row['result_id'],
                    'result_value'       => $row['result_value'],
                    'has_espc'           => (int) $row['saved_has_espc'],
                    'result_display'     => $row['result_display']
                ] : null
            ];
        }

        return $tests;
    }

    // ========================================================
    // SAVE RESULTS
    // ========================================================

    /**
     * Upsert a single test result.
     * Uses INSERT ... ON DUPLICATE KEY UPDATE on sample_test_id.
     *
     * @param array $data
     * @return bool
     */
    public function upsertResult($data)
    {
        $sql = "INSERT INTO sample_test_results 
                    (sample_test_id, sample_item_id, parameter_id, variant_id,
                     result_mode, result_value, has_espc, result_display, entered_by, entered_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    result_mode = VALUES(result_mode),
                    result_value = VALUES(result_value),
                    has_espc = VALUES(has_espc),
                    result_display = VALUES(result_display),
                    entered_by = VALUES(entered_by),
                    entered_at = NOW()";

        $stmt = $this->conn->prepare($sql);

        $sampleTestId   = (int) $data['sample_test_id'];
        $sampleItemId   = (int) $data['sample_item_id'];
        $parameterId    = (int) $data['parameter_id'];
        $variantId      = $data['variant_id'] ? (int) $data['variant_id'] : null;
        $resultMode     = $data['result_mode'];
        $resultValue    = $data['result_value'];
        $hasEspc        = (int) $data['has_espc'];
        $resultDisplay  = $data['result_display'];
        $enteredBy      = $data['entered_by'] ? (int) $data['entered_by'] : null;

        $stmt->bind_param(
            'iiiissisi',
            $sampleTestId,
            $sampleItemId,
            $parameterId,
            $variantId,
            $resultMode,
            $resultValue,
            $hasEspc,
            $resultDisplay,
            $enteredBy
        );

        return $stmt->execute();
    }

    /**
     * Get the result_mode and espc_applicable for a parameter.
     * Server-side enforcement: never trust client-sent values.
     *
     * @param int $parameterId
     * @return array|null
     */
    public function getParameterConfig($parameterId)
    {
        $sql = "SELECT result_mode, espc_applicable 
                FROM test_parameters 
                WHERE parameter_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $parameterId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ========================================================
    // COMPLETION CHECK
    // ========================================================

    /**
     * Check if all tests for a sample have results.
     *
     * @param int $sampleId
     * @return bool
     */
    public function areAllResultsFilled($sampleId)
    {
        $sql = "SELECT 
                    COUNT(st.sample_test_id) AS total_tests,
                    COUNT(str.result_id) AS filled_results
                FROM sample_items si
                INNER JOIN sample_tests st ON st.sample_item_id = si.sample_item_id
                LEFT JOIN sample_test_results str ON str.sample_test_id = st.sample_test_id
                WHERE si.sample_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row && $row['total_tests'] > 0 && $row['total_tests'] == $row['filled_results'];
    }

    /**
     * Explicitly update analysis dates from user-provided values.
     * Called when the user manually sets dates via the date pickers.
     *
     * @param int $sampleId
     * @param string|null $startDate  YYYY-MM-DD or null
     * @param string|null $endDate    YYYY-MM-DD or null
     * @return bool
     */
    public function updateAnalysisDates($sampleId, $startDate = null, $endDate = null)
    {
        $sampleId = (int) $sampleId;
        if ($sampleId <= 0) {
            return false;
        }

        $sql = "UPDATE samples 
                SET analysis_start_date = ?, 
                    analysis_end_date = ?
                WHERE sample_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $startDate, $endDate, $sampleId);
        return $stmt->execute();
    }

    /**
     * Auto-fill analysis dates on result save (fallback).
     * Only sets dates if the user has NOT already provided them.
     *
     * - If analysis_start_date is NULL, set it to today.
     * - If all results are filled and analysis_end_date is NULL, set it to today.
     *
     * @param int $sampleId
     * @return void
     */
    public function updateAnalysisDatesOnResultSave($sampleId)
    {
        $sampleId = (int) $sampleId;
        if ($sampleId <= 0) {
            return;
        }

        // Read current dates
        $sql = "SELECT analysis_start_date, analysis_end_date 
                FROM samples 
                WHERE sample_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return;
        }

        $needStart = empty($row['analysis_start_date']);
        $needEnd = empty($row['analysis_end_date']) && $this->areAllResultsFilled($sampleId);

        if (!$needStart && !$needEnd) {
            return;
        }

        $updateSql = "UPDATE samples 
                      SET analysis_start_date = COALESCE(analysis_start_date, CURDATE()),
                          analysis_end_date = CASE 
                              WHEN analysis_end_date IS NULL AND ? = 1 THEN CURDATE()
                              ELSE analysis_end_date
                          END
                      WHERE sample_id = ?";

        $flag = $needEnd ? 1 : 0;
        $updStmt = $this->conn->prepare($updateSql);
        $updStmt->bind_param('ii', $flag, $sampleId);
        $updStmt->execute();
    }

    /**
     * Mark a sample as Completed and record who/when.
     *
     * @param int $sampleId
     * @param string $updatedBy
     * @return bool
     */
    public function markSampleCompleted($sampleId, $updatedBy)
    {
        // Get current status for audit log
        $currentSql = "SELECT status FROM samples WHERE sample_id = ?";
        $currentStmt = $this->conn->prepare($currentSql);
        $currentStmt->bind_param('i', $sampleId);
        $currentStmt->execute();
        $current = $currentStmt->get_result()->fetch_assoc();
        $oldStatus = $current ? $current['status'] : 'Pending';

        // Update status
        $sql = "UPDATE samples 
                SET status = 'Completed', 
                    status_updated_at = NOW(), 
                    status_updated_by = ?
                WHERE sample_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $updatedBy, $sampleId);
        $result = $stmt->execute();

        // Insert audit log
        if ($result && $oldStatus !== 'Completed') {
            $logSql = "INSERT INTO sample_status_log 
                        (sample_id, old_status, new_status, updated_by, notes, updated_at)
                       VALUES (?, ?, 'Completed', ?, 'Auto-completed: all results entered', NOW())";
            $logStmt = $this->conn->prepare($logSql);
            $logStmt->bind_param('iss', $sampleId, $oldStatus, $updatedBy);
            $logStmt->execute();
        }

        return $result;
    }

    /**
     * Get sample_id from sample_test_id (for validation).
     *
     * @param int $sampleTestId
     * @return int|null
     */
    public function getSampleIdFromTest($sampleTestId)
    {
        $sql = "SELECT si.sample_id
                FROM sample_tests st
                INNER JOIN sample_items si ON st.sample_item_id = si.sample_item_id
                WHERE st.sample_test_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $sampleTestId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? (int) $row['sample_id'] : null;
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
