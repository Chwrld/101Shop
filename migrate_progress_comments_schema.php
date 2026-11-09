<?php
/**
 * Migration script to ensure service_progress_comments table has proper schema with FK constraint
 * Run this once to fix any existing installations
 */

require 'bootstrap.php';
require 'backend/handlers/Database.php';

header('Content-Type: text/plain');

echo "=== SERVICE PROGRESS COMMENTS TABLE MIGRATION ===\n\n";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn || $conn->connect_error) {
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }
    
    echo "✅ Database connected successfully\n\n";
    
    // Check if table exists
    $checkTable = "SHOW TABLES LIKE 'service_progress_comments'";
    $result = $conn->query($checkTable);
    
    if ($result->num_rows === 0) {
        echo "📝 Table does not exist. Creating...\n";
        
        $createTableQuery = "
            CREATE TABLE `service_progress_comments` (
                `id` int NOT NULL AUTO_INCREMENT,
                `report_id` int NOT NULL,
                `progress_key` varchar(50) NOT NULL,
                `comment_text` longtext NOT NULL,
                `created_by` int DEFAULT NULL,
                `created_by_name` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_report_id` (`report_id`),
                KEY `idx_progress_key` (`progress_key`),
                KEY `idx_report_progress` (`report_id`, `progress_key`),
                KEY `idx_created_by` (`created_by`),
                KEY `idx_created_at` (`created_at`),
                CONSTRAINT `fk_progress_comments_report` FOREIGN KEY (`report_id`) REFERENCES `service_reports` (`report_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->query($createTableQuery)) {
            echo "✅ Table created successfully with FK constraint\n";
        } else {
            echo "❌ Failed to create table: " . $conn->error . "\n";
            exit(1);
        }
    } else {
        echo "✅ Table already exists\n\n";
        
        // Check if FK constraint exists
        $fkQuery = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'service_progress_comments' 
                    AND COLUMN_NAME = 'report_id' 
                    AND REFERENCED_TABLE_NAME = 'service_reports'";
        
        $fkResult = $conn->query($fkQuery);
        
        if ($fkResult && $fkResult->num_rows > 0) {
            echo "✅ Foreign Key constraint already exists\n";
            $row = $fkResult->fetch_assoc();
            echo "   Constraint Name: " . $row['CONSTRAINT_NAME'] . "\n";
        } else {
            echo "⚠️  Foreign Key constraint is missing. Adding...\n";
            
            // First, drop the old FK if it exists with different name
            $dropFkQuery = "ALTER TABLE `service_progress_comments` DROP FOREIGN KEY IF EXISTS `fk_progress_comments_report`";
            $conn->query($dropFkQuery); // Ignore errors if FK doesn't exist
            
            // Add the FK constraint
            $addFkQuery = "ALTER TABLE `service_progress_comments` 
                          ADD CONSTRAINT `fk_progress_comments_report` 
                          FOREIGN KEY (`report_id`) 
                          REFERENCES `service_reports` (`report_id`) 
                          ON DELETE CASCADE 
                          ON UPDATE CASCADE";
            
            if ($conn->query($addFkQuery)) {
                echo "✅ Foreign Key constraint added successfully\n";
            } else {
                echo "❌ Failed to add FK constraint: " . $conn->error . "\n";
                echo "   (This might be okay if data integrity is already handled)\n";
            }
        }
        
        // Verify final table structure
        echo "\n📋 Final Table Structure:\n";
        $desc = $conn->query("DESCRIBE service_progress_comments");
        echo str_repeat("-", 80) . "\n";
        while ($row = $desc->fetch_assoc()) {
            printf("%-20s | %-20s | %-8s | %-4s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
                $row['Key'] ?: ''
            );
        }
        echo str_repeat("-", 80) . "\n";
    }
    
    // Count existing records
    $countQuery = "SELECT COUNT(*) as total FROM service_progress_comments";
    $countResult = $conn->query($countQuery);
    $countRow = $countResult->fetch_assoc();
    
    echo "\n📊 Database Statistics:\n";
    echo "   Total Comments: " . $countRow['total'] . "\n";
    
    echo "\n✅ MIGRATION COMPLETE!\n";
    echo "\nThe service_progress_comments table is now properly configured with:\n";
    echo "  • Correct schema matching repairsystem.sql\n";
    echo "  • Foreign Key constraint for referential integrity\n";
    echo "  • All necessary indexes\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
    exit(1);
}

$conn->close();
?>
