-- ============================================================
-- Migration 004: Sample Names Category System
-- Links sample names to SLAB accredited categories
-- 
-- Creates:
--   sample_type_categories: Lookup table for 4 categories
--   Adds category_id column to sample_names
--   Maps existing 11 sample names to appropriate categories
-- ============================================================

-- ============================================================
-- STEP 1: Create sample_type_categories lookup table
-- ============================================================

CREATE TABLE IF NOT EXISTS `sample_type_categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `category_code` VARCHAR(10) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `base_category_id` INT(11) DEFAULT NULL COMMENT 'Links to parameter_base_unit_config.base_category_id for unit/method resolution',
  `is_slab_accredited` TINYINT(1) NOT NULL DEFAULT 0,
  `display_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `unique_category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sample type categories linked to SLAB accreditation';

-- Insert the 4 categories
INSERT INTO `sample_type_categories` (`category_id`, `category_name`, `category_code`, `description`, `base_category_id`, `is_slab_accredited`, `display_order`) VALUES
(1, 'Water and Ice', 'WAT', 'Drinking water, treated water, ice cubes, potable water', 1, 1, 1),
(2, 'Fish and Shellfish', 'FSH', 'Fish, shrimp, shellfish, and other seafood products', 2, 1, 2),
(3, 'Surface Swab', 'SWB', 'Surface swabs from tables, equipment, and other surfaces', 3, 1, 3),
(4, 'Other', 'OTH', 'Non-accredited samples (cosmetics, soil, general food, etc.)', NULL, 0, 4);

-- ============================================================
-- STEP 2: Add category_id column to sample_names
-- ============================================================

ALTER TABLE `sample_names` 
ADD COLUMN `category_id` INT(11) DEFAULT NULL AFTER `sample_name`,
ADD KEY `idx_category_id` (`category_id`);

-- ============================================================
-- STEP 3: Map existing sample names to categories
-- ============================================================

-- Water and Ice (category_id = 1)
UPDATE `sample_names` SET `category_id` = 1 WHERE `sample_name` IN ('Drinking Water', 'Treated Water', 'Potable water', 'Water', 'Ice Cubes', 'Ice');

-- Fish and Shellfish (category_id = 2)
UPDATE `sample_names` SET `category_id` = 2 WHERE `sample_name` IN ('Fish', 'Shrimp', 'Food Sample');

-- Surface Swab (category_id = 3)
UPDATE `sample_names` SET `category_id` = 3 WHERE `sample_name` IN ('Surface Swab', 'Table');

-- Any remaining unmapped names → Other (category_id = 4)
UPDATE `sample_names` SET `category_id` = 4 WHERE `category_id` IS NULL;

-- ============================================================
-- VERIFICATION QUERIES (run after migration)
-- ============================================================
-- SELECT sn.sample_name, stc.category_name 
-- FROM sample_names sn 
-- LEFT JOIN sample_type_categories stc ON sn.category_id = stc.category_id 
-- ORDER BY stc.display_order, sn.sample_name;
