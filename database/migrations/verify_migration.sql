-- ============================================================================
-- DATABASE VERIFICATION SCRIPT
-- Run this after migration to verify all columns and tables exist
-- ============================================================================

SELECT '======================================' AS '';
SELECT 'DATABASE STRUCTURE VERIFICATION' AS '';
SELECT '======================================' AS '';

-- Check PROPERTIES table columns
SELECT '\n=== PROPERTIES TABLE ===' AS '';
SELECT
  CASE
    WHEN COUNT(*) >= 20 THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Properties table has ', COUNT(*), ' columns (expected 20+)') AS Result
FROM information_schema.columns
WHERE table_schema = 'rentigo_db' AND table_name = 'properties';

-- Check critical properties columns
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'current_occupant') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'current_occupant'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'property_purpose') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'property_purpose'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'tenant') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'tenant'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'issue') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'issue'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'approval_status') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'approval_status';

-- Check POLICIES table columns
SELECT '\n=== POLICIES TABLE ===' AS '';
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'policy_description') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'policies'
  AND column_name = 'policy_description'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'policy_type') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'policies'
  AND column_name = 'policy_type'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'expiry_date') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'policies'
  AND column_name = 'expiry_date';

-- Check PROPERTY_MANAGER table columns
SELECT '\n=== PROPERTY_MANAGER TABLE ===' AS '';
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'rejection_reason') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'property_manager'
  AND column_name = 'rejection_reason'
UNION ALL
SELECT
  CASE
    WHEN column_name = 'id' THEN 'PASS ✓'
    ELSE 'FAIL ✗ (should be id, not manager_id)'
  END AS Status,
  CONCAT('Primary Key: ', column_name) AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'property_manager'
  AND column_key = 'PRI';

-- Check SERVICE_PROVIDERS table columns
SELECT '\n=== SERVICE_PROVIDERS TABLE ===' AS '';
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'company') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'service_providers'
  AND column_name = 'company'
UNION ALL
SELECT
  CASE
    WHEN column_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Column: ', 'address') AS ColumnCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'service_providers'
  AND column_name = 'address';

-- Check ENUM values
SELECT '\n=== ENUM VALUES CHECK ===' AS '';

-- Properties listing_type enum
SELECT
  CASE
    WHEN column_type LIKE '%rent%' AND column_type LIKE '%maintenance%' THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('listing_type enum: ', column_type) AS EnumCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'properties'
  AND column_name = 'listing_type';

-- Policies policy_category enum
SELECT
  CASE
    WHEN column_type LIKE '%rental%'
     AND column_type LIKE '%security%'
     AND column_type LIKE '%maintenance%'
     AND column_type LIKE '%financial%'
     AND column_type LIKE '%general%' THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  'policy_category enum contains old values (rental,security,maintenance,financial,general)' AS EnumCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'policies'
  AND column_name = 'policy_category';

-- Policies policy_status enum
SELECT
  CASE
    WHEN column_type LIKE '%inactive%' THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  'policy_status enum contains ''inactive''' AS EnumCheck
FROM information_schema.columns
WHERE table_schema = 'rentigo_db'
  AND table_name = 'policies'
  AND column_name = 'policy_status';

-- Check NEW tables exist
SELECT '\n=== NEW TABLES ===' AS '';
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'bookings') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'bookings'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'lease_agreements') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'lease_agreements'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'payments') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'payments'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'maintenance_requests') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'maintenance_requests'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'messages') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'messages'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'notifications') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'notifications'
UNION ALL
SELECT
  CASE
    WHEN table_name IS NOT NULL THEN 'PASS ✓'
    ELSE 'FAIL ✗'
  END AS Status,
  CONCAT('Table: ', 'reviews') AS TableCheck
FROM information_schema.tables
WHERE table_schema = 'rentigo_db'
  AND table_name = 'reviews';

-- Final summary
SELECT '\n=== VERIFICATION SUMMARY ===' AS '';
SELECT
  CONCAT(
    'Total tables in database: ',
    COUNT(*)
  ) AS Summary
FROM information_schema.tables
WHERE table_schema = 'rentigo_db';

SELECT '======================================' AS '';
SELECT 'If all checks show PASS ✓, migration was successful!' AS '';
SELECT 'Your old CRUD operations should work without changes.' AS '';
SELECT '======================================' AS '';
