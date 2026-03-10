<?php

namespace Lab\Helpers;

/**
 * Form Data Validator
 * 
 * Validates form data before template rendering to prevent:
 * - Undefined index errors
 * - Missing required fields
 * - Type mismatches
 * - Invalid data structures
 * 
 * @package Lab\Helpers
 * @author Laboratory System
 * @version 1.0.0
 */

class FormDataValidator
{
    /**
     * Validation errors
     * @var array
     */
    private array $errors = [];
    
    /**
     * Validate Sample Acceptance Form (SAF) data
     * 
     * @param array $data SAF data
     * @return array Validation result with valid flag, validated data, and errors
     */
    public function validateSAFData(array $data): array
    {
        $this->errors = [];
        
        // Required fields
        $this->requireField($data, 'sample', 'array');
        $this->requireField($data, 'pages', 'array');
        
        // Validate sample data structure
        if (isset($data['sample']) && is_array($data['sample'])) {
            $this->requireField($data['sample'], 'sample_code', 'string');
            $this->requireField($data['sample'], 'sample_id', 'int');
        }
        
        // Validate pages structure
        if (isset($data['pages']) && is_array($data['pages'])) {
            if (empty($data['pages'])) {
                $this->errors[] = "SAF must have at least one page";
            }
        }
        
        // Apply defaults
        $data = $this->applySAFDefaults($data);
        
        return [
            'valid' => empty($this->errors),
            'data' => $data,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Validate Acknowledgement Form data
     * 
     * @param array $data Acknowledgement data
     * @return array Validation result
     */
    public function validateAcknowledgementData(array $data): array
    {
        $this->errors = [];
        
        // Required fields
        $this->requireField($data, 'sample_id', 'int');
        $this->requireField($data, 'form_number', 'string');
        $this->requireField($data, 'report_ref', 'string');
        $this->requireField($data, 'parameters', 'array');
        $this->requireField($data, 'parameter_count', 'int');
        $this->requireField($data, 'page_config', 'array');
        $this->requireField($data, 'display_config', 'array');
        
        // Validate page_config structure
        if (isset($data['page_config']) && is_array($data['page_config'])) {
            $this->requireField($data['page_config'], 'size', 'string');
            $this->requireField($data['page_config'], 'width', 'numeric');
            $this->requireField($data['page_config'], 'height', 'numeric');
            $this->requireField($data['page_config'], 'print_mode', 'string');
        }
        
        // Validate display_config structure
        if (isset($data['display_config']) && is_array($data['display_config'])) {
            $this->requireField($data['display_config'], 'row_count', 'int');
            $this->requireField($data['display_config'], 'font_size', 'string');
        }
        
        // Apply defaults
        $data = $this->applyAcknowledgementDefaults($data);
        
        return [
            'valid' => empty($this->errors),
            'data' => $data,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Validate Analyst Information Form (AIF) data
     * 
     * @param array $data Analyst data
     * @return array Validation result
     */
    public function validateAnalystData(array $data): array
    {
        $this->errors = [];
        
        // Required fields
        $this->requireField($data, 'sample_id', 'int');
        $this->requireField($data, 'form_number', 'string');
        $this->requireField($data, 'parameters', 'array');
        $this->requireField($data, 'parameter_count', 'int');
        $this->requireField($data, 'page_config', 'array');
        $this->requireField($data, 'display_config', 'array');
        
        // Validate page_config structure
        if (isset($data['page_config']) && is_array($data['page_config'])) {
            $this->requireField($data['page_config'], 'size', 'string');
            $this->requireField($data['page_config'], 'width', 'numeric');
            $this->requireField($data['page_config'], 'height', 'numeric');
        }
        
        // Validate display_config structure
        if (isset($data['display_config']) && is_array($data['display_config'])) {
            $this->requireField($data['display_config'], 'row_count', 'int');
            $this->requireField($data['display_config'], 'font_size', 'string');
        }
        
        // Apply defaults
        $data = $this->applyAnalystDefaults($data);
        
        return [
            'valid' => empty($this->errors),
            'data' => $data,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Require a field to exist and be of specified type
     * 
     * @param array $data Data array
     * @param string $field Field name
     * @param string $type Expected type
     * @return void
     */
    private function requireField(array $data, string $field, string $type): void
    {
        if (!isset($data[$field])) {
            $this->errors[] = "Missing required field: $field";
            return;
        }
        
        $value = $data[$field];
        $valid = false;
        
        switch ($type) {
            case 'int':
                $valid = is_int($value) || filter_var($value, FILTER_VALIDATE_INT) !== false;
                break;
                
            case 'string':
                $valid = is_string($value);
                break;
                
            case 'array':
                $valid = is_array($value);
                break;
                
            case 'numeric':
                $valid = is_numeric($value);
                break;
                
            case 'bool':
                // Accept native booleans and common HTTP form boolean representations
                // e.g., "true", "false", "1", "0", "on", "off", "yes", "no"
                $valid = is_bool($value) || filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
                break;
        }
        
        if (!$valid) {
            $this->errors[] = "Field '$field' must be of type $type, got " . gettype($value);
        }
    }
    
    /**
     * Apply default values for SAF data
     * 
     * @param array $data SAF data
     * @return array Data with defaults applied
     */
    private function applySAFDefaults(array $data): array
    {
        // Sample defaults
        if (!isset($data['sample'])) {
            $data['sample'] = [];
        }
        
        // Note: sample_code is required, do not set default here
        $data['sample']['client_name'] = $data['sample']['client_name'] ?? 'N/A';
        $data['sample']['sample_type'] = $data['sample']['sample_type'] ?? 'N/A';
        $data['sample']['sample_description'] = $data['sample']['sample_description'] ?? '';
        
        // Acceptance defaults
        if (!isset($data['acceptance'])) {
            $data['acceptance'] = [];
        }
        
        $data['acceptance']['received_by'] = $data['acceptance']['received_by'] ?? 'N/A';
        $data['acceptance']['received_date'] = $data['acceptance']['received_date'] ?? date('Y-m-d');
        $data['acceptance']['remarks'] = $data['acceptance']['remarks'] ?? '';
        
        // Pages defaults
        if (!isset($data['pages']) || !is_array($data['pages'])) {
            $data['pages'] = [];
        }
        
        return $data;
    }
    
    /**
     * Apply default values for Acknowledgement data
     * 
     * @param array $data Acknowledgement data
     * @return array Data with defaults applied
     */
    private function applyAcknowledgementDefaults(array $data): array
    {
        // Only set defaults for truly optional fields
        // Note: Fields validated as required (sample_id, form_number, report_ref, parameters, parameter_count, 
        // page_config, display_config) are NOT given defaults to avoid masking validation failures
        
        $data['received_by'] = $data['received_by'] ?? 'N/A';
        $data['received_date'] = $data['received_date'] ?? date('d/m/Y');
        $data['received_time'] = $data['received_time'] ?? date('H:i');
        $data['tentative_date'] = $data['tentative_date'] ?? '';
        $data['receipt_no'] = $data['receipt_no'] ?? 'NOT PAID';
        $data['test_charges'] = $data['test_charges'] ?? 0.00;
        $data['total_charges'] = $data['total_charges'] ?? 0.00;
        $data['signature'] = $data['signature'] ?? '';
        
        return $data;
    }
    
    /**
     * Apply default values for Analyst data
     * 
     * @param array $data Analyst data
     * @return array Data with defaults applied
     */
    private function applyAnalystDefaults(array $data): array
    {
        $data['received_by'] = $data['received_by'] ?? 'N/A';
        $data['received_date'] = $data['received_date'] ?? date('d/m/Y');
        $data['received_time'] = $data['received_time'] ?? date('H:i');
        $data['sample_nos'] = $data['sample_nos'] ?? '';
        $data['sample_description'] = $data['sample_description'] ?? '';
        $data['volume_weight'] = $data['volume_weight'] ?? '';
        $data['sampling_date'] = $data['sampling_date'] ?? '';
        $data['remarks'] = $data['remarks'] ?? '';
        $data['analysis_start_date'] = $data['analysis_start_date'] ?? '';
        $data['analysis_start_by'] = $data['analysis_start_by'] ?? '';
        $data['report_submission_date'] = $data['report_submission_date'] ?? '';
        $data['authorized_by'] = $data['authorized_by'] ?? '';
        
        // Ensure parameters is array
        if (!isset($data['parameters']) || !is_array($data['parameters'])) {
            $data['parameters'] = [];
        }
        
        // Ensure selected_params is array
        if (!isset($data['selected_params']) || !is_array($data['selected_params'])) {
            $data['selected_params'] = [];
        }
        
        return $data;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array Array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool True if no errors
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }
}