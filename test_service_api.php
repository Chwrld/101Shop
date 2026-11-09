<?php
// Test file for service report API debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Service Report API Test</h1>";

// Test database connection first
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once __DIR__ . '/backend/handlers/Database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green'>✓ Database connected successfully</p>";
    
    // Check if tables exist
    $tables = ['service_reports', 'service_details', 'parts_used'];
    echo "<h3>Tables Check:</h3><ul>";
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<li style='color: green'>✓ Table '$table' exists</li>";
            
            // Count records
            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($count_result) {
                $row = $count_result->fetch_assoc();
                echo " <span style='color: blue'>(" . $row['count'] . " records)</span>";
            }
        } else {
            echo "<li style='color: red'>✗ Table '$table' NOT FOUND</li>";
        }
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Test API endpoint
echo "<h2>2. Service API Test (getAll)</h2>";
try {
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/backend/api/service_api.php?action=getAll';
    echo "<p><strong>API URL:</strong> " . htmlspecialchars($api_url) . "</p>";
    
    // Initialize cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> " . $http_code . "</p>";
    
    if ($curl_error) {
        echo "<p style='color: red'>✗ CURL Error: " . htmlspecialchars($curl_error) . "</p>";
    } else {
        echo "<p style='color: green'>✓ API responded successfully</p>";
        echo "<h3>Response:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow: auto;'>";
        
        // Try to format JSON
        $json_data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo htmlspecialchars(json_encode($json_data, JSON_PRETTY_PRINT));
            
            if (isset($json_data['success']) && $json_data['success']) {
                $count = is_array($json_data['data']) ? count($json_data['data']) : 0;
                echo "</pre><p style='color: green'><strong>✓ Success!</strong> Found $count service reports</p>";
                
                if ($count > 0) {
                    echo "<h3>Sample Record:</h3>";
                    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
                    echo htmlspecialchars(json_encode($json_data['data'][0], JSON_PRETTY_PRINT));
                    echo "</pre>";
                }
            } else {
                echo "</pre><p style='color: orange'><strong>⚠ API returned success=false</strong></p>";
                if (isset($json_data['message'])) {
                    echo "<p>Message: " . htmlspecialchars($json_data['message']) . "</p>";
                }
            }
        } else {
            echo htmlspecialchars($response);
            echo "</pre><p style='color: red'>✗ Invalid JSON response</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Test error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Direct database query test
echo "<h2>3. Direct Database Query Test</h2>";
try {
    require_once __DIR__ . '/backend/handlers/serviceHandler.php';
    $serviceHandler = new ServiceHandler($conn);
    $result = $serviceHandler->getAll(10, 0);
    
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
    echo "</pre>";
    
    if ($result['success']) {
        $count = is_array($result['data']) ? count($result['data']) : 0;
        echo "<p style='color: green'><strong>✓ Direct query successful!</strong> Found $count records</p>";
    } else {
        echo "<p style='color: red'><strong>✗ Direct query failed:</strong> " . htmlspecialchars($result['message']) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Direct query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check for SQL errors
echo "<h2>4. Recent MySQL Errors</h2>";
if ($conn->error) {
    echo "<p style='color: red'>MySQL Error: " . htmlspecialchars($conn->error) . "</p>";
} else {
    echo "<p style='color: green'>No MySQL errors</p>";
}

echo "<hr>";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><a href='diagnostic.php'>← Back to Diagnostic</a> | <a href='index.php'>← Back to Login</a></p>";
?>
