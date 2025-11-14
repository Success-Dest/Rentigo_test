-- ============================================================================
-- RENTIGO COMPLETE DATABASE SCHEMA
-- Merges old working database with new features
-- Maintains backward compatibility with existing CRUD operations
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- USERS TABLE (Enhanced with both old and new fields)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_type` enum('admin','property_manager','tenant','landlord') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'tenant',
  `account_status` enum('pending','active','suspended','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `terms_version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '1.0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_account_status` (`account_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- PROPERTIES TABLE (Merged - keeps ALL old columns + new ones)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `landlord_id` int NOT NULL,
  `manager_id` int DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,

  -- OLD COLUMNS (keep for backward compatibility)
  `property_purpose` enum('rent','maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rent',
  `listing_type` enum('rent','maintenance','rental') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rent',
  `current_occupant` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,

  -- Property details
  `bedrooms` int NOT NULL,
  `bathrooms` decimal(2,1) NOT NULL,
  `sqft` int DEFAULT NULL,
  `rent` decimal(10,2) NOT NULL,
  `deposit` decimal(10,2) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,

  -- Status fields (keep both for compatibility)
  `status` enum('available','occupied','maintenance','vacant','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,

  -- Amenities
  `available_date` date DEFAULT NULL,
  `parking` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `pet_policy` enum('no','cats','dogs','both') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `laundry` enum('none','shared','in_unit','hookups','included') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',

  -- Timestamps
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `landlord_id` (`landlord_id`),
  KEY `manager_id` (`manager_id`),
  KEY `idx_listing_type` (`listing_type`),
  KEY `idx_status` (`status`),
  KEY `idx_approval_status` (`approval_status`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MARKET PROPERTIES TABLE (Keep as is - working)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `market_properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `landlord_id` int NOT NULL DEFAULT '1',
  `manager_id` int DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_type` enum('apartment','house','condo','townhouse') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bedrooms` int NOT NULL,
  `bathrooms` decimal(2,1) NOT NULL,
  `sqft` int DEFAULT NULL,
  `rent` decimal(10,2) NOT NULL,
  `deposit` decimal(10,2) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('available','occupied','maintenance','vacant','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `available_date` date DEFAULT NULL,
  `parking` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `pet_policy` enum('no','cats','dogs','both') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `laundry` enum('none','shared','in_unit','hookups','included') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type_bedrooms` (`property_type`,`bedrooms`),
  KEY `idx_rent` (`rent`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROPERTY MANAGER TABLE (Keep old structure)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `property_manager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `employee_id_document` longblob,
  `employee_id_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id_filetype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id_filesize` int DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `property_manager_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `property_manager_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ISSUES TABLE (Keep old structure with flexible categories)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` enum('low','medium','high','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_issues_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_issues_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- INSPECTIONS TABLE (Keep old structure)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `inspections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `issues` int DEFAULT '0',
  `type` enum('routine','move_in','move_out','maintenance','annual','emergency','issue') COLLATE utf8mb4_general_ci NOT NULL,
  `scheduled_date` date NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled','pending') COLLATE utf8mb4_general_ci DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_scheduled_date` (`scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SERVICE PROVIDERS TABLE (Keep old structure)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `service_providers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `specialty` enum('plumbing','electrical','hvac','general','cleaning','landscaping') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `rating` decimal(3,2) DEFAULT '0.00',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_specialty` (`specialty`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- POLICIES TABLE (Keep old structure with both enum sets)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `policies` (
  `policy_id` int NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `policy_category` enum('rental','security','maintenance','financial','general','privacy','terms_of_service','refund','data_protection') COLLATE utf8mb4_general_ci NOT NULL,
  `policy_description` text COLLATE utf8mb4_general_ci,
  `policy_content` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `policy_version` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'v1.0',
  `policy_status` enum('draft','active','inactive','archived','under_review') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `policy_type` enum('standard','custom') COLLATE utf8mb4_general_ci DEFAULT 'standard',
  `effective_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`policy_id`),
  KEY `idx_policy_status` (`policy_status`),
  KEY `idx_policy_category` (`policy_category`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `policies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- NEW TABLES FOR ENHANCED FEATURES
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

-- Posts Table (Keep for backward compatibility)
CREATE TABLE IF NOT EXISTS `Posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- View for Posts (Keep for backward compatibility)
CREATE OR REPLACE VIEW `v_posts` AS
SELECT
  `posts`.`id` AS `post_id`,
  `users`.`id` AS `user_id`,
  `users`.`name` AS `user_name`,
  `posts`.`title` AS `title`,
  `posts`.`body` AS `body`,
  `posts`.`created_at` AS `post_created_at`,
  `users`.`created_at` AS `user_created_at`
FROM (`posts` JOIN `users` ON((`posts`.`user_id` = `users`.`id`)))
ORDER BY `posts`.`created_at` DESC;

COMMIT;
