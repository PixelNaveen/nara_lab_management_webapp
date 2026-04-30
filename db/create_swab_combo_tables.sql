-- =============================================
-- Swab Combo Tables for Lab Management System
-- Run this script once to create the tables
-- =============================================

CREATE TABLE IF NOT EXISTS `swab_combos` (
  `combo_id` int(11) NOT NULL AUTO_INCREMENT,
  `combo_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`combo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Swab combo pricing groups';

CREATE TABLE IF NOT EXISTS `swab_combo_items` (
  `combo_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `combo_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`combo_item_id`),
  KEY `fk_swab_combo_items_combo` (`combo_id`),
  KEY `fk_swab_combo_items_param` (`parameter_id`),
  CONSTRAINT `fk_swab_combo_items_combo` FOREIGN KEY (`combo_id`) REFERENCES `swab_combos` (`combo_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_swab_combo_items_param` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Parameters linked to swab combos';
