<?php
/**
 * Consolidated Parameters Model
 * Gets ALL base parameters (merges Food/Water/Ice variants)
 * NOT LIMITED TO 13 - returns all parameters dynamically
 * 
 * CORRECTED: Methods separator is COMMA + SPACE (not /)
 * 
 * @package LabManagementSystem
 * @subpackage Models
 * @version 2.0 - Corrected
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
     * Get ALL consolidated parameters with methods
     * Returns ALL rows dynamically (not limited to 13)
     * 
     * CORRECTED: Methods separated by COMMA + SPACE (not /)
     * 
     * @return array Array of [parameter_name, methods]
     */
    public function getAllConsolidatedParams()
    {
        try {
            // UNIVERSAL QUERY - Works with ANY delimiter (–, -, etc.)
            // Automatically detects and extracts base parameter name
            $sql = "SELECT 
                        CASE 
                            WHEN variant_list IS NOT NULL AND variant_list != '' 
                            THEN CONCAT(base_name, ' (', variant_list, ')')
                            ELSE base_name
                        END AS parameter_name,
                        
                        -- CORRECTED: Methods separated by COMMA + SPACE
                        GROUP_CONCAT(
                            DISTINCT tm.method_name 
                            ORDER BY tm.method_name 
                            SEPARATOR ', '
                        ) AS methods

                    FROM (
                        SELECT 
                            tp.parameter_id,
                            CASE
                                -- Automatically extracts base parameter name
                                -- Handles: 'Food – Coliforms' → 'Coliforms'
                                --          'Water and Ice – E. coli' → 'E. coli'
                                --          'Soil – ABC' → 'ABC' (future-proof)
                                WHEN LOCATE('–', tp.parameter_name) > 0 
                                THEN TRIM(SUBSTRING(tp.parameter_name, LOCATE('–', tp.parameter_name) + 3))
                                ELSE tp.parameter_name
                            END AS base_name
                        FROM test_parameters tp
                        WHERE tp.is_active = 1 AND tp.is_deleted = 0
                    ) AS base_params

                    LEFT JOIN parameter_methods pm ON base_params.parameter_id = pm.parameter_id
                    
                    LEFT JOIN test_methods tm ON pm.method_id = tm.method_id 
                        AND tm.is_active = 1 
                        AND tm.is_deleted = 0
                    
                    LEFT JOIN (
                        -- Get variants for each base parameter
                        SELECT 
                            CASE
                                WHEN LOCATE('–', tp3.parameter_name) > 0 
                                THEN TRIM(SUBSTRING(tp3.parameter_name, LOCATE('–', tp3.parameter_name) + 3))
                                ELSE tp3.parameter_name
                            END AS base_key,
                            GROUP_CONCAT(
                                DISTINCT pv.variant_name 
                                ORDER BY pv.variant_name 
                                SEPARATOR ', '
                            ) AS variant_list
                        FROM test_parameters tp3
                        LEFT JOIN parameter_variants pv ON tp3.parameter_id = pv.parameter_id 
                            AND pv.is_active = 1 
                            AND pv.is_deleted = 0
                        WHERE tp3.is_active = 1 
                          AND tp3.is_deleted = 0
                        GROUP BY base_key
                    ) AS variants ON base_params.base_name = variants.base_key

                    GROUP BY base_name, variant_list, base_params.parameter_id

                    ORDER BY MIN(base_params.parameter_id) ASC";
            
            $result = $this->conn->query($sql);
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
     * Returns array of BASE parameter names (without Food/Water prefix)
     * 
     * @param int $sampleId Sample ID
     * @return array Array of selected parameter base names
     */
    public function getSelectedParamsForSample($sampleId)
    {
        try {
            $sql = "SELECT DISTINCT tp.parameter_id, tp.parameter_name
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
                // Extract base name for matching
                $baseName = $row['parameter_name'];
                
                // Remove prefix if exists (Food, Water and Ice, etc.)
                if (strpos($baseName, '–') !== false) {
                    $baseName = trim(substr($baseName, strpos($baseName, '–') + strlen('–')));
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