-- Archive Records Table Migration
-- This table stores all deleted records for recovery purposes

CREATE TABLE IF NOT EXISTS `archive_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `deleted_data` longtext NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_name` (`table_name`),
  KEY `record_id` (`record_id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_table_record ON archive_records(table_name, record_id);
CREATE INDEX IF NOT EXISTS idx_deleted_at ON archive_records(deleted_at DESC);
CREATE INDEX IF NOT EXISTS idx_deleted_by ON archive_records(deleted_by);
