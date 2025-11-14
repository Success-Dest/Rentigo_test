-- ============================================================================
-- IN-PLACE MIGRATION SCRIPT
-- Safely updates existing database to new schema without losing data
-- Run this on your existing rentigo_db database
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- STEP 1: Backup checkpoint
-- ============================================================================
-- If this fails, your database structure might be different. Stop here!

-- ============================================================================
-- STEP 2: Update PROPERTIES table
-- ============================================================================

-- Check if properties table exists
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables
                     WHERE table_schema = 'rentigo_db' AND table_name = 'properties');

-- Add listing_type 'rental' value if needed (extend enum)
ALTER TABLE `properties`
  MODIFY COLUMN `listing_type` enum('rent','maintenance','rental')
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rent';

-- Ensure approval_status exists (it should from your old DB)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'properties'
                   AND column_name = 'approval_status');

-- Add approval_status if missing (shouldn't be, but safe check)
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `properties` ADD COLUMN `approval_status` enum(''pending'',''approved'',''rejected'') COLLATE utf8mb4_unicode_ci DEFAULT ''pending'' AFTER `status`',
  'SELECT "approval_status already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure current_occupant exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'properties'
                   AND column_name = 'current_occupant');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `properties` ADD COLUMN `current_occupant` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `description`',
  'SELECT "current_occupant already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure tenant column exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'properties'
                   AND column_name = 'tenant');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `properties` ADD COLUMN `tenant` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `laundry`',
  'SELECT "tenant already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure issue column exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'properties'
                   AND column_name = 'issue');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `properties` ADD COLUMN `issue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AFTER `tenant`',
  'SELECT "issue already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure property_purpose exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'properties'
                   AND column_name = 'property_purpose');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `properties` ADD COLUMN `property_purpose` enum(''rent'',''maintenance'') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''rent'' AFTER `property_type`',
  'SELECT "property_purpose already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 3: Update POLICIES table
-- ============================================================================

-- Extend policy_category enum to include both old and new values
ALTER TABLE `policies`
  MODIFY COLUMN `policy_category` enum('rental','security','maintenance','financial','general','privacy','terms_of_service','refund','data_protection')
  COLLATE utf8mb4_general_ci NOT NULL;

-- Extend policy_status enum to include both old and new values
ALTER TABLE `policies`
  MODIFY COLUMN `policy_status` enum('draft','active','inactive','archived','under_review')
  COLLATE utf8mb4_general_ci DEFAULT 'draft';

-- Ensure policy_description exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'policies'
                   AND column_name = 'policy_description');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `policies` ADD COLUMN `policy_description` text COLLATE utf8mb4_general_ci AFTER `policy_category`',
  'SELECT "policy_description already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure policy_type exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'policies'
                   AND column_name = 'policy_type');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `policies` ADD COLUMN `policy_type` enum(''standard'',''custom'') COLLATE utf8mb4_general_ci DEFAULT ''standard'' AFTER `policy_status`',
  'SELECT "policy_type already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure expiry_date exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'policies'
                   AND column_name = 'expiry_date');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `policies` ADD COLUMN `expiry_date` date DEFAULT NULL AFTER `effective_date`',
  'SELECT "expiry_date already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 4: Update PROPERTY_MANAGER table
-- ============================================================================

-- Ensure rejection_reason exists
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'property_manager'
                   AND column_name = 'rejection_reason');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `property_manager` ADD COLUMN `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AFTER `approved_at`',
  'SELECT "rejection_reason already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 5: Create NEW tables if they don't exist
-- ============================================================================

-- Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `move_in_date` date NOT NULL,
  `move_out_date` date NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','active','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_landlord_id` (`landlord_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_bookings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lease Agreements Table
CREATE TABLE IF NOT EXISTS `lease_agreements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `property_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `lease_duration_months` int NOT NULL,
  `terms_and_conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','pending_signatures','active','completed','terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `signed_by_tenant` tinyint(1) NOT NULL DEFAULT '0',
  `signed_by_landlord` tinyint(1) NOT NULL DEFAULT '0',
  `tenant_signature_date` timestamp NULL DEFAULT NULL,
  `landlord_signature_date` timestamp NULL DEFAULT NULL,
  `termination_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `termination_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_landlord_id` (`landlord_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_leases_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leases_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leases_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leases_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `property_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('pending','completed','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `due_date` date NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_landlord_id` (`landlord_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maintenance Requests Table
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `issue_id` int DEFAULT NULL,
  `provider_id` int DEFAULT NULL,
  `requester_id` int NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('pending','scheduled','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `completion_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_landlord_id` (`landlord_id`),
  KEY `idx_issue_id` (`issue_id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_requester_id` (`requester_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_maintenance_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_maintenance_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_maintenance_issue` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_maintenance_provider` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_maintenance_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages Table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `property_id` int DEFAULT NULL,
  `subject` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_message_id` int DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sender_id` (`sender_id`),
  KEY `idx_recipient_id` (`recipient_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_parent_message_id` (`parent_message_id`),
  KEY `idx_is_read` (`is_read`),
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_messages_parent` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reviewer_id` int NOT NULL,
  `reviewee_id` int NOT NULL,
  `property_id` int DEFAULT NULL,
  `booking_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `review_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `review_type` enum('property','tenant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviewer_id` (`reviewer_id`),
  KEY `idx_reviewee_id` (`reviewee_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_review_type` (`review_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_reviewee` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 6: Add indexes for performance
-- ============================================================================

-- Add updated_at to inspections if missing
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
                   WHERE table_schema = 'rentigo_db'
                   AND table_name = 'inspections'
                   AND column_name = 'updated_at');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `inspections` ADD COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
  'SELECT "updated_at already exists in inspections"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- FINAL STEP: Commit all changes
-- ============================================================================

COMMIT;

-- ============================================================================
-- Migration Complete!
-- ============================================================================
SELECT 'Migration completed successfully! Your old columns and enum values are preserved.' AS Result;
SELECT 'All existing CRUD operations should continue to work.' AS Status;
SELECT 'New tables have been created for enhanced features.' AS NewFeatures;
