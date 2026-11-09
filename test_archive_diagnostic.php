<?php
/**
 * Archive History Diagnostic Test
 * Tests if the archive history functionality is working properly
 */

session_start();
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/backend/handlers/Database.php';
require_once __DIR__ . '/backend/handlers/archiveHandler.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Archive History Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .test { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .test.success { border-left-color: #4CAF50; }
        .test.error { border-left-color: #f44336; }
        .test.warning { border-left-color: #ff9800; }
        h1 { color: #333; }
        h3 { color: #555; margin-top: 0; }
        .status { font-weight: bold; padding: 5px 10px; border-radius: 3px; display: inline-block; }
        .status.pass { background: #4CAF50; color: white; }
        .status.fail { background: #f44336; color: white; }
        .status.info { background: #2196F3; color: white; }
        .details { background: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 3px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📊 Archive History Diagnostic Report</h1>
        <p>Generated: " . date('Y-m-d H:i:s') . "</p>
";

$testsPassed = 0;
$testsFailed = 0;

// Test 1: Database Connection
echo "<div class='test'>";
try {
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "<h3><span class='status pass'>✓ PASS</span> Database Connection</h3>";
        echo "<p>Successfully connected to database</p>";
        $testsPassed++;
    }
} catch (Exception $e) {
    echo "<h3><span class='status fail'>✗ FAIL</span> Database Connection</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    $testsFailed++;
    $db = null;
}
echo "</div>";

// Test 2: Archive Table Exists
if ($db) {
    echo "<div class='test'>";
    try {
        $query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'archive_history'";
        $result = mysqli_query($db, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<h3><span class='status pass'>✓ PASS</span> Archive History Table Exists</h3>";
            echo "<p>Table 'archive_history' found in database</p>";
            $testsPassed++;
        } else {
            echo "<h3><span class='status fail'>✗ FAIL</span> Archive History Table</h3>";
            echo "<p>Table 'archive_history' not found in database</p>";
            $testsFailed++;
        }
    } catch (Exception $e) {
        echo "<h3><span class='status fail'>✗ FAIL</span> Archive History Table Check</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        $testsFailed++;
    }
    echo "</div>";
}

// Test 3: Archive Records Count
if ($db) {
    echo "<div class='test'>";
    try {
        $query = "SELECT COUNT(*) as count FROM archive_history";
        $result = mysqli_query($db, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'] ?? 0;
            echo "<h3><span class='status info'>ℹ INFO</span> Archive Records Count</h3>";
            echo "<p><strong>Total Archived Records:</strong> " . $count . "</p>";
            $testsPassed++;
            
            if ($count > 0) {
                // Show sample records
                $sampleQuery = "SELECT id, record_id, table_name, deleted_at, deleted_by FROM archive_history ORDER BY deleted_at DESC LIMIT 5";
                $sampleResult = mysqli_query($db, $sampleQuery);
                if ($sampleResult && mysqli_num_rows($sampleResult) > 0) {
                    echo "<h4>Sample Recent Archives:</h4>";
                    echo "<table>";
                    echo "<tr><th>Archive ID</th><th>Record ID</th><th>Table</th><th>Deleted At</th><th>Deleted By</th></tr>";
                    while ($sample = mysqli_fetch_assoc($sampleResult)) {
                        echo "<tr>";
                        echo "<td>#" . $sample['id'] . "</td>";
                        echo "<td>#" . $sample['record_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($sample['table_name']) . "</td>";
                        echo "<td>" . $sample['deleted_at'] . "</td>";
                        echo "<td>" . ($sample['deleted_by'] ?: 'System') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
    } catch (Exception $e) {
        echo "<h3><span class='status fail'>✗ FAIL</span> Archive Records Count</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        $testsFailed++;
    }
    echo "</div>";
}

// Test 4: Archive Handler
echo "<div class='test'>";
try {
    if ($db) {
        $archiveHandler = new ArchiveHandler($db);
        echo "<h3><span class='status pass'>✓ PASS</span> Archive Handler Initialized</h3>";
        echo "<p>ArchiveHandler class loaded successfully</p>";
        $testsPassed++;
        
        // Test getArchivedRecords method
        $result = $archiveHandler->getArchivedRecords(1, 5);
        echo "<h4>Archive Handler Test Results:</h4>";
        echo "<div class='details'>";
        echo "Total Items: " . ($result['total_items'] ?? 0) . "<br>";
        echo "Total Pages: " . ($result['total_pages'] ?? 0) . "<br>";
        echo "Records Retrieved: " . count($result['archives'] ?? []);
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<h3><span class='status fail'>✗ FAIL</span> Archive Handler</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<div class='details'>" . $e->getTraceAsString() . "</div>";
    $testsFailed++;
}
echo "</div>";

// Test 5: API File Exists
echo "<div class='test'>";
$apiPath = __DIR__ . '/backend/api/archive_history_api.php';
if (file_exists($apiPath)) {
    echo "<h3><span class='status pass'>✓ PASS</span> API File Exists</h3>";
    echo "<p>File: archive_history_api.php</p>";
    echo "<p>Path: /backend/api/archive_history_api.php</p>";
    $testsPassed++;
} else {
    echo "<h3><span class='status fail'>✗ FAIL</span> API File Not Found</h3>";
    echo "<p>Expected path: /backend/api/archive_history_api.php</p>";
    $testsFailed++;
}
echo "</div>";

// Test 6: View File Exists
echo "<div class='test'>";
$viewPath = __DIR__ . '/views/archive_history_new.php';
if (file_exists($viewPath)) {
    echo "<h3><span class='status pass'>✓ PASS</span> View File Exists</h3>";
    echo "<p>File: archive_history_new.php</p>";
    echo "<p>Path: /views/archive_history_new.php</p>";
    
    // Check if the new Record Details column is in the file
    $viewContent = file_get_contents($viewPath);
    if (strpos($viewContent, 'Record Details') !== false) {
        echo "<p><strong>✓ Record Details Column Found</strong> - Recent changes are present</p>";
    } else {
        echo "<p><strong>⚠ Record Details Column Not Found</strong> - File may need updating</p>";
    }
    
    $testsPassed++;
} else {
    echo "<h3><span class='status fail'>✗ FAIL</span> View File Not Found</h3>";
    echo "<p>Expected path: /views/archive_history_new.php</p>";
    $testsFailed++;
}
echo "</div>";

// Summary
echo "<div class='test'>";
echo "<h3>📋 Diagnostic Summary</h3>";
echo "<table>";
echo "<tr><td><strong>Tests Passed:</strong></td><td><span class='status pass'>" . $testsPassed . "</span></td></tr>";
echo "<tr><td><strong>Tests Failed:</strong></td><td><span class='status fail'>" . $testsFailed . "</span></td></tr>";
echo "<tr><td><strong>Total Tests:</strong></td><td>" . ($testsPassed + $testsFailed) . "</td></tr>";
echo "<tr><td><strong>Success Rate:</strong></td><td>" . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 1) . "%</td></tr>";
echo "</table>";

if ($testsFailed === 0) {
    echo "<p style='color: #4CAF50; font-weight: bold;'>✓ All systems operational! Archive history is working correctly.</p>";
} else {
    echo "<p style='color: #f44336; font-weight: bold;'>✗ Some issues detected. Please review the failed tests above.</p>";
}

echo "</div>";

echo "
    </div>
</body>
</html>
";
?>
