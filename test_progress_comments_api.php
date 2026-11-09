<?php
/**
 * Test script for Progress Comments API
 * Tests both GET and POST endpoints with detailed error logging
 */

require 'bootstrap.php';
require 'backend/handlers/Database.php';

header('Content-Type: text/plain');

echo "=== PROGRESS COMMENTS API TEST ===\n\n";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn || $conn->connect_error) {
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }
    
    echo "✅ Database connected successfully\n\n";
    
    // Test 1: Check table existence
    echo "TEST 1: Checking table structure...\n";
    $result = $conn->query("SHOW TABLES LIKE 'service_progress_comments'");
    if ($result->num_rows > 0) {
        echo "✅ Table EXISTS\n";
        
        // Show table structure
        $desc = $conn->query("DESCRIBE service_progress_comments");
        echo "\nTable columns:\n";
        while ($row = $desc->fetch_assoc()) {
            echo "  - {$row['Field']} ({$row['Type']}) " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
        }
    } else {
        echo "⚠️  Table does not exist yet (will be created on first request)\n";
    }
    
    // Test 2: Get all service reports
    echo "\n\nTEST 2: Available service reports:\n";
    $reports = $conn->query("SELECT report_id, customer_name, status FROM service_reports LIMIT 5");
    if ($reports && $reports->num_rows > 0) {
        while ($row = $reports->fetch_assoc()) {
            echo "  - Report ID {$row['report_id']}: {$row['customer_name']} ({$row['status']})\n";
        }
    } else {
        echo "⚠️  No service reports found\n";
    }
    
    // Test 3: Simulate API - Get Comments for Report ID 16
    echo "\n\nTEST 3: Simulating getProgressComments API for report_id=16...\n";
    
    // Simulate the API function
    $report_id = 16;
    
    // Ensure table exists
    $ensureTableQuery = "
        CREATE TABLE IF NOT EXISTS service_progress_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            progress_key VARCHAR(50) NOT NULL,
            comment_text LONGTEXT NOT NULL,
            created_by INT NULL,
            created_by_name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_report_id (report_id),
            KEY idx_progress_key (progress_key),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if (!$conn->query($ensureTableQuery)) {
        echo "❌ Failed to create/verify table: " . $conn->error . "\n";
    } else {
        echo "✅ Table verified\n";
    }
    
    // Now fetch comments
    $query = "
        SELECT 
            id,
            report_id,
            progress_key,
            comment_text,
            created_by_name as created_by,
            created_at
        FROM service_progress_comments
        WHERE report_id = ?
        ORDER BY created_at ASC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error . "\n";
    } else {
        echo "✅ Query prepared\n";
        
        $stmt->bind_param('i', $report_id);
        if (!$stmt->execute()) {
            echo "❌ Execute failed: " . $stmt->error . "\n";
        } else {
            echo "✅ Query executed\n";
            
            $result = $stmt->get_result();
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            echo "✅ Found " . count($comments) . " comments for report ID 16\n";
            if (count($comments) > 0) {
                foreach ($comments as $comment) {
                    echo "   - [{$comment['progress_key']}] {$comment['created_by']}: {$comment['comment_text']}\n";
                }
            }
            
            $stmt->close();
        }
    }
    
    // Test 4: Test INSERT simulation
    echo "\n\nTEST 4: Simulating addProgressComment API...\n";
    
    $test_report_id = 16;
    $test_progress_key = 'test_progress';
    $test_comment = 'This is a test comment';
    $test_created_by_name = 'Test User';
    $test_created_by = NULL;
    
    $insertQuery = "
        INSERT INTO service_progress_comments 
        (report_id, progress_key, comment_text, created_by, created_by_name) 
        VALUES (?, ?, ?, ?, ?)
    ";
    
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error . "\n";
    } else {
        echo "✅ Insert query prepared\n";
        
        // Bind parameters: i=int, s=string, s=string, i=int, s=string
        $stmt->bind_param('isssi', $test_report_id, $test_progress_key, $test_comment, $test_created_by_name, $test_created_by);
        
        if (!$stmt->execute()) {
            echo "❌ Execute failed: " . $stmt->error . "\n";
        } else {
            echo "✅ Test comment inserted successfully (ID: {$stmt->insert_id})\n";
        }
        
        $stmt->close();
    }
    
    // Test 5: Verify inserted comment
    echo "\n\nTEST 5: Verifying inserted comment...\n";
    
    $verifyQuery = "
        SELECT 
            id,
            report_id,
            progress_key,
            comment_text,
            created_by_name,
            created_at
        FROM service_progress_comments
        WHERE report_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    if ($stmt) {
        $stmt->bind_param('i', $test_report_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo "✅ Latest comment found:\n";
                echo "   ID: {$row['id']}\n";
                echo "   Report: {$row['report_id']}\n";
                echo "   Progress Key: {$row['progress_key']}\n";
                echo "   Text: {$row['comment_text']}\n";
                echo "   Author: {$row['created_by_name']}\n";
                echo "   Created: {$row['created_at']}\n";
            }
        }
        $stmt->close();
    }
    
    echo "\n\n✅ ALL TESTS COMPLETED SUCCESSFULLY\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
    exit(1);
}

$conn->close();
?>
