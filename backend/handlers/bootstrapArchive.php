<?php
/**
 * Bootstrap Archive System
 * Auto-creates archive_records table if it doesn't exist
 * Include this file in handlers that use archiving
 */

function ensureArchiveTableExists($db) {
    // Check if table exists
    $result = $db->query("SHOW TABLES LIKE 'archive_records'");
    
    if ($result && $result->num_rows > 0) {
        return true; // Table exists
    }
    
    // Create the table
    $createSQL = "CREATE TABLE IF NOT EXISTS `archive_records` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($createSQL)) {
        error_log('Archive table created successfully');
        
        // Create indexes
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_table_record ON archive_records(table_name, record_id)",
            "CREATE INDEX IF NOT EXISTS idx_deleted_by ON archive_records(deleted_by)"
        ];
        
        foreach ($indexes as $indexSQL) {
            try {
                $db->query($indexSQL);
            } catch (Exception $e) {
                // Ignore if index already exists
            }
        }
        
        return true;
    }
    
    error_log('Failed to create archive table: ' . $db->error);
    return false;
}
?>
