-- Migration: Update inspections table to use property_id and add missing fields
-- Date: 2025-01-16
-- Description: Fix database schema for inspection module

-- Step 1: Add columns only if they don't exist
-- Note: Run each ALTER TABLE separately and ignore errors for existing columns

-- Add property_id if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'property_id') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `property_id` INT NULL AFTER `id`',
    'SELECT "Column property_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add issue_id if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'issue_id') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `issue_id` INT NULL AFTER `property_id`',
    'SELECT "Column issue_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add scheduled_time if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'scheduled_time') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `scheduled_time` TIME NULL AFTER `scheduled_date`',
    'SELECT "Column scheduled_time already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add notes if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'notes') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `notes` TEXT NULL COMMENT "Scheduling notes" AFTER `scheduled_time`',
    'SELECT "Column notes already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add inspection_notes if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'inspection_notes') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `inspection_notes` TEXT NULL COMMENT "Findings after inspection" AFTER `notes`',
    'SELECT "Column inspection_notes already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add manager_id if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'manager_id') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `manager_id` INT NULL COMMENT "PM who scheduled the inspection" AFTER `inspection_notes`',
    'SELECT "Column manager_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add landlord_id if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'landlord_id') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `landlord_id` INT NULL AFTER `manager_id`',
    'SELECT "Column landlord_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id if not exists
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
     AND table_name = 'inspections'
     AND column_name = 'tenant_id') = 0,
    'ALTER TABLE `inspections` ADD COLUMN `tenant_id` INT NULL AFTER `landlord_id`',
    'SELECT "Column tenant_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Migrate existing data (SKIP - only needed if old 'property' column exists)
-- Step 3: Copy issues column (SKIP - only needed if old 'issues' column exists)
-- Step 4: Drop old columns (SKIP - not needed for fresh schema)

-- Step 5: Add foreign key constraints (only if they don't exist)
SET @fk_property = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_schema = DATABASE()
                    AND table_name = 'inspections'
                    AND constraint_name = 'fk_inspections_property');

SET @sql = IF(@fk_property = 0,
    'ALTER TABLE `inspections` ADD CONSTRAINT `fk_inspections_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE',
    'SELECT "Foreign key fk_inspections_property already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_issue = (SELECT COUNT(*) FROM information_schema.table_constraints
                 WHERE constraint_schema = DATABASE()
                 AND table_name = 'inspections'
                 AND constraint_name = 'fk_inspections_issue');

SET @sql = IF(@fk_issue = 0,
    'ALTER TABLE `inspections` ADD CONSTRAINT `fk_inspections_issue` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key fk_inspections_issue already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_manager = (SELECT COUNT(*) FROM information_schema.table_constraints
                   WHERE constraint_schema = DATABASE()
                   AND table_name = 'inspections'
                   AND constraint_name = 'fk_inspections_manager');

SET @sql = IF(@fk_manager = 0,
    'ALTER TABLE `inspections` ADD CONSTRAINT `fk_inspections_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key fk_inspections_manager already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 6: Make property_id required after migration (run this manually after verification)
-- ALTER TABLE `inspections` MODIFY `property_id` INT NOT NULL;
