-- Add is_slab_accredited column to sample_names table
-- Inherits values from the existing sample_type_categories table initially

ALTER TABLE `sample_names`
  ADD COLUMN `is_slab_accredited` TINYINT(1) NOT NULL DEFAULT 0 AFTER `category_id`;

-- Migrate existing accreditation data from the category table to the individual names
UPDATE `sample_names` sn
JOIN `sample_type_categories` stc ON sn.category_id = stc.category_id
SET sn.is_slab_accredited = stc.is_slab_accredited;
