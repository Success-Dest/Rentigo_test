-- ============================================================================
-- SCHEMA FIX FOR RENTIGO DATABASE
-- This adds missing columns to match the application code
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Fix properties table - add approval_status column
ALTER TABLE `properties`
ADD COLUMN `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' AFTER `status`,
ADD COLUMN `approved_at` timestamp NULL DEFAULT NULL AFTER `approval_status`;

-- Update existing records to sync status with approval_status
UPDATE `properties` SET `approval_status` =
    CASE
        WHEN `status` IN ('pending') THEN 'pending'
        WHEN `status` IN ('approved', 'available', 'occupied', 'maintenance') THEN 'approved'
        WHEN `status` = 'rejected' THEN 'rejected'
        ELSE 'pending'
    END;

-- Fix property_manager table - rename and add columns
ALTER TABLE `property_manager`
CHANGE COLUMN `document_filename` `employee_id_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
CHANGE COLUMN `document_mimetype` `employee_id_filetype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `employee_id_filesize` int DEFAULT NULL AFTER `employee_id_filetype`,
CHANGE COLUMN `verification_status` `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
CHANGE COLUMN `approval_date` `approved_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `approved_by` int DEFAULT NULL AFTER `approved_at`,
ADD COLUMN `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `approved_by`;

-- Add foreign key for approved_by
ALTER TABLE `property_manager`
ADD CONSTRAINT `fk_property_manager_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Display confirmation
SELECT 'Schema updated successfully! Properties table now has approval_status and approved_at columns.' AS message;
SELECT 'Property_manager table now has correct column names matching the code.' AS message;
