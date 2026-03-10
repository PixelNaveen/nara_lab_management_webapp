-- ============================================================
-- Migration 009: Test Report Generation System
-- Laboratory Management System
-- Date: 2026-03-03
-- 
-- Creates tables for:
--   1. report_signatories  — Scientists and heads who sign reports
--   2. report_logos         — Logo images for accredited/non-accredited reports
--   3. final_test_reports   — Generated report metadata + audit trail
--   4. report_items         — Links report to sample_items (column/page positions)
--
-- Also:
--   - Adds analysis_start_date, analysis_end_date, is_drawn_by_nara to samples
--   - Fixes cm² encoding in base_units
--   - Fixes method names to correct ISO/SLS standards
--   - Fixes accreditation flags in parameter_base_unit_config
--   - Fixes result_mode for pathogen parameters
-- ============================================================

-- ==================== ALTER SAMPLES TABLE ====================

ALTER TABLE samples
    ADD COLUMN analysis_start_date DATE DEFAULT NULL 
        COMMENT 'Analysis period start date',
    ADD COLUMN analysis_end_date DATE DEFAULT NULL 
        COMMENT 'Analysis period end date',
    ADD COLUMN is_drawn_by_nara TINYINT(1) DEFAULT 0 
        COMMENT '1 = NARA drew the sample, 0 = client submitted';

-- ==================== TABLE 1: report_signatories ====================

