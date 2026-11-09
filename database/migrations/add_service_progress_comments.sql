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

-- Verify table creation
SELECT 'Service Progress Comments table created successfully' AS status;
