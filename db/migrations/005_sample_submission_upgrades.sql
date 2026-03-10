-- ============================================================
-- Migration 005: Sample Submission Upgrades
-- Adds collected date/time, temperature value, container
-- tracking, and sample category locking per item.
-- ============================================================

-- STEP 1: Add collected date/time to samples table
ALTER TABLE `samples`
ADD COLUMN `sample_collected_date` DATE DEFAULT NULL AFTER `received_time`,
ADD COLUMN `sample_collected_time` TIME DEFAULT NULL AFTER `sample_collected_date`;

-- STEP 2: Add per-item fields to sample_items table
ALTER TABLE `sample_items`
ADD COLUMN `temperature_value` DECIMAL(4,2) DEFAULT NULL COMMENT 'Exact temp when chilled (2.0-6.0)' AFTER `temperature_condition`,
ADD COLUMN `container_item_id` INT(11) DEFAULT NULL COMMENT 'FK to extra_items for container used' AFTER `temperature_value`,
ADD COLUMN `sample_category_id` INT(11) DEFAULT NULL COMMENT 'FK to sample_type_categories - locked at submission time' AFTER `container_item_id`;

-- STEP 3: Create sample_extra_items junction for additional charges
CREATE TABLE IF NOT EXISTS `sample_extra_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sample_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `line_total` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `idx_sample_id` (`sample_id`),
  KEY `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Extra items purchased per submission';
