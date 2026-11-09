<?php
// Diagnostic page for production debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Diagnostic</h1>";
echo "<h2>Environment Check</h2>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check required extensions
$required_extensions = ['mysqli', 'pdo', 'pdo_mysql', 'json', 'mbstring'];
echo "<h3>Required Extensions:</h3><ul>";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✓ Loaded' : '✗ Missing';
    $color = extension_loaded($ext) ? 'green' : 'red';
    echo "<li style='color: $color'><strong>$ext:</strong> $status</li>";
}
echo "</ul>";

// Check environment variables (mask sensitive data)
echo "<h3>Environment Variables:</h3><ul>";
$env_vars = ['DB_HOST', 'DB_USER', 'DB_NAME', 'DATABASE_URL', 'MYSQL_HOST', 'MYSQL_USER', 'MYSQL_DATABASE'];
foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value) {
        if (strpos($var, 'PASSWORD') !== false || strpos($var, 'URL') !== false) {
            echo "<li><strong>$var:</strong> [SET - " . strlen($value) . " chars]</li>";
        } else {
            echo "<li><strong>$var:</strong> $value</li>";
        }
    } else {
        echo "<li style='color: gray'><strong>$var:</strong> Not set</li>";
    }
}
echo "</ul>";

// Test database configuration loading
echo "<h2>Database Configuration</h2>";
try {
    $config = include(__DIR__ . '/database/database.php');
    echo "<ul>";
    echo "<li><strong>Host:</strong> " . htmlspecialchars($config['host']) . "</li>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($config['username']) . "</li>";
    echo "<li><strong>Database:</strong> " . htmlspecialchars($config['dbname']) . "</li>";
    echo "<li><strong>Port:</strong> " . ($config['port'] ?? '3306') . "</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red'>Error loading config: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once __DIR__ . '/backend/handlers/Database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green'>✓ Database connection successful!</p>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM staffs");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green'>✓ Database query successful! Found " . $row['count'] . " staff members.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Error details:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Check file permissions
echo "<h2>File System Check</h2>";
$dirs_to_check = [
    'database' => __DIR__ . '/database',
    'backend/handlers' => __DIR__ . '/backend/handlers',
    'authentication' => __DIR__ . '/authentication',
];

echo "<ul>";
foreach ($dirs_to_check as $name => $path) {
    $exists = is_dir($path);
    $readable = is_readable($path);
    echo "<li><strong>$name:</strong> ";
    if ($exists && $readable) {
        echo "<span style='color: green'>✓ Accessible</span>";
    } elseif ($exists) {
        echo "<span style='color: orange'>⚠ Not readable</span>";
    } else {
        echo "<span style='color: red'>✗ Not found</span>";
    }
    echo "</li>";
}
echo "</ul>";

// Session check
echo "<h2>Session Check</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green'>✓ Sessions are working</p>";
} else {
    echo "<p style='color: red'>✗ Sessions not active</p>";
}

echo "<hr>";
echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
