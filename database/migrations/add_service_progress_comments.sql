-- Migration: Add Service Progress Comments Table
-- Purpose: Store comments for repair progress tracking
-- Created: December 16, 2025

-- Create the service_progress_comments table
CREATE TABLE IF NOT EXISTS `service_progress_comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT NOT NULL,
  `progress_key` VARCHAR(50) NOT NULL,
  `comment_text` LONGTEXT NOT NULL,
  `created_by` INT DEFAULT NULL,
  `created_by_name` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Indexes for better query performance
  INDEX `idx_report_id` (`report_id`),
  INDEX `idx_progress_key` (`progress_key`),
  INDEX `idx_report_progress` (`report_id`, `progress_key`),
  INDEX `idx_created_by` (`created_by`),
  INDEX `idx_created_at` (`created_at`),
  
  -- Foreign key constraint
  CONSTRAINT `fk_progress_comments_report` FOREIGN KEY (`report_id`) 
    REFERENCES `service_reports`(`report_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Add missing columns to an OLDER service_progress_comments table.
--
-- The CREATE TABLE above is a no-op when the table already exists, so a
-- database created before these columns were added keeps failing. MySQL 8.0
-- has no "ADD COLUMN IF NOT EXISTS", so each column is added only when it is
-- actually missing. Safe to run more than once.
--
-- This mirrors the runtime self-heal in
-- public/backend/api/service_report_api.php (same definitions, same order).
-- ---------------------------------------------------------------------------

-- comment_text
SET @ddl := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_progress_comments'
      AND COLUMN_NAME = 'comment_text') = 0,
  'ALTER TABLE `service_progress_comments` ADD COLUMN `comment_text` LONGTEXT NULL',
  'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_by
SET @ddl := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_progress_comments'
      AND COLUMN_NAME = 'created_by') = 0,
  'ALTER TABLE `service_progress_comments` ADD COLUMN `created_by` INT NULL',
  'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_by_name
SET @ddl := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_progress_comments'
      AND COLUMN_NAME = 'created_by_name') = 0,
  'ALTER TABLE `service_progress_comments` ADD COLUMN `created_by_name` VARCHAR(255) NULL',
  'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_at
SET @ddl := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_progress_comments'
      AND COLUMN_NAME = 'created_at') = 0,
  'ALTER TABLE `service_progress_comments` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP',
  'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- updated_at
SET @ddl := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_progress_comments'
      AND COLUMN_NAME = 'updated_at') = 0,
  'ALTER TABLE `service_progress_comments` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
  'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Verify table creation
SELECT 'Service Progress Comments table created successfully' AS status;
