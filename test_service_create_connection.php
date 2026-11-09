<?php
// Test script to verify service report creation connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Service Report Creation Connection</h2>";

// Test 1: Database Connection
echo "<h3>1. Testing Database Connection</h3>";
try {
    require_once __DIR__ . '/backend/handlers/Database.php';
    $database = new Database();
    $db = $database->getConnection();
    if ($db->connect_error) {
        echo "❌ Database connection failed: " . $db->connect_error . "<br>";
    } else {
        echo "✅ Database connected successfully<br>";
        echo "Server info: " . $db->server_info . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Database exception: " . $e->getMessage() . "<br>";
}

// Test 2: Check if required tables exist
echo "<h3>2. Testing Required Tables</h3>";
$tables = ['service_reports', 'service_details', 'parts_used', 'parts', 'customers', 'appliances', 'staffs'];
foreach ($tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' NOT FOUND<br>";
    }
}

// Test 3: Check ServiceHandler class
echo "<h3>3. Testing ServiceHandler Class</h3>";
try {
    require_once __DIR__ . '/backend/handlers/serviceHandler.php';
    $serviceHandler = new ServiceHandler($db);
    echo "✅ ServiceHandler instantiated successfully<br>";
    
    // Test method existence
    $methods = ['createCompleteServiceReport', 'getById', 'getAll'];
    foreach ($methods as $method) {
        if (method_exists($serviceHandler, $method)) {
            echo "✅ Method '$method' exists<br>";
        } else {
            echo "❌ Method '$method' NOT FOUND<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ ServiceHandler exception: " . $e->getMessage() . "<br>";
}

// Test 4: Check PartsHandler class
echo "<h3>4. Testing PartsHandler Class</h3>";
try {
    require_once __DIR__ . '/backend/handlers/partsHandler.php';
    $partsHandler = new PartsHandler($db);
    echo "✅ PartsHandler instantiated successfully<br>";
    
    // Check if parts exist
    $result = $partsHandler->getAllParts();
    if ($result['success']) {
        echo "✅ Parts API working - " . count($result['data']) . " parts found<br>";
    } else {
        echo "❌ Parts API error: " . $result['message'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ PartsHandler exception: " . $e->getMessage() . "<br>";
}

// Test 5: Test API endpoint accessibility
echo "<h3>5. Testing API Endpoints</h3>";
$apis = [
    'service_api.php',
    'customer_appliance_api.php',
    'parts_api.php',
    'staff_api.php'
];

foreach ($apis as $api) {
    $path = __DIR__ . '/backend/api/' . $api;
    if (file_exists($path)) {
        echo "✅ API file '$api' exists<br>";
    } else {
        echo "❌ API file '$api' NOT FOUND<br>";
    }
}

// Test 6: Test Service_report class instantiation
echo "<h3>6. Testing Service_report Class</h3>";
try {
    $testReport = new Service_report(
        'Test Customer',
        'Test Appliance',
        new DateTime('2025-12-06'),
        'received',
        'Test Dealer'
    );
    echo "✅ Service_report class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ Service_report exception: " . $e->getMessage() . "<br>";
}

// Test 7: Test Service_detail class instantiation
echo "<h3>7. Testing Service_detail Class</h3>";
try {
    $testDetail = new Service_detail(
        ['repair'],
        0,
        null,
        null,
        'Test complaint',
        0,
        0,
        0,
        0
    );
    echo "✅ Service_detail class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ Service_detail exception: " . $e->getMessage() . "<br>";
}

// Test 8: Test Parts_used class instantiation
echo "<h3>8. Testing Parts_used Class</h3>";
try {
    $testParts = new Parts_used([]);
    echo "✅ Parts_used class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ Parts_used exception: " . $e->getMessage() . "<br>";
}

// Test 9: PHP Version check
echo "<h3>9. PHP Version Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "✅ PHP version is compatible<br>";
} else {
    echo "❌ PHP version is too old (need 7.4+)<br>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><strong>Next Step:</strong> If all tests pass, try creating a service report through the UI.</p>";
echo "<p><strong>Access via:</strong> <a href='http://localhost/RepairSystem-main/test_service_create_connection.php'>http://localhost/RepairSystem-main/test_service_create_connection.php</a></p>";
?>
