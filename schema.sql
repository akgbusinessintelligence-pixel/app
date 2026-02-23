-- Rental Application Database Schema (AppFolio Clone)

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  -- Basic Info
  `unit` varchar(100) NOT NULL,
  `move_in` date NOT NULL,
  
  -- Personal Info
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `sms_opt_in` tinyint(1) DEFAULT 0,
  `ssn_hash` varchar(255) NOT NULL,
  `gov_id` varchar(100) DEFAULT NULL,
  `id_state` varchar(100) DEFAULT NULL,

  -- Household
  `co_enabled` tinyint(1) DEFAULT 0,
  `has_dependents` tinyint(1) DEFAULT 0,
  `dependents_info` text DEFAULT NULL,
  `has_pets` tinyint(1) DEFAULT 0,
  `pets_info` text DEFAULT NULL,
  `has_vehicles` tinyint(1) DEFAULT 0,
  `vehicles_info` text DEFAULT NULL,

  -- Employment Summary
  `has_employment` tinyint(1) DEFAULT 0,
  `total_income` decimal(10,2) DEFAULT NULL,
  `additional_income` decimal(10,2) DEFAULT NULL,
  `income_source` varchar(255) DEFAULT NULL,

  -- Questionnaire
  `evicted` enum('Yes','No') DEFAULT 'No',
  `judgments` enum('Yes','No') DEFAULT 'No',
  `criminal` enum('Yes','No') DEFAULT 'No',
  `bg_documents_opt` tinyint(1) DEFAULT 0,
  `referral_source` varchar(100) DEFAULT NULL,

  -- Meta
  `pdf_path` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `authorized` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Multi-entry tables for history
CREATE TABLE IF NOT EXISTS `application_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `type` enum('current', 'previous') DEFAULT 'current',
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `rent` decimal(10,2) DEFAULT NULL,
  `move_in` varchar(7) DEFAULT NULL, -- YYYY-MM
  `reason` text DEFAULT NULL,
  `landlord` varchar(255) DEFAULT NULL,
  `landlord_phone` varchar(50) DEFAULT NULL,
  `landlord_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_addr_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `application_employment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `employer` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `supervisor` varchar(255) DEFAULT NULL,
  `supervisor_phone` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_emp_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `application_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  CONSTRAINT `application_files_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
