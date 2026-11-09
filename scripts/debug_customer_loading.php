<?php
/**
 * Debug Script: Check Staff Service Report Customer Loading
 * Purpose: Verify if API data is being loaded correctly into the form
 */

require_once __DIR__ . '/backend/handlers/authHandler.php';

// Check if user is logged in and is staff
$auth = new AuthHandler();
if (!$auth->isLoggedIn()) {
    die(json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]));
}

$role = $auth->getRole();
if ($role !== 'staff') {
    die(json_encode([
        'success' => false,
        'message' => 'Not a staff user'
    ]));
}

// Test 1: Check if API endpoint works
echo json_encode([
    'step' => 'TEST 1: API Endpoint Check',
    'description' => 'Attempting to reach customer_appliance_api.php...'
], JSON_PRETTY_PRINT);
echo "\n\n";

// Simulate API call
$api_response = @file_get_contents('http://localhost/RepairSystem-main/backend/api/customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=5');

if ($api_response === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to reach API endpoint',
        'url' => 'http://localhost/RepairSystem-main/backend/api/customer_appliance_api.php?action=getAllCustomers'
    ], JSON_PRETTY_PRINT);
} else {
    $data = json_decode($api_response, true);
    echo json_encode([
        'success' => true,
        'api_response' => $data
    ], JSON_PRETTY_PRINT);
}

echo "\n\n";

// Test 2: Check form HTML
echo json_encode([
    'step' => 'TEST 2: Form HTML Check',
    'description' => 'Checking if form elements exist...'
], JSON_PRETTY_PRINT);
echo "\n\n";

$form_path = __DIR__ . '/staff/staff_service_report.php';
if (file_exists($form_path)) {
    $form_content = file_get_contents($form_path);
    
    $checks = [
        'customer-search' => 'id="customer-search"' !== strpos($form_content, 'id="customer-search"'),
        'customer-select' => 'id="customer-select"' !== strpos($form_content, 'id="customer-select"'),
        'customer-suggestions' => 'id="customer-suggestions"' !== strpos($form_content, 'id="customer-suggestions"'),
        'loadInitialData' => 'loadInitialData' !== strpos($form_content, 'loadInitialData'),
        'renderCustomerSuggestions' => 'renderCustomerSuggestions' !== strpos($form_content, 'renderCustomerSuggestions')
    ];
    
    echo json_encode([
        'form_exists' => true,
        'elements_found' => $checks
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'error' => 'Form file not found',
        'path' => $form_path
    ], JSON_PRETTY_PRINT);
}

echo "\n\n";

// Test 3: Summary
echo json_encode([
    'step' => 'SUMMARY',
    'next_steps' => [
        '1. Open staff/staff_service_report.php in browser',
        '2. Open browser Developer Console (F12)',
        '3. Check Console tab for any JavaScript errors',
        '4. Check Network tab to see if customer_appliance_api.php is called',
        '5. Click on customer search input to trigger suggestions',
        '6. Verify window.customersList is populated'
    ]
], JSON_PRETTY_PRINT);
?>
