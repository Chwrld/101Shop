<?php
/**
 * Simple test script to verify service API is working
 * Run from command line: php test_service_api.php
 */

// Set the working directory to the project root
chdir(__DIR__ . '/..');

// Include necessary files
require_once __DIR__ . '/../backend/handlers/Database.php';
require_once __DIR__ . '/../backend/handlers/serviceHandler.php';

echo "=== Service API Test ===\n\n";

// Test 1: Database Connection
echo "Test 1: Database Connection\n";
try {
    $database = new Database();
    $db = $database->getConnection();
    if ($db && !$db->connect_error) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed: " . $db->connect_error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: ServiceHandler instantiation
echo "\nTest 2: ServiceHandler instantiation\n";
try {
    $serviceHandler = new ServiceHandler($db);
    echo "✓ ServiceHandler instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Fetch all service reports
echo "\nTest 3: Fetch all service reports\n";
try {
    $result = $serviceHandler->getAll(10, 0);
    if ($result['success']) {
        $count = is_array($result['data']) ? count($result['data']) : 0;
        echo "✓ Successfully fetched $count service reports\n";
    } else {
        echo "✗ Failed: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== All tests completed ===\n";
?>
