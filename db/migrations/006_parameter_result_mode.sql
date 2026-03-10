-- ============================================================
-- Migration 006: Parameter Result Mode & ESPC Flag
-- 
-- Purpose:
--   Add simple, future-proof configuration for how each parameter
--   stores results and whether ESPC notation is applicable.
--
--   - result_mode:
--       'numeric_or_ND'    → numeric value OR ND (Not Detected)
--       'present_or_absent'→ categorical Present / Absent
--
--   - espc_applicable:
--       1 → parameter may use ESPC wording in result entry / reports
--       0 → ESPC not used
--
-- Notes:
--   - Existing parameters default to 'numeric_or_ND' with ESPC off.
--   - No existing logic is removed; this only extends configuration.
-- ============================================================

ALTER TABLE `test_parameters`
  ADD COLUMN `result_mode` ENUM('numeric_or_ND','present_or_absent') NOT NULL DEFAULT 'numeric_or_ND'
    COMMENT 'Result rule: numeric+ND or Present/Absent',
  ADD COLUMN `espc_applicable` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = ESPC notation can be used for this parameter';

