<?php

/**
 * Invoice Model
 * Laboratory Management System
 *
 * Handles database operations for invoice generation:
 * - Fetching sample and pricing data
 * - Grouping sample items by type (Water, Ice, Swab, etc.)
 * - Calculating quantities and sub-totals
 * - Generating the QC/M/... invoice number format
 * - Saving and retrieving invoice snapshots to freeze pricing
 *
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class InvoiceModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get all samples that have test items (so invoices can be generated).
     */
    public function getSamplesForInvoice($filters = [])
    {
        $sql = "SELECT 
                    s.sample_id,
                    s.sample_code,
                    s.received_date,
                    c.client_name,
                    COUNT(DISTINCT si.sample_item_id) AS item_count,
                    i.invoice_id,
                    i.invoice_number,
                    i.created_at AS invoice_date
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                INNER JOIN sample_items si ON si.sample_id = s.sample_id
                LEFT JOIN invoices i ON i.sample_id = s.sample_id
                WHERE 1=1";

        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (s.sample_code LIKE ? OR c.client_name LIKE ? OR i.invoice_number LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
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
            $samples[] = [
                'sample_id'      => (int) $row['sample_id'],
                'sample_code'    => $row['sample_code'],
                'received_date'  => $row['received_date'],
                'client_name'    => $row['client_name'],
                'item_count'     => (int) $row['item_count'],
                'invoice_id'     => $row['invoice_id'] ? (int)$row['invoice_id'] : null,
                'invoice_number' => $row['invoice_number'],
                'invoice_date'   => $row['invoice_date']
            ];
        }
        return $samples;
    }

    /**
     * Build the dynamic Invoice Number based on Sample Code
     * e.g., QC/26/012/004 -> QC/M/26/012
     */
    public function generateInvoiceNumber($base_sample_code)
    {
        $code = trim($base_sample_code);
        // Ensure it starts with QC
        if (strtoupper(substr($code, 0, 3)) !== 'QC/') {
            $code = 'QC/' . ltrim($code, '/');
        }

        $parts = explode('/', $code);

        // If it has 4 parts (e.g., QC/26/015/002), it represents a specific sample.
        // For the invoice, we use the submission base (first 3 parts).
        if (count($parts) >= 4) {
            array_pop($parts);
        }

        // Re-insert 'M' as the second element -> QC / M / 26 / 012
        if (count($parts) >= 2 && strtoupper($parts[0]) === 'QC') {
            array_splice($parts, 1, 0, 'M');
            return implode('/', $parts);
        }

        // Fallback if the format doesn't match expected
        return $code . '-INV';
    }

    /**
     * Get Raw data needed to build an invoice.
     * Groups parameters by sample type.
     */
    public function getInvoiceRawData($sampleId, $requestDate = null)
    {
        // 1. Get header / customer details
        $headerSql = "SELECT 
                        s.sample_id, s.sample_code, s.received_date,
                        c.client_name, c.address_line1 AS client_address,
                        ci.city_name
                      FROM samples s
                      INNER JOIN clients c ON s.client_id = c.client_id
                      LEFT JOIN cities ci ON c.city_id = ci.city_id
                      WHERE s.sample_id = ?";

        $stmt = $this->conn->prepare($headerSql);
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();

        if (!$header) {
            return null;
        }

        // Generate report number (Ensure it has QC prefix)
        $code = trim($header['sample_code']);
        if (strtoupper(substr($code, 0, 3)) !== 'QC/') {
            $code = 'QC/' . ltrim($code, '/');
        }
        $reportNumber = $code;

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($header['sample_code']);

        // 2. Fetch all parameters, prices, and group by item type
        // Use sample_name (e.g., Cuttle fish samples) to group. Combine with category if needed.
        $itemsSql = "SELECT 
                        si.sample_item_id,
                        si.sample_name,
                        st.parameter_id,
                        st.combo_id,
                        st.charge,
                        st.is_swab,
                        tp.parameter_name,
                        tp.short_name,
                        pv.variant_name,
                        pc.combo_name,
                        cp.test_charge AS combo_test_charge
                     FROM sample_items si
                     INNER JOIN sample_tests st ON st.sample_item_id = si.sample_item_id
                     INNER JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                     LEFT JOIN parameter_variants pv ON st.variant_id = pv.variant_id
                     LEFT JOIN parameter_combinations pc ON st.combo_id = pc.combo_id
                     LEFT JOIN combination_pricing cp ON pc.combo_id = cp.combo_id AND cp.is_active = 1 AND cp.is_deleted = 0
                     WHERE si.sample_id = ?
                     ORDER BY si.sample_name ASC, tp.parameter_name ASC";

        $stmt2 = $this->conn->prepare($itemsSql);
        $stmt2->bind_param('i', $sampleId);
        $stmt2->execute();
        $testsRes = $stmt2->get_result();

        $itemGroups = []; // Group by sample name

        // ─── PHASE 1: Collect per-item charges ─────────────────────────────
        // Track each item's charges independently so items with the same name
        // but different tests are correctly priced.
        $perItemCharges = []; // [item_id => total_charge_for_this_item]
        $perItemCombos  = []; // [item_id => [combo_id => true]] to avoid double-counting combos per item

        while ($row = $testsRes->fetch_assoc()) {
            $sampleName = trim($row['sample_name']);
            if (empty($sampleName)) $sampleName = 'Unknown Sample';

            // Swab categorization
            if ($row['is_swab']) {
                $sampleName = 'Swab sample';
            }

            $itemId = $row['sample_item_id'];
            $paramId = $row['parameter_id'];
            $baseParamName = trim($row['parameter_name']);
            $paramName = $baseParamName;
            $shortName = trim($row['short_name']);
            $variantName = trim($row['variant_name']);
            if ($variantName) {
                $paramName .= ' ' . $variantName;
            }
            $charge = floatval($row['charge']);

            $comboId = $row['combo_id'];
            $comboName = trim($row['combo_name'] ?? '');

            if (!isset($itemGroups[$sampleName])) {
                $itemGroups[$sampleName] = [
                    'sample_type' => $sampleName,
                    'items_tracked' => [], // unique sample_item_ids
                    'parameters' => [], // Union of ALL unique parameters across all items
                    'quantity' => 0,
                    'sub_total' => 0 // Will be computed as SUM of individual item charges
                ];
            }

            // Track item count
            if (!in_array($itemId, $itemGroups[$sampleName]['items_tracked'])) {
                $itemGroups[$sampleName]['items_tracked'][] = $itemId;
                $itemGroups[$sampleName]['quantity'] = count($itemGroups[$sampleName]['items_tracked']);
            }

            // Initialize per-item tracking
            if (!isset($perItemCharges[$itemId])) {
                $perItemCharges[$itemId] = 0;
                $perItemCombos[$itemId] = [];
            }

            // ─── Accumulate charges PER ITEM (not just the first) ───
            // Process Short Names for footnotes (collect from ALL items)
            $displayParamName = $paramName;
            if (!empty($shortName)) {
                $itemGroups[$sampleName]['footnotes'][$shortName] = $baseParamName;
                $displayParamName = $shortName . '*';
                if ($variantName) {
                    $displayParamName .= ' ' . $variantName;
                }
            }

            // Build the UNION parameter list for display (all unique params across all items)
            if ($comboId) {
                $comboKey = 'combo_' . $comboId;
                // Add to display parameter list (union — only once per combo)
                if (!isset($itemGroups[$sampleName]['parameters'][$comboKey])) {
                    $actualComboCharge = isset($row['combo_test_charge']) ? floatval($row['combo_test_charge']) : $charge;
                    $itemGroups[$sampleName]['parameters'][$comboKey] = [
                        'is_combo' => true,
                        'name'     => $comboName ?: 'Combo Test',
                        'params'   => [$displayParamName],
                        'fee'      => $actualComboCharge
                    ];
                } else {
                    if (!in_array($displayParamName, $itemGroups[$sampleName]['parameters'][$comboKey]['params'])) {
                        $itemGroups[$sampleName]['parameters'][$comboKey]['params'][] = $displayParamName;
                    }
                }

                // Accumulate charge PER ITEM: count combo charge once per item
                if (!isset($perItemCombos[$itemId][$comboId])) {
                    $perItemCombos[$itemId][$comboId] = true;
                    $actualComboCharge = isset($row['combo_test_charge']) ? floatval($row['combo_test_charge']) : $charge;
                    $perItemCharges[$itemId] += $actualComboCharge;
                }
            } else {
                // Non-combo parameter — add to display list (union)
                if (!isset($itemGroups[$sampleName]['parameters'][$displayParamName])) {
                    $itemGroups[$sampleName]['parameters'][$displayParamName] = [
                        'is_combo' => false,
                        'fee'      => $charge
                    ];
                }

                // Accumulate charge PER ITEM
                $perItemCharges[$itemId] += $charge;
            }
        }

        // ─── PHASE 2: Compute sub-totals from per-item charges ────────────
        foreach ($itemGroups as $sampleName => &$group) {
            $groupSubTotal = 0;
            foreach ($group['items_tracked'] as $trackedItemId) {
                $groupSubTotal += ($perItemCharges[$trackedItemId] ?? 0);
            }
            $group['sub_total'] = $groupSubTotal;
        }
        unset($group); // Break reference

        // ─── PHASE 3: Format for invoice template ─────────────────────────
        $invoiceRows = [];
        $grandTotal = 0;
        $totalSamplesCount = 0;
        $globalFootnotes = [];

        foreach ($itemGroups as $group) {

            // Gather footnotes up to the global level
            if (isset($group['footnotes'])) {
                foreach ($group['footnotes'] as $short => $full) {
                    $globalFootnotes[$short] = $full;
                }
            }

            $paramList = [];
            foreach ($group['parameters'] as $pKey => $pData) {
                if (isset($pData['is_combo']) && $pData['is_combo']) {
                    $paramStr = implode(', ', $pData['params']);
                    $paramList[] = [
                        'name' => $paramStr,
                        'fee'  => $pData['fee']
                    ];
                } else {
                    $paramList[] = [
                        'name' => $pKey,
                        'fee'  => $pData['fee']
                    ];
                }
            }

            $qty = $group['quantity'];
            $subTotal = $group['sub_total']; // ✅ Uses actual per-item sum, NOT unit_price × qty
            $unitPrice = ($qty > 0) ? round($subTotal / $qty, 2) : 0; // Averaged for display
            $grandTotal += $subTotal;
            $totalSamplesCount += $qty;

            $invoiceRows[] = [
                'sample_type' => $group['sample_type'],
                'parameters'  => $paramList,
                'unit_price'  => $unitPrice,
                'quantity'    => $qty,
                'sub_total'   => $subTotal
            ];
        }

        // 3. Fetch Extra Items
        $extraSql = "SELECT
                        sei.quantity,
                        sei.unit_price,
                        sei.line_total,
                        ei.item_name
                     FROM sample_extra_items sei
                     INNER JOIN extra_items ei ON sei.item_id = ei.item_id
                     WHERE sei.sample_id = ?";
        $stmt_ex = $this->conn->prepare($extraSql);
        $stmt_ex->bind_param('i', $sampleId);
        $stmt_ex->execute();
        $extraRes = $stmt_ex->get_result();

        $extraItems = [];
        $extraTotal = 0;
        while ($exRow = $extraRes->fetch_assoc()) {
            $extraItems[] = [
                'name'       => $exRow['item_name'],
                'quantity'   => (int)$exRow['quantity'],
                'unit_price' => floatval($exRow['unit_price']),
                'line_total' => floatval($exRow['line_total'])
            ];
            $extraTotal += floatval($exRow['line_total']);
            // $totalSamplesCount is NOT incremented here because extra items 
            // should not be counted as "No. of Samples" in the total row.
        }

        $grandTotal += $extraTotal;

        // ─── CROSS-VALIDATION: Compare with authoritative stored total ────
        // The samples table stores the correct total calculated at submission time.
        // If our dynamic calculation differs, log it and use the stored value.
        $storedGrandTotal = null;
        $validationStmt = $this->conn->prepare("SELECT grand_total, test_charges_total, additional_charges FROM samples WHERE sample_id = ?");
        $validationStmt->bind_param('i', $sampleId);
        $validationStmt->execute();
        $storedRow = $validationStmt->get_result()->fetch_assoc();
        if ($storedRow) {
            $storedGrandTotal = floatval($storedRow['grand_total']);
            $tolerance = 5; // Allow small rounding differences from combo splitting
            if (abs($grandTotal - $storedGrandTotal) > $tolerance) {
                error_log("[InvoiceModel WARNING] Price mismatch for sample_id=$sampleId: " .
                    "Calculated=$grandTotal, Stored=$storedGrandTotal, Diff=" . abs($grandTotal - $storedGrandTotal));
                // Use stored total as authoritative source
                $grandTotal = $storedGrandTotal;
            }
        }

        // Also fetch active signatories
        $signatories = $this->getActiveSignatories();

        return [
            'sample_id'       => $header['sample_id'],
            'invoice_number'  => $invoiceNumber,
            'report_number'   => $reportNumber,
            'date_of_request' => $requestDate ? date('d - m - Y', strtotime($requestDate)) : date('d - m - Y', strtotime($header['received_date'])),
            'customer'        => [
                'name'    => $header['client_name'],
                'address' => $header['client_address'],
                'city'    => $header['city_name']
            ],
            'rows'             => $invoiceRows,
            'extra_items'      => $extraItems,
            'footnotes'        => $globalFootnotes,
            'total_payable'    => $grandTotal,
            'total_items_count' => $totalSamplesCount,
            'signatories'      => $signatories
        ];
    }

    /**
     * Get active signatories for the invoice footer
     */
    public function getActiveSignatories()
    {
        $sql = "SELECT signatory_id, full_name, title, division 
                FROM report_signatories
                WHERE is_active = 1 AND is_deleted = 0
                ORDER BY display_order ASC";
        $result = $this->conn->query($sql);
        $sigs = [];
        while ($row = $result->fetch_assoc()) {
            $sigs[] = $row;
        }
        return $sigs;
    }

    /**
     * Generate or Retrieve an Invoice
     * Freezes pricing data to `invoices` table
     */
    public function saveAndFreezeInvoice($sampleId, $signatoryId, $userId, $requestDate = null)
    {
        // Check if invoice already exists
        $stmt = $this->conn->prepare("SELECT invoice_id, signatory_id, invoice_data_snapshot FROM invoices WHERE sample_id = ?");
        $stmt->bind_param('i', $sampleId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $requestDate === null) {
            // Already generated and no new date requested? Return it.
            return $res['invoice_id'];
        }

        // Generate data
        $data = $this->getInvoiceRawData($sampleId, $requestDate);
        if (!$data) throw new Exception("Unable to load data for sample ID: " . $sampleId);

        // Find signatory title/division for snapping
        $sigData = null;
        foreach ($data['signatories'] as $s) {
            if ($s['signatory_id'] == $signatoryId) {
                $sigData = $s;
                break;
            }
        }

        $snapshot = $data;
        $snapshot['selected_signatory'] = $sigData;
        $jsonSnapshot = json_encode($snapshot);

        if ($res) {
            // Invoice exists but we want to update it (likely a new date or signatory)
            $updateSql = "UPDATE invoices SET invoice_data_snapshot = ?, signatory_id = ? WHERE invoice_id = ?";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bind_param('sii', $jsonSnapshot, $signatoryId, $res['invoice_id']);
            $updateStmt->execute();
            return $res['invoice_id'];
        }

        // Save
        $sql = "INSERT INTO invoices (sample_id, invoice_number, report_number, invoice_data_snapshot, signatory_id, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmtInsert = $this->conn->prepare($sql);
        $stmtInsert->bind_param(
            'isssii',
            $sampleId,
            $data['invoice_number'],
            $data['report_number'],
            $jsonSnapshot,
            $signatoryId,
            $userId
        );

        if ($stmtInsert->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Failed to save invoice.");
        }
    }

    /**
     * Get a saved frozen invoice by ID
     */
    public function getInvoiceById($invoiceId)
    {
        $sql = "SELECT * FROM invoices WHERE invoice_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $invoiceId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
