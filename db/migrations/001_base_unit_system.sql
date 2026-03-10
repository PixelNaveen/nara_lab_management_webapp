-- ============================================================
-- Migration 001: Move SLAB Accreditation to Per-Category
-- Baseline: lab(5).sql
-- ============================================================

-- Add is_slab_accredited to category config
ALTER TABLE `parameter_base_unit_config`
  ADD COLUMN `is_slab_accredited` TINYINT(1) NOT NULL DEFAULT 0 AFTER `base_unit_id`;

-- Drop obsolete columns from test_parameters
-- (is_slab_accredited moves to config table, has_category_methods/has_temperature_variants are obsolete because ALL configs are per-category now)
ALTER TABLE `test_parameters`
  DROP COLUMN IF EXISTS `is_slab_accredited`,
  DROP COLUMN IF EXISTS `is_category_specific`,
  DROP COLUMN IF EXISTS `has_temperature_variants`,
  DROP COLUMN IF EXISTS `has_category_methods`;

-- Clean up duplicate base_units (IDs 38-53 are duplicates from a previous bad migration run)
DELETE FROM `base_units` WHERE `base_unit_id` >= 38;
