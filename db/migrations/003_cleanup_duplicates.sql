-- ============================================================
-- CLEANUP DUPLICATE PRICES AND COMBOS
-- After new database schema migration, parameters were
-- consolidated (Food/Water/Swab → single parameter).
-- This created duplicate individual prices and combo entries.
-- ============================================================

-- ========================
-- STEP 1: Fix Individual Price Duplicates
-- Keep the OLDER pricing_id (first created), soft-delete the newer one
-- ========================

-- parameter_id=2 (Coliforms): keep pricing_id=3, delete pricing_id=5
UPDATE parameter_pricing SET is_deleted = 1, updated_at = NOW() WHERE pricing_id = 5;

-- parameter_id=3 (Faecal coliforms): keep pricing_id=4, delete pricing_id=14
UPDATE parameter_pricing SET is_deleted = 1, updated_at = NOW() WHERE pricing_id = 14;

-- parameter_id=4 (E. coli): keep pricing_id=6, delete pricing_id=13
UPDATE parameter_pricing SET is_deleted = 1, updated_at = NOW() WHERE pricing_id = 13;

-- ========================
-- STEP 2: Fix Combo Duplicates
-- COMBO-001 (id=1) and COMBO-003 (id=3) have same params (2,3) → keep 1, remove 3
-- COMBO-002 (id=2) and COMBO-004 (id=4) have same params (2,3,4) → keep 2, remove 4
-- ========================

-- 2a. Re-point sample_tests from combo_id=3 → combo_id=1 (if any exist)
UPDATE sample_tests SET combo_id = 1 WHERE combo_id = 3;

-- 2b. Re-point sample_tests from combo_id=4 → combo_id=2
UPDATE sample_tests SET combo_id = 2 WHERE combo_id = 4;

-- 2c. Soft-delete the duplicate combos
UPDATE parameter_combinations SET is_deleted = 1, updated_at = NOW() WHERE combo_id = 3;
UPDATE parameter_combinations SET is_deleted = 1, updated_at = NOW() WHERE combo_id = 4;

-- 2d. Soft-delete the duplicate combo pricing
UPDATE combination_pricing SET is_deleted = 1, updated_at = NOW() WHERE combo_id = 3;
UPDATE combination_pricing SET is_deleted = 1, updated_at = NOW() WHERE combo_id = 4;

-- ========================
-- STEP 3: Update combo names to match new schema (no Food/Water prefix)
-- ========================

UPDATE parameter_combinations 
SET combo_name = 'Coliforms + Faecal Coliforms', updated_at = NOW() 
WHERE combo_id = 1 AND is_deleted = 0;

UPDATE parameter_combinations 
SET combo_name = 'Coliforms + Escherichia coli + Faecal Coliforms', updated_at = NOW() 
WHERE combo_id = 2 AND is_deleted = 0;

-- ========================
-- STEP 4 (OPTIONAL): Add UNIQUE constraint to prevent future individual duplicates
-- ========================
-- Only uncomment if you want DB-level protection:
-- ALTER TABLE parameter_pricing ADD UNIQUE KEY `unique_param_active` (parameter_id, is_deleted);

-- ============================================================
-- VERIFICATION: Run these after to confirm cleanup
-- ============================================================
-- SELECT pricing_id, parameter_id, test_charge, is_active, is_deleted FROM parameter_pricing ORDER BY parameter_id;
-- SELECT combo_id, combo_name, combo_code, is_deleted FROM parameter_combinations;
-- SELECT combo_id, COUNT(*) FROM sample_tests WHERE combo_id IS NOT NULL GROUP BY combo_id;
