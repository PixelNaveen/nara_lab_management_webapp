-- ============================================================
-- Migration 007: Sample Test Results Table
-- 
-- Purpose:
--   Create storage for test results entered by lab analysts.
--   One row per sample_test (per item/parameter/variant).
--   Supports two result modes:
--     - numeric_or_ND: numeric value OR ND (Not Detected)
--     - present_or_absent: Present / Absent categorical
--   ESPC flag stored per result when applicable.
--
-- Dependencies:
--   - sample_tests table (FK)
--   - sample_items table (FK)
--   - test_parameters table (FK)
--   - parameter_variants table (FK)
--
-- Notes:
--   - result_mode is copied from test_parameters at save time
--   - result_display is pre-formatted for reports/certificates
--   - entered_by references users.user_id
-- ============================================================

CREATE TABLE IF NOT EXISTS `sample_test_results` (
  `result_id`          INT(11) NOT NULL AUTO_INCREMENT,
  `sample_test_id`     INT(11) NOT NULL,
  `sample_item_id`     INT(11) NOT NULL,
  `parameter_id`       INT(11) NOT NULL,
  `variant_id`         INT(11) NULL,

  -- Result mode (copied from test_parameters.result_mode at save time)
  `result_mode`        ENUM('numeric_or_ND','present_or_absent') NOT NULL,

  -- Unified string-based result value (supports <1, 1800+, 10x10^4)
  `result_value`       VARCHAR(100) NULL,

  -- ESPC flag
  `has_espc`           TINYINT(1) NOT NULL DEFAULT 0,

  -- Pre-formatted display value for certificates/reports
  `result_display`     VARCHAR(255) NULL,

  -- Audit fields
  `entered_by`         INT(11) NULL COMMENT 'FK: users.user_id',
  `entered_at`         TIMESTAMP NULL,
  `updated_at`         TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`result_id`),
  UNIQUE KEY `uniq_sample_test` (`sample_test_id`),
  KEY `idx_sample_item` (`sample_item_id`),
  KEY `idx_parameter` (`parameter_id`),
  KEY `idx_entered_by` (`entered_by`),

  CONSTRAINT `fk_str_sample_test` FOREIGN KEY (`sample_test_id`)
    REFERENCES `sample_tests` (`sample_test_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_str_sample_item` FOREIGN KEY (`sample_item_id`)
    REFERENCES `sample_items` (`sample_item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_str_parameter` FOREIGN KEY (`parameter_id`)
    REFERENCES `test_parameters` (`parameter_id`),
  CONSTRAINT `fk_str_variant` FOREIGN KEY (`variant_id`)
    REFERENCES `parameter_variants` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Test result entries with type-aware storage (numeric/ND or Present/Absent)';

-- Add index on sample_items.sample_category_id if not exists
-- (safe to run even if index already exists - will just warn)
ALTER TABLE `sample_items`
  ADD KEY `idx_sample_category_id` (`sample_category_id`);
