<?php
require 'bootstrap.php';
require 'backend/handlers/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "=== DATABASE CHECK ===\n\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'service_progress_comments'");
    if ($result->num_rows > 0) {
        echo "✓ Table EXISTS: service_progress_comments\n";
    } else {
        echo "✗ Table MISSING: service_progress_comments\n";
    }
    echo "\n";
    
    // Check table structure
    echo "=== TABLE STRUCTURE ===\n\n";
    $desc = $conn->query("DESCRIBE service_progress_comments");
    if ($desc && $desc->num_rows > 0) {
        echo "Columns found:\n";
        while ($row = $desc->fetch_assoc()) {
            echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Could not retrieve table structure\n";
    }
    echo "\n";
    
    // Count comments
    echo "=== COMMENTS COUNT ===\n\n";
    $count = $conn->query("SELECT COUNT(*) as total FROM service_progress_comments");
    $row = $count->fetch_assoc();
    echo "Total comments in database: " . $row['total'] . "\n\n";
    
    // List recent comments
    echo "=== RECENT COMMENTS ===\n\n";
    $recent = $conn->query("SELECT * FROM service_progress_comments ORDER BY created_at DESC LIMIT 5");
    if ($recent && $recent->num_rows > 0) {
        while ($row = $recent->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Report: " . $row['report_id'] . " | Key: " . $row['progress_key'] . " | Author: " . $row['created_by_name'] . "\n";
            echo "  Text: " . substr($row['comment_text'], 0, 50) . "...\n";
            echo "  Created: " . $row['created_at'] . "\n\n";
        }
    } else {
        echo "No comments found in database\n\n";
    }
    
    // Check service_reports table
    echo "=== SERVICE_REPORTS TABLE ===\n\n";
    $reports = $conn->query("SELECT COUNT(*) as total FROM service_reports");
    $row = $reports->fetch_assoc();
    echo "Total reports: " . $row['total'] . "\n\n";
    
    // Check for FK constraint issues
    echo "=== FOREIGN KEY CONSTRAINT ===\n\n";
    $fk = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'service_progress_comments' AND COLUMN_NAME = 'report_id'");
    if ($fk && $fk->num_rows > 0) {
        $row = $fk->fetch_assoc();
        echo "✓ FK Constraint: " . $row['CONSTRAINT_NAME'] . "\n";
    } else {
        echo "✗ No FK constraint found\n";
    }
    echo "\n";
    
    // Check indexes
    echo "=== INDEXES ===\n\n";
    $indexes = $conn->query("SHOW INDEX FROM service_progress_comments");
    if ($indexes && $indexes->num_rows > 0) {
        while ($row = $indexes->fetch_assoc()) {
            echo "  - " . $row['Key_name'] . " on " . $row['Column_name'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
