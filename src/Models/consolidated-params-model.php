<?php

/**
 * Consolidated Parameters Model - SCHEMA-COMPATIBLE VERSION
 * 
 * Uses BOTH old (parameter_methods) and new (parameter_category_methods) 
 * tables for complete method coverage.
 * 
 * KEY FIX: Uses EN-DASH (–) not hyphen or em-dash
 * 
 * @version 6.0 - Compatible with new base_unit schema
 */

require_once __DIR__ . '/../../Config/Database.php';

class ConsolidatedParamsModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get ALL consolidated parameters with methods from BOTH tables
     * Uses UNION of parameter_methods (old) + parameter_category_methods (new)
     * to ensure complete method coverage across both schemas.
     */
    public function getAllConsolidatedParams()
    {
        try {
            $sql = "SELECT 
                        -- Show parameter with variants if they exist
                        CASE 
                            WHEN variant_list IS NOT NULL AND variant_list != '' 
                            THEN CONCAT(base_name, ' (', variant_list, ')')
                            ELSE base_name
                        END AS parameter_name,
                        
                        base_params.display_format,
                        
                        -- Methods separated by ', ' (comma-space)
                        GROUP_CONCAT(
                            DISTINCT method_name 
                            ORDER BY method_name 
                            SEPARATOR ', '
                        ) AS methods
                    
                    FROM (
                        -- Extract base parameter name
                        SELECT 
                            tp.parameter_id,
                            tp.display_format,
                            CASE
                                WHEN LOCATE('–', tp.parameter_name) > 0 
                                THEN TRIM(SUBSTRING(tp.parameter_name, LOCATE('–', tp.parameter_name) + 1))
                                ELSE tp.parameter_name
                            END AS base_name
                            
                        FROM test_parameters tp
                        WHERE tp.is_active = 1 
                          AND tp.is_deleted = 0
                    ) AS base_params
                    
                    -- UNION of methods from BOTH old and new tables
                    LEFT JOIN (
                        -- Old table: parameter_methods (direct assignment)
                        SELECT pm.parameter_id, tm.method_name
                        FROM parameter_methods pm
                        INNER JOIN test_methods tm ON pm.method_id = tm.method_id
                            AND tm.is_active = 1 AND tm.is_deleted = 0
                        
                        UNION
                        
                        -- New table: parameter_category_methods (via base_unit_config)
                        SELECT pbc.parameter_id, tm.method_name
                        FROM parameter_base_unit_config pbc
                        INNER JOIN parameter_category_methods pcm ON pbc.config_id = pcm.config_id
                        INNER JOIN test_methods tm ON pcm.method_id = tm.method_id
                            AND tm.is_active = 1 AND tm.is_deleted = 0
                        WHERE pbc.is_active = 1
                    ) AS all_methods ON base_params.parameter_id = all_methods.parameter_id
                    
                    -- Get variants for each base parameter
                    LEFT JOIN (
                        SELECT 
                            CASE
                                WHEN LOCATE('–', tp_var.parameter_name) > 0 
                                THEN TRIM(SUBSTRING(tp_var.parameter_name, LOCATE('–', tp_var.parameter_name) + 1))
                                ELSE tp_var.parameter_name
                            END AS base_key,
                            GROUP_CONCAT(
                                DISTINCT pv.variant_name 
                                ORDER BY pv.variant_name 
                                SEPARATOR ', '
                            ) AS variant_list
                        FROM test_parameters tp_var
                        LEFT JOIN parameter_variants pv ON tp_var.parameter_id = pv.parameter_id 
                            AND pv.is_active = 1 
                            AND pv.is_deleted = 0
                        WHERE tp_var.is_active = 1 
                          AND tp_var.is_deleted = 0
                        GROUP BY base_key
                    ) AS variants ON base_params.base_name = variants.base_key
                    
                    GROUP BY base_name, variant_list, base_params.display_format
                    
                    ORDER BY base_name ASC";

            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }

            $params = [];
            while ($row = $result->fetch_assoc()) {
                $params[] = [
                    'parameter_name' => $row['parameter_name'] ?? '',
                    'methods' => $row['methods'] ?? '',
                    'display_format' => $row['display_format'] ?? 'normal'
                ];
            }

            return $params;
        } catch (Exception $e) {
            error_log("Consolidated Params Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get selected parameters for a sample (for highlighting in AIF)
     * FIXED: Uses sample_tests table (actual table, not sample_item_parameters)
     * Returns base parameter names
     */
    public function getSelectedParamsForSample($sampleId)
    {
        try {
            $sql = "SELECT DISTINCT tp.parameter_name
                    FROM sample_items si
                    INNER JOIN sample_tests st ON si.sample_item_id = st.sample_item_id
                    INNER JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                    WHERE si.sample_id = ?
                      AND tp.is_active = 1 
                      AND tp.is_deleted = 0";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $sampleId);
            $stmt->execute();
            $result = $stmt->get_result();

            $selected = [];
            while ($row = $result->fetch_assoc()) {
                $paramName = $row['parameter_name'];

                // Extract base name using same EN-DASH approach
                if (strpos($paramName, '–') !== false) {
                    $position = strpos($paramName, '–');
                    $baseName = trim(substr($paramName, $position + 1));
                } else {
                    $baseName = $paramName;
                }

                $selected[] = $baseName;
            }

            return array_unique($selected);
        } catch (Exception $e) {
            error_log("Get Selected Params Error: " . $e->getMessage());
            return [];
        }
    }
}