CREATE TABLE IF NOT EXISTS report_signatories (
    signatory_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL COMMENT 'Full name e.g. P. Ginigaddarage',
    title VARCHAR(150) NOT NULL COMMENT 'Job title e.g. Senior Scientist',
    division VARCHAR(200) NOT NULL COMMENT 'Division e.g. Post Harvest Technology Division',
    role_type ENUM('scientist','head') NOT NULL COMMENT 'scientist = left block, head = right block',
    is_default TINYINT(1) DEFAULT 1 COMMENT 'Auto-assign to new reports',
    display_order INT DEFAULT 0 COMMENT '1 = left position, 2 = right position',
    is_active TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0 COMMENT 'Soft delete',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-populate with known signatories from existing reports
INSERT INTO report_signatories (full_name, title, division, role_type, is_default, display_order) VALUES
('P. Ginigaddarage', 'Senior Scientist', 'Post Harvest Technology Division', 'scientist', 1, 1),
('Suseema Ariyarathna', 'Head/Senior Scientist', 'Post Harvest Technology Division', 'head', 1, 2);

-- ==================== TABLE 2: report_logos ====================

CREATE TABLE IF NOT EXISTS report_logos (
    logo_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    logo_name VARCHAR(100) NOT NULL COMMENT 'Display name e.g. NARA Logo',
    logo_type ENUM('primary','accreditation','institutional') NOT NULL 
        COMMENT 'primary=NARA, accreditation=SLAB, institutional=Govt seal',
    file_path VARCHAR(500) NOT NULL COMMENT 'Path to logo image file',
    is_for_accredited TINYINT(1) DEFAULT 1 COMMENT '1 = only show on accredited reports, 0 = show on all',
    display_order INT DEFAULT 1 COMMENT 'Position: 1=left, 2=center, 3=right',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-populate logos (users will need to upload actual image files)
INSERT INTO report_logos (logo_name, logo_type, file_path, is_for_accredited, display_order) VALUES
('Government Seal', 'institutional', 'assets/images/govt_seal.png', 1, 1),
('NARA Logo', 'primary', 'assets/images/nara_logo.png', 0, 2),
('SLAB Accreditation Mark', 'accreditation', 'assets/images/slab_logo.png', 1, 3);

-- ==================== TABLE 3: final_test_reports ====================

CREATE TABLE IF NOT EXISTS final_test_reports (
    report_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sample_id INT NOT NULL COMMENT 'FK to samples table',
    report_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Same as sample report_ref e.g. QC/26/009/01',
    report_type ENUM('accredited','non_accredited') NOT NULL 
        COMMENT 'Determines logo count and asterisks',
    layout_type ENUM('single','multi_column','non_accredited_single') NOT NULL DEFAULT 'single'
        COMMENT 'single=1 item/col, multi_column=2-5 items as columns, non_accredited_single=forced 1 item',
    signatory_left_id INT DEFAULT NULL COMMENT 'FK to report_signatories (scientist)',
    signatory_right_id INT DEFAULT NULL COMMENT 'FK to report_signatories (head)',
    signatory_snapshot JSON NOT NULL COMMENT 'Snapshot of signatory data at generation time',
    report_data_snapshot JSON DEFAULT NULL COMMENT 'Full data snapshot for reprint',
    generated_by INT NOT NULL COMMENT 'FK to users',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    print_count INT DEFAULT 0,
    last_printed_at TIMESTAMP NULL,
    notes TEXT DEFAULT NULL,
    is_deleted TINYINT(1) DEFAULT 0 COMMENT 'Soft delete',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sample_id) REFERENCES samples(sample_id) ON DELETE RESTRICT,
    FOREIGN KEY (signatory_left_id) REFERENCES report_signatories(signatory_id) ON DELETE SET NULL,
    FOREIGN KEY (signatory_right_id) REFERENCES report_signatories(signatory_id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== TABLE 4: report_items ====================
-- Links which sample_items are included in a report, with page/column positions

CREATE TABLE IF NOT EXISTS report_items (
    report_item_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL COMMENT 'FK to final_test_reports',
    sample_item_id INT NOT NULL COMMENT 'FK to sample_items',
    page_number INT NOT NULL DEFAULT 1 COMMENT 'Which page (1, 2, 3...)',
    column_position INT NOT NULL DEFAULT 1 COMMENT 'Which column on that page (1-5)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (report_id) REFERENCES final_test_reports(report_id) ON DELETE CASCADE,
    FOREIGN KEY (sample_item_id) REFERENCES sample_items(sample_item_id) ON DELETE RESTRICT,

    UNIQUE KEY uq_report_item (report_id, sample_item_id),
    INDEX idx_report_page (report_id, page_number, column_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== DATA FIXES ====================

-- Fix cm² encoding in base_units (if corrupted)
UPDATE base_units SET unit_name = REPLACE(unit_name, 'cm?', 'cm²') 
WHERE unit_name LIKE '%cm?%';

-- Fix method standards to match actual ISO/SLS references from PDF reports
-- Only update if the current value doesn't already match (safe to re-run)
UPDATE test_methods SET method_name = 'ISO 4833-1:2013(E)' 
WHERE method_id = 1 AND method_name != 'ISO 4833-1:2013(E)';

UPDATE test_methods SET method_name = 'ISO 4831-1:2006(E)' 
WHERE method_id = 3 AND method_name != 'ISO 4831-1:2006(E)';

UPDATE test_methods SET method_name = 'ISO 7251:2005(E)' 
WHERE method_id = 4 AND method_name != 'ISO 7251:2005(E)';

UPDATE test_methods SET method_name = 'ISO/TS 21872-1:2017(E)' 
WHERE method_id = 5 AND method_name != 'ISO/TS 21872-1:2017(E)';

UPDATE test_methods SET method_name = 'ISO 11290-1:2017(E)' 
WHERE method_id = 6 AND method_name != 'ISO 11290-1:2017(E)';

UPDATE test_methods SET method_name = 'ISO 6579-1:2017(E)' 
WHERE method_id = 7 AND method_name != 'ISO 6579-1:2017(E)';

UPDATE test_methods SET method_name = 'ISO 6888-1:2021(E)' 
WHERE method_id = 8 AND method_name != 'ISO 6888-1:2021(E)';

-- Fix accreditation flags for specific configs
-- (Uncomment and adjust IDs based on your actual data)
-- UPDATE parameter_base_unit_config SET is_slab_accredited = 1 WHERE config_id IN (247, 254, 255);

-- Fix result_mode for pathogen parameters (should be present_or_absent)
UPDATE test_parameters SET result_mode = 'present_or_absent'
WHERE parameter_id IN (8, 9, 10, 13) 
AND result_mode != 'present_or_absent';

-- ==================== END MIGRATION 009 ====================
