<?php
/**
 * Database Setup Script for Service Progress Comments
 * This script creates the service_progress_comments table if it doesn't exist
 * and verifies the database schema
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../backend/handlers/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn || $conn->connect_error) {
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }
    
    echo "✅ Database connected successfully\n\n";
    
    // Create the service_progress_comments table
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS `service_progress_comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `report_id` INT NOT NULL,
            `progress_key` VARCHAR(50) NOT NULL,
            `comment_text` LONGTEXT NOT NULL,
            `created_by` INT DEFAULT NULL,
            `created_by_name` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            KEY `idx_report_id` (`report_id`),
            KEY `idx_progress_key` (`progress_key`),
            KEY `idx_report_progress` (`report_id`, `progress_key`),
            KEY `idx_created_by` (`created_by`),
            KEY `idx_created_at` (`created_at`),
            
            CONSTRAINT `fk_progress_comments_report` FOREIGN KEY (`report_id`) 
                REFERENCES `service_reports`(`report_id`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createTableQuery)) {
        echo "✅ Table 'service_progress_comments' created/verified successfully\n";
    } else {
        echo "❌ Error creating table: " . $conn->error . "\n";
        exit(1);
    }
    
    // Verify table structure
    $describeQuery = "DESCRIBE service_progress_comments";
    $result = $conn->query($describeQuery);
    
    if ($result) {
        echo "\n📋 Table Structure:\n";
        echo str_repeat("-", 80) . "\n";
        
        while ($row = $result->fetch_assoc()) {
            printf("%-20s | %-15s | %s\n", 
                $row['Field'], 
                $row['Type'], 
                ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL')
            );
        }
        echo str_repeat("-", 80) . "\n";
    }
    
    // Verify foreign key constraint
    $fkQuery = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'service_progress_comments' AND COLUMN_NAME = 'report_id'";
    $fkResult = $conn->query($fkQuery);
    
    if ($fkResult && $fkResult->num_rows > 0) {
        echo "\n✅ Foreign Key Constraint verified\n";
        while ($row = $fkResult->fetch_assoc()) {
            echo "   Constraint Name: " . $row['CONSTRAINT_NAME'] . "\n";
        }
    }
    
    // Count existing comments
    $countQuery = "SELECT COUNT(*) as total FROM service_progress_comments";
    $countResult = $conn->query($countQuery);
    $countRow = $countResult->fetch_assoc();
    
    echo "\n📊 Database Statistics:\n";
    echo "   Total Comments: " . $countRow['total'] . "\n";
    
    echo "\n✅ Setup Complete! The service progress comments system is ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
    exit(1);
}
?>
