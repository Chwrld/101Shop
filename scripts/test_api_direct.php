<?php
/**
 * Simulate an API request to test the service creation endpoint
 */

// Mock the $_SERVER and $_GET variables
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'create';

// Simulate JSON input
$testData = [
    'customer_name' => 'John Doe',
    'appliance_name' => 'Refrigerator',
    'date_in' => date('Y-m-d'),
    'status' => 'Pending',
    'dealer' => 'Test Dealer',
    'service_charge' => 100,
    'labor' => 50,
    'pullout_delivery' => 25,
    'parts_total_charge' => 0,
    'total_amount' => 175,
    'complaint' => 'Not cooling properly',
    'receptionist' => 'Test',
    'manager' => 'Manager',
    'technician' => 'Tech',
    'service_types' => ['repair'],
    'parts' => []
];

// Set up input stream for the API
$_POST = $testData;
$GLOBALS['STDIN_DATA'] = json_encode($testData);

// Capture output
ob_start();

// Change to the project directory
chdir(__DIR__ . '/..');

// Include the API file directly
try {
    require_once __DIR__ . '/../backend/api/service_api.php';
} catch (Exception $e) {
    ob_end_clean();
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

// Get output
$output = ob_get_clean();
echo $output;
?>
