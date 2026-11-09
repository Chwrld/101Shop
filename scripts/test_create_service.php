<?php
/**
 * Test script to verify service creation API works
 */

chdir(__DIR__ . '/..');

require_once __DIR__ . '/../backend/handlers/Database.php';
require_once __DIR__ . '/../backend/handlers/serviceHandler.php';
require_once __DIR__ . '/../backend/handlers/partsHandler.php';

echo "=== Service Creation Test ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db->connect_error) {
        echo "✗ Database connection failed: " . $db->connect_error . "\n";
        exit(1);
    }
    echo "✓ Database connected\n";
    
    $serviceHandler = new ServiceHandler($db);
    echo "✓ ServiceHandler created\n";
    
    // Create test data
    $report = new Service_report(
        'Test Customer',
        'Test Appliance',
        new DateTime('2024-12-04'),
        'Pending',
        'Test Dealer',
        null,
        null,
        'Test findings',
        'Test remarks',
        ['shop']
    );
    echo "✓ Service_report object created\n";
    
    $detail = new Service_detail(
        ['repair'],
        100.00,
        null,
        null,
        'Test complaint',
        50.00,
        25.00,
        0.00,
        175.00,
        'Test Receptionist',
        'Test Manager',
        'Test Technician',
        ''
    );
    echo "✓ Service_detail object created\n";
    
    $partsUsed = new Parts_used([]);
    echo "✓ Parts_used object created\n";
    
    // Try to create the service report
    $result = $serviceHandler->createCompleteServiceReport($report, $detail, $partsUsed);
    echo "✓ createCompleteServiceReport called\n";
    
    // Check result
    if ($result['success']) {
        echo "✓ Service report created successfully with ID: " . $result['data'] . "\n";
    } else {
        echo "✗ Failed to create service report: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";
?>
