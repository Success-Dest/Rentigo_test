-- ============================================================================
-- RENTIGO RENTAL PROPERTY MANAGEMENT SYSTEM - COMPLETE DATABASE SCHEMA
-- Version: 1.0 Final
-- Date: 2025-11-12
-- Description: Complete schema with all tables, foreign keys, indexes, and seed data
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rentigo_db`
--
CREATE DATABASE IF NOT EXISTS `rentigo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rentigo_db`;

-- ============================================================================
-- TABLE STRUCTURE
-- ============================================================================

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','property_manager','tenant','landlord') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tenant',
  `account_status` enum('pending','active','suspended','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `terms_version` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_account_status` (`account_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `properties`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `landlord_id` int NOT NULL,
  `manager_id` int DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_type` enum('apartment','house','condo','townhouse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `listing_type` enum('rental','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rental',
  `bedrooms` int NOT NULL,
  `bathrooms` decimal(2,1) NOT NULL,
  `sqft` int DEFAULT NULL,
  `rent` decimal(10,2) NOT NULL,
  `deposit` decimal(10,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('available','occupied','maintenance','pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `available_date` date DEFAULT NULL,
  `parking` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `pet_policy` enum('no','cats','dogs','both') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `laundry` enum('none','shared','in_unit','hookups','included') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_landlord_id` (`landlord_id`),
  KEY `idx_manager_id` (`manager_id`),
  KEY `idx_status` (`status`),
  KEY `idx_property_type` (`property_type`),
  KEY `idx_listing_type` (`listing_type`),
  CONSTRAINT `fk_properties_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_properties_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `move_in_date` date NOT NULL,
  `move_out_date` date NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
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

-- --------------------------------------------------------
-- Table structure for table `lease_agreements`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lease_agreements`;
CREATE TABLE `lease_agreements` (
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
  `terms_and_conditions` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','pending_signatures','active','completed','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `signed_by_tenant` tinyint(1) NOT NULL DEFAULT '0',
  `signed_by_landlord` tinyint(1) NOT NULL DEFAULT '0',
  `tenant_signature_date` timestamp NULL DEFAULT NULL,
  `landlord_signature_date` timestamp NULL DEFAULT NULL,
  `termination_reason` text COLLATE utf8mb4_unicode_ci,
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

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `property_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `due_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
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

-- --------------------------------------------------------
-- Table structure for table `issues`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `issues`;
CREATE TABLE `issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('low','medium','high','emergency') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_issues_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_issues_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `maintenance_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `maintenance_requests`;
CREATE TABLE `maintenance_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `landlord_id` int NOT NULL,
  `issue_id` int DEFAULT NULL,
  `provider_id` int DEFAULT NULL,
  `requester_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','emergency') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('pending','scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `completion_notes` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
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
  CONSTRAINT `fk_maintenance_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `inspections`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `inspections`;
CREATE TABLE `inspections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issues` int DEFAULT '0',
  `type` enum('routine','move_in','move_out','maintenance','annual','emergency','issue') COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_date` date NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `service_providers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `service_providers`;
CREATE TABLE `service_providers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialty` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_specialty` (`specialty`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for maintenance_requests -> service_providers
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `fk_maintenance_provider` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- Table structure for table `reviews`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reviewer_id` int NOT NULL,
  `reviewee_id` int NOT NULL,
  `property_id` int DEFAULT NULL,
  `booking_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `review_type` enum('property','tenant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
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
  CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_rating` CHECK ((`rating` >= 1 AND `rating` <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `property_id` int DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
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

-- --------------------------------------------------------
-- Table structure for table `policies`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `policies`;
CREATE TABLE `policies` (
  `policy_id` int NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_category` enum('privacy','terms_of_service','refund','security','data_protection','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `policy_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `policy_status` enum('draft','active','archived','under_review') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `effective_date` date DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`policy_id`),
  KEY `idx_policy_status` (`policy_status`),
  KEY `idx_policy_category` (`policy_category`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_policies_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `property_manager`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `property_manager`;
CREATE TABLE `property_manager` (
  `manager_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id_document` longblob,
  `document_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_mimetype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approval_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`manager_id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  UNIQUE KEY `idx_employee_id` (`employee_id`),
  CONSTRAINT `fk_property_manager_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Insert ONE Admin account only
INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `account_status`, `terms_accepted_at`, `terms_version`, `created_at`) VALUES
(1, 'System Administrator', 'admin@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW(), '1.0', NOW());

-- Insert sample landlords
INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `account_status`, `created_at`) VALUES
(2, 'John Landlord', 'landlord1@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'active', NOW()),
(3, 'Sarah Property Owner', 'landlord2@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'active', NOW());

-- Insert sample tenants
INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `account_status`, `created_at`) VALUES
(4, 'Alice Tenant', 'tenant1@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant', 'active', NOW()),
(5, 'Bob Renter', 'tenant2@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant', 'active', NOW()),
(6, 'Charlie Resident', 'tenant3@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant', 'active', NOW());

-- Insert sample property manager
INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `account_status`, `terms_accepted_at`, `created_at`) VALUES
(7, 'Maria Manager', 'manager1@rentigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'property_manager', 'active', NOW(), NOW());

INSERT INTO `property_manager` (`user_id`, `employee_id`, `verification_status`, `approval_date`, `created_at`) VALUES
(7, 'EMP001', 'verified', NOW(), NOW());

-- Insert sample properties
INSERT INTO `properties` (`id`, `landlord_id`, `manager_id`, `address`, `property_type`, `listing_type`, `bedrooms`, `bathrooms`, `sqft`, `rent`, `deposit`, `description`, `status`, `parking`, `pet_policy`, `laundry`, `created_at`) VALUES
(1, 2, 7, '123 Main Street, Colombo 03, Sri Lanka', 'apartment', 'rental', 2, 1.5, 950, 35000.00, 35000.00, 'Beautiful 2-bedroom apartment in the heart of Colombo', 'available', '1', 'cats', 'in_unit', NOW()),
(2, 2, NULL, '456 Ocean View Road, Colombo 03, Sri Lanka', 'condo', 'rental', 3, 2.0, 1200, 45000.00, 45000.00, 'Luxury condo with ocean views and modern amenities', 'available', '2', 'no', 'in_unit', NOW()),
(3, 3, 7, '789 Park Avenue, Colombo 05, Sri Lanka', 'house', 'rental', 4, 3.0, 2000, 75000.00, 75000.00, 'Spacious family house with garden and parking', 'available', '2', 'both', 'in_unit', NOW()),
(4, 2, NULL, '321 Green Street, Colombo 07, Sri Lanka', 'apartment', 'rental', 1, 1.0, 650, 25000.00, 25000.00, 'Cozy studio apartment perfect for singles', 'occupied', '1', 'no', 'shared', NOW()),
(5, 3, NULL, '654 Hill Road, Colombo 04, Sri Lanka', 'townhouse', 'rental', 3, 2.5, 1500, 55000.00, 55000.00, 'Modern townhouse in quiet neighborhood', 'available', '2', 'dogs', 'in_unit', NOW());

-- Insert sample bookings
INSERT INTO `bookings` (`id`, `tenant_id`, `property_id`, `landlord_id`, `move_in_date`, `move_out_date`, `monthly_rent`, `deposit_amount`, `total_amount`, `status`, `notes`, `created_at`) VALUES
(1, 4, 4, 2, '2025-01-01', '2025-12-31', 25000.00, 25000.00, 50000.00, 'active', 'One-year lease agreement', '2025-01-01 00:00:00'),
(2, 5, 1, 2, '2025-02-01', '2026-01-31', 35000.00, 35000.00, 70000.00, 'pending', 'Interested in immediate move-in', NOW());

-- Insert sample lease agreements
INSERT INTO `lease_agreements` (`id`, `tenant_id`, `landlord_id`, `property_id`, `booking_id`, `start_date`, `end_date`, `monthly_rent`, `deposit_amount`, `lease_duration_months`, `terms_and_conditions`, `status`, `signed_by_tenant`, `signed_by_landlord`, `tenant_signature_date`, `landlord_signature_date`, `created_at`) VALUES
(1, 4, 2, 4, 1, '2025-01-01', '2025-12-31', 25000.00, 25000.00, 12, 'Standard lease terms and conditions apply. Tenant agrees to maintain property in good condition.', 'active', 1, 1, '2024-12-28 10:00:00', '2024-12-29 14:00:00', '2024-12-27 00:00:00');

-- Insert sample payments
INSERT INTO `payments` (`id`, `tenant_id`, `landlord_id`, `property_id`, `booking_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`, `due_date`, `notes`, `created_at`) VALUES
(1, 4, 2, 4, 1, 25000.00, 'bank_transfer', 'TXN202501010001', 'completed', '2025-01-01 09:00:00', '2025-01-01', 'January 2025 rent payment', '2025-01-01 09:00:00'),
(2, 4, 2, 4, 1, 25000.00, 'pending', '', 'pending', NULL, '2025-02-01', 'February 2025 rent payment', '2025-01-15 00:00:00');

-- Insert sample issues
INSERT INTO `issues` (`id`, `tenant_id`, `property_id`, `title`, `description`, `category`, `priority`, `status`, `created_at`) VALUES
(1, 4, 4, 'Leaking faucet in kitchen', 'The kitchen faucet has been dripping water continuously for the past 2 days', 'Plumbing', 'medium', 'pending', NOW()),
(2, 4, 4, 'Air conditioning not working', 'The AC unit is not cooling properly despite being set to the lowest temperature', 'Heating/Cooling', 'high', 'in_progress', NOW());

-- Insert sample service providers
INSERT INTO `service_providers` (`id`, `name`, `email`, `phone`, `specialty`, `description`, `hourly_rate`, `rating`, `status`, `created_at`) VALUES
(1, 'Quick Fix Plumbing', 'contact@quickfixplumbing.lk', '+94112345678', 'Plumbing', 'Professional plumbing services for residential and commercial properties', 3500.00, 4.50, 'active', NOW()),
(2, 'Cool Air HVAC Services', 'info@coolair.lk', '+94112345679', 'HVAC', 'Air conditioning installation, repair, and maintenance services', 4000.00, 4.80, 'active', NOW()),
(3, 'Bright Spark Electricians', 'hello@brightspark.lk', '+94112345680', 'Electrical', 'Licensed electricians for all electrical work', 3800.00, 4.60, 'active', NOW());

-- Insert sample maintenance requests
INSERT INTO `maintenance_requests` (`id`, `property_id`, `landlord_id`, `issue_id`, `provider_id`, `requester_id`, `title`, `description`, `category`, `priority`, `status`, `estimated_cost`, `scheduled_date`, `created_at`) VALUES
(1, 4, 2, 1, 1, 4, 'Fix leaking kitchen faucet', 'Replace or repair the leaking faucet in the kitchen', 'Plumbing', 'medium', 'scheduled', 5000.00, '2025-11-15', NOW()),
(2, 4, 2, 2, 2, 4, 'Repair air conditioning unit', 'Diagnose and fix the malfunctioning AC unit', 'HVAC', 'high', 'in_progress', 8000.00, '2025-11-14', NOW());

-- Insert sample inspections
INSERT INTO `inspections` (`id`, `property`, `issues`, `type`, `scheduled_date`, `status`, `created_at`) VALUES
(1, '123 Main Street, Colombo 03, Sri Lanka', 0, 'routine', '2025-12-01', 'scheduled', NOW()),
(2, '456 Ocean View Road, Colombo 03, Sri Lanka', 0, 'move_in', '2025-11-20', 'scheduled', NOW());

-- Insert sample reviews
INSERT INTO `reviews` (`id`, `reviewer_id`, `reviewee_id`, `property_id`, `booking_id`, `rating`, `review_text`, `review_type`, `status`, `created_at`) VALUES
(1, 4, 2, 4, 1, 5, 'Excellent landlord! Very responsive to maintenance requests and the property was exactly as described.', 'property', 'approved', NOW());

-- Insert sample notifications
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 4, 'payment', 'Rent Payment Due Soon', 'Your rent payment of LKR 25,000.00 is due on February 1, 2025', '/tenant/pay_rent', 0, NOW()),
(2, 2, 'booking', 'New Booking Request', 'Bob Renter has requested to book your property at 123 Main Street', '/landlord/bookings', 0, NOW()),
(3, 4, 'issue', 'Issue Status Updated', 'Your issue "Air conditioning not working" status has been updated to: in_progress', '/issues/track_issues', 1, NOW());

-- Insert sample messages
INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `property_id`, `subject`, `message`, `parent_message_id`, `is_read`, `created_at`) VALUES
(1, 5, 2, 1, 'Inquiry about 123 Main Street property', 'Hello, I am interested in viewing the property at 123 Main Street. Is it still available?', NULL, 1, NOW()),
(2, 2, 5, 1, 'Re: Inquiry about 123 Main Street property', 'Yes, the property is still available. Would you like to schedule a viewing?', 1, 0, NOW());

-- Insert sample policies
INSERT INTO `policies` (`policy_id`, `policy_name`, `policy_category`, `policy_content`, `policy_version`, `policy_status`, `effective_date`, `created_by`, `created_at`) VALUES
(1, 'Privacy Policy', 'privacy', 'This Privacy Policy describes how Rentigo collects, uses, and protects your personal information...', '1.0', 'active', '2025-01-01', 1, NOW()),
(2, 'Terms of Service', 'terms_of_service', 'By using Rentigo, you agree to these Terms of Service...', '1.0', 'active', '2025-01-01', 1, NOW()),
(3, 'Refund Policy', 'refund', 'Our refund policy outlines the conditions under which refunds may be issued...', '1.0', 'active', '2025-01-01', 1, NOW());

-- ============================================================================
-- FINAL NOTES
-- ============================================================================
-- All tables use InnoDB engine for transaction support and foreign key constraints
-- All foreign keys are properly defined with appropriate ON DELETE actions
-- Indexes are created for frequently queried columns
-- Default admin password is 'password' (hashed with bcrypt)
-- All sample user passwords are 'password' (for testing purposes only)
-- The database includes ONE admin account as required

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
