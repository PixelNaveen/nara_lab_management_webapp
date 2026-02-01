<?php
/**
 * Consolidated Parameters Model - CORRECTED VERSION
 * Based on working uni.php approach
 * 
 * KEY FIX: Uses EN-DASH (–) not hyphen or em-dash
 * Returns 13 consolidated parameters
 * 
 * @version 5.0 - CORRECTED (Based on uni.php)
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
     * Get ALL consolidated parameters
     * CORRECTED: Uses EN-DASH (–) character exactly as uni.php does
     * Returns 13 unique consolidated parameters
     */
    public function getAllConsolidatedParams()
    {
        try {
            // ========================================
            // EXACT APPROACH FROM uni.php
            // Uses EN-DASH (–) character
            // ========================================
            $sql = "SELECT 
                        -- Show parameter with variants if they exist
                        CASE 
                            WHEN variant_list IS NOT NULL AND variant_list != '' 
                            THEN CONCAT(base_name, ' (', variant_list, ')')
                            ELSE base_name
                        END AS parameter_name,
                        
                        -- Methods separated by ', ' (comma-space)
                        GROUP_CONCAT(
                            DISTINCT tm.method_name 
                            ORDER BY tm.method_name 
                            SEPARATOR ', '
                        ) AS methods
                    
                    FROM (
                        -- Extract base parameter name
                        SELECT 
                            tp.parameter_id,
                            CASE
                                -- ✅ CRITICAL: Using EN-DASH (–) exactly as uni.php
                                WHEN LOCATE('–', tp.parameter_name) > 0 
                                THEN TRIM(SUBSTRING(tp.parameter_name, LOCATE('–', tp.parameter_name) + 1))
                                
                                -- Otherwise use full name
                                ELSE tp.parameter_name
                            END AS base_name
                            
                        FROM test_parameters tp
                        WHERE tp.is_active = 1 
                          AND tp.is_deleted = 0
                    ) AS base_params
                    
                    LEFT JOIN parameter_methods pm ON base_params.parameter_id = pm.parameter_id
                    
                    LEFT JOIN test_methods tm ON pm.method_id = tm.method_id 
                        AND tm.is_active = 1 
                        AND tm.is_deleted = 0
                    
                    -- Get variants for each base parameter
                    LEFT JOIN (
                        SELECT 
                            CASE
                                -- ✅ Same EN-DASH extraction for variants
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
                    
                    GROUP BY base_name, variant_list
                    
                    ORDER BY base_name ASC";
            
            $result = $this->conn->query($sql);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $params = [];
            while ($row = $result->fetch_assoc()) {
                $params[] = [
                    'parameter_name' => $row['parameter_name'] ?? '',
                    'methods' => $row['methods'] ?? ''
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
     * Returns base parameter names
     */
    public function getSelectedParamsForSample($sampleId)
    {
        try {
            $sql = "SELECT DISTINCT tp.parameter_name
                    FROM sample_items si
                    INNER JOIN sample_item_parameters sip ON si.sample_item_id = sip.sample_item_id
                    INNER JOIN test_parameters tp ON sip.parameter_id = tp.parameter_id
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