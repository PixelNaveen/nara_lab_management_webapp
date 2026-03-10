-- ============================================================
-- Migration 002: SLAB Accreditation Data + PARAMETER MERGING
-- Baseline: lab(5).sql + 001 migration applied
--
-- MERGES:
--   Coliforms: Water(2) + Food(5) → keep ID 2, rename "Coliforms", soft-delete 5
--   F.Coliforms: Water(3) + Food(6) → keep ID 3, rename "Faecal Coliforms", soft-delete 6
--   E. coli: Water(4) + Food(7) → keep ID 4, rename "Escherichia coli", soft-delete 7
--
-- After merge: 13 active parameters (16 - 3 soft-deleted)
--
-- REAL base_unit IDs from lab(5).sql:
--   Water: 1=cfu/mL, 2=MPN/100mL, 3=MPN/mL, 5=/100mL, 6=/100-1000mL
--   Food:  12=cfu/g, 14=MPN/g, 17=/25g
--   Swab:  25=cfu/cm², 27=MPN/cm², 31=Present/Absent
-- ============================================================

-- ============================================================
-- STEP 1: MERGE PARAMETERS (rename survivors, soft-delete duplicates)
-- ============================================================

-- Merge Coliforms: keep ID 2, rename to "Coliforms"
UPDATE test_parameters SET
  parameter_name = 'Coliforms',
  short_name = 'Coliforms',
  display_format = 'normal',
  is_active = 1,
  is_deleted = 0
WHERE parameter_id = 2;

-- Merge F.Coliforms: keep ID 3, rename to "Faecal Coliforms"
UPDATE test_parameters SET
  parameter_name = 'Faecal Coliforms',
  short_name = 'F.Coliforms',
  display_format = 'normal',
  is_active = 1,
  is_deleted = 0
WHERE parameter_id = 3;

-- Merge E.coli: keep ID 4, rename to "Escherichia coli"
UPDATE test_parameters SET
  parameter_name = 'Escherichia coli',
  short_name = 'E. coli',
  display_format = 'scientific',
  is_active = 1,
  is_deleted = 0
WHERE parameter_id = 4;

-- Soft-delete the food-only duplicates
UPDATE test_parameters SET is_active = 0, is_deleted = 1 WHERE parameter_id IN (5, 6, 7);

-- Update references: point old Food param IDs → merged param IDs
UPDATE sample_tests SET parameter_id = 2 WHERE parameter_id = 5;
UPDATE sample_tests SET parameter_id = 3 WHERE parameter_id = 6;
UPDATE sample_tests SET parameter_id = 4 WHERE parameter_id = 7;

UPDATE parameter_pricing SET parameter_id = 2 WHERE parameter_id = 5;
UPDATE parameter_pricing SET parameter_id = 3 WHERE parameter_id = 6;
UPDATE parameter_pricing SET parameter_id = 4 WHERE parameter_id = 7;

UPDATE combination_items SET parameter_id = 2 WHERE parameter_id = 5;
UPDATE combination_items SET parameter_id = 3 WHERE parameter_id = 6;
UPDATE combination_items SET parameter_id = 4 WHERE parameter_id = 7;

-- ============================================================
-- STEP 2: Update remaining parameters (flags, names)
-- ============================================================

-- ID 1: APC
UPDATE test_parameters SET
  short_name = 'APC',
  display_format = 'normal'
WHERE parameter_id = 1;

-- ID 8: V. cholerae
UPDATE test_parameters SET
  short_name = 'V. cholerae',
  display_format = 'scientific'
WHERE parameter_id = 8;

-- ID 9: V. parahaemolyticus
UPDATE test_parameters SET
  short_name = 'V. parahaemo.',
  display_format = 'scientific'
WHERE parameter_id = 9;

-- ID 10: Salmonella
UPDATE test_parameters SET
  short_name = 'Salmonella',
  display_format = 'scientific'
WHERE parameter_id = 10;

-- ID 11: S. aureus
UPDATE test_parameters SET
  short_name = 'S. aureus',
  display_format = 'scientific'
WHERE parameter_id = 11;

-- Non-accredited params (12-16)
UPDATE test_parameters SET short_name = 'F.Streptococci', display_format = 'normal' WHERE parameter_id = 12;
UPDATE test_parameters SET short_name = 'Listeria',       display_format = 'scientific' WHERE parameter_id = 13;
UPDATE test_parameters SET short_name = 'Vibrio spp.',    display_format = 'scientific' WHERE parameter_id = 14;
UPDATE test_parameters SET short_name = 'SRC',            display_format = 'normal' WHERE parameter_id = 15;
UPDATE test_parameters SET short_name = 'Y&M',            display_format = 'normal' WHERE parameter_id = 16;


-- ============================================================
-- STEP 3: Populate parameter_base_unit_config
-- ============================================================

DELETE FROM parameter_category_methods;
DELETE FROM parameter_base_unit_config;

-- PARAM 1 (APC): Water(cfu/mL=1), Food(cfu/g=12), Swab(cfu/cm²=25) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(1, 1, 1, 1, 'SLS 516-1/Sec 1:2013'),
(1, 2, 12, 1, 'SLS 516-1/Sec 1:2013'),
(1, 3, 25, 1, 'SLS 516-1/Sec 1:2013');

-- PARAM 2 (Coliforms — MERGED): Water(MPN/100mL=2) + Food(MPN/g=14) + Swab(MPN/cm²=27) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(2, 1, 2, 1, 'SLS 1461 Part 1/Sec 3:2013'),
(2, 2, 14, 1, 'SLS 516-3/Sec 1:2013'),
(2, 3, 27, 1, 'SLS 1461 Part 1/Sec 3:2013');

-- PARAM 3 (F.Coliforms — MERGED): Water(MPN/100mL=2) + Food(MPN/g=14) + Swab(MPN/cm²=27) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(3, 1, 2, 1, 'SLS 1461 Part 1/Sec 3:2013'),
(3, 2, 14, 1, 'APHA:2015'),
(3, 3, 27, 1, 'SLS 1461 Part 1/Sec 3:2013');

-- PARAM 4 (E.coli — MERGED): Water(MPN/100mL=2) + Food(MPN/g=14) + Swab(MPN/cm²=27) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(4, 1, 2, 1, 'SLS 1461 Part 1/Sec 3:2013'),
(4, 2, 14, 1, 'SLS 516-12:2013'),
(4, 3, 27, 1, 'SLS 1461 Part 1/Sec 3:2013');

-- PARAM 8 (V. cholerae): Water(/100mL=5), Food(/25g=17), Swab(P/A=31) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(8, 1, 5, 1, 'SLS 516-7/Sec 1:2017'),
(8, 2, 17, 1, 'SLS 516-7/Sec 1:2017'),
(8, 3, 31, 1, 'SLS 516-7/Sec 1:2017');

-- PARAM 9 (V. parahaemolyticus): Water(/100mL=5), Food(/25g=17), Swab(P/A=31) | Accr: Food, Swab (NOT Water)
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(9, 1, 5, 0, NULL),
(9, 2, 17, 1, 'SLS 516-7/Sec 1:2017'),
(9, 3, 31, 1, 'SLS 516-7/Sec 1:2017');

-- PARAM 10 (Salmonella): Water(/100-1000mL=6), Food(/25g=17), Swab(P/A=31) | Accr: All 3
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(10, 1, 6, 1, 'ISO 19250:2010'),
(10, 2, 17, 1, 'SLS 516-5:2017'),
(10, 3, 31, 1, 'SLS 516-5:2017');

-- PARAM 11 (S. aureus): Water(cfu/mL=1), Food(cfu/g=12), Swab(cfu/cm²=25) | Accr: Food, Swab (NOT Water)
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(11, 1, 1, 0, NULL),
(11, 2, 12, 1, 'SLS 516-6/Sec 1:2022'),
(11, 3, 25, 1, 'SLS 516-6/Sec 1:2022');

-- PARAM 12 (F. Streptococci): Water(MPN/mL=3), Food(MPN/g=14), Swab(MPN/cm²=27) | Accr: None
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(12, 1, 3, 0, NULL),
(12, 2, 14, 0, NULL),
(12, 3, 27, 0, NULL);

