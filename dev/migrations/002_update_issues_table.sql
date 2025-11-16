-- Migration: Update issues table to support full workflow
-- Date: 2025-01-16
-- Description: Add columns for maintenance/inspection linking, resolution notes, and tracking

ALTER TABLE `issues`
ADD COLUMN `maintenance_request_id` INT NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `inspection_id` INT NULL DEFAULT NULL AFTER `maintenance_request_id`,
ADD COLUMN `resolution_notes` TEXT NULL DEFAULT NULL AFTER `inspection_id`,
ADD COLUMN `assigned_to` INT NULL DEFAULT NULL COMMENT 'PM assigned to handle this issue' AFTER `resolution_notes`,
ADD COLUMN `landlord_id` INT NULL DEFAULT NULL COMMENT 'Landlord of the property' AFTER `assigned_to`,
ADD COLUMN `resolved_at` TIMESTAMP NULL DEFAULT NULL AFTER `landlord_id`,
ADD COLUMN `pm_notified` TINYINT(1) DEFAULT 0 COMMENT 'Whether PM was notified' AFTER `resolved_at`,
ADD COLUMN `landlord_notified` TINYINT(1) DEFAULT 0 COMMENT 'Whether landlord was notified' AFTER `pm_notified`;

-- Add foreign keys (commented out - uncomment if needed)
-- ALTER TABLE `issues`
-- ADD CONSTRAINT `fk_issues_maintenance`
-- FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`id`) ON DELETE SET NULL,
-- ADD CONSTRAINT `fk_issues_inspection`
-- FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE SET NULL,
-- ADD CONSTRAINT `fk_issues_assigned_to`
-- FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
-- ADD CONSTRAINT `fk_issues_landlord`
-- FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Update existing issues to populate landlord_id based on property owner
UPDATE issues i
JOIN properties p ON i.property_id = p.id
SET i.landlord_id = p.landlord_id
WHERE i.landlord_id IS NULL;