-- PARAM 13 (Listeria): Water(/100mL=5), Food(/25g=17), Swab(P/A=31) | Accr: None
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(13, 1, 5, 0, NULL),
(13, 2, 17, 0, NULL),
(13, 3, 31, 0, NULL);

-- PARAM 14 (Vibrio spp.): Water(/100mL=5), Food(cfu/g=12), Swab(P/A=31) | Accr: None
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(14, 1, 5, 0, NULL),
(14, 2, 12, 0, NULL),
(14, 3, 31, 0, NULL);

-- PARAM 15 (SRC): Water(MPN/100mL=2), Food(MPN/g=14), Swab(MPN/cm²=27) | Accr: None
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(15, 1, 2, 0, NULL),
(15, 2, 14, 0, NULL),
(15, 3, 27, 0, NULL);

-- PARAM 16 (Y&M): Water(cfu/mL=1), Food(cfu/g=12), Swab(cfu/cm²=25) | Accr: None
INSERT INTO parameter_base_unit_config (parameter_id, base_category_id, base_unit_id, is_slab_accredited, slab_standard) VALUES
(16, 1, 1, 0, NULL),
(16, 2, 12, 0, NULL),
(16, 3, 25, 0, NULL);


-- ============================================================
-- STEP 4: Populate parameter_category_methods
-- Only for params with has_category_methods = 1 (IDs: 1, 2, 3, 4, 10)
-- Method IDs: 1=SLS516-1, 2=SLS1461, 3=SLS516-3, 4=SLS516-12, 5=SLS516-7, 6=SLS516-5, 7=ISO19250, 16=APHA2015
-- ============================================================

-- PARAM 1 (APC): method 1 (SLS 516-1) for all 3 categories
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 1, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 1 AND base_category_id = 1;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 1, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 1 AND base_category_id = 2;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 1, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 1 AND base_category_id = 3;

-- PARAM 2 (Coliforms): Water=method 2 (SLS 1461), Food=method 3 (SLS 516-3), Swab=method 2 (SLS 1461)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 2 AND base_category_id = 1;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 3, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 2 AND base_category_id = 2;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 2 AND base_category_id = 3;

-- PARAM 3 (F.Coliforms): Water=method 2 (SLS 1461), Food=method 16 (APHA:2015), Swab=method 2 (SLS 1461)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 3 AND base_category_id = 1;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 16, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 3 AND base_category_id = 2;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 3 AND base_category_id = 3;

-- PARAM 4 (E.coli): Water=method 2 (SLS 1461), Food=method 4 (SLS 516-12), Swab=method 2 (SLS 1461)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 4 AND base_category_id = 1;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 4, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 4 AND base_category_id = 2;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 2, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 4 AND base_category_id = 3;

-- PARAM 10 (Salmonella): Water=method 7 (ISO 19250), Food=method 6 (SLS 516-5), Swab=method 6
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 7, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 10 AND base_category_id = 1;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 6, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 10 AND base_category_id = 2;
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 6, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 10 AND base_category_id = 3;

-- ============================================================
-- STEP 5: Populate parameter_category_methods for OTHER params
-- Based on the original parameter_methods table
-- ============================================================

-- PARAM 8 (V. cholerae): method 5 (SLS 516-7)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 5, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 8;

-- PARAM 9 (V. parahaemolyticus): method 5 (SLS 516-7)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 5, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 9;

-- PARAM 11 (S. aureus): method 8 (SLS 516-6)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 8, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 11;

-- PARAM 12 (F. Streptococci): method 9 (SLS 516-4)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 9, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 12;

-- PARAM 13 (Listeria): method 10 (ISO 11920)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 10, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 13;

-- PARAM 14 (Vibrio spp.): method 11 (APHA:2001)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 11, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 14;

-- PARAM 15 (SRC): method 12 (ISO 6461)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 12, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 15;

-- PARAM 16 (Y&M): method 13 (SLS 516-2)
INSERT INTO parameter_category_methods (config_id, method_id, sequence_order, is_primary)
SELECT config_id, 13, 0, 1 FROM parameter_base_unit_config WHERE parameter_id = 16;
