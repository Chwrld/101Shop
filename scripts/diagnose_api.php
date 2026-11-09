#!/usr/bin/env php
<?php
/**
 * Comprehensive Staff Service Report API Diagnostics
 * Run: php scripts/diagnose_api.php
 */

echo "\n=== STAFF SERVICE REPORT API DIAGNOSTICS ===\n\n";

// Test 1: Check if API files exist
echo "[TEST 1] Checking API Files\n";
$api_files = [
    'backend/api/customer_appliance_api.php',
    'backend/api/parts_api.php',
    'backend/api/service_api.php',
    'backend/api/staff_api.php',
    'backend/api/service_price_api.php'
];

foreach ($api_files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        echo "[✓] Found: $file\n";
    } else {
        echo "[✗] Missing: $file\n";
    }
}

echo "\n[TEST 2] Checking Handler Files\n";
$handler_files = [
    'backend/handlers/customersHandler.php',
    'backend/handlers/partsHandler.php',
    'backend/handlers/serviceHandler.php',
    'backend/handlers/staffsHandler.php'
];

foreach ($handler_files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        echo "[✓] Found: $file\n";
    } else {
        echo "[✗] Missing: $file\n";
    }
}

// Test 2: Check database connection
echo "\n[TEST 3] Checking Database Connection\n";
try {
    $db = new mysqli('localhost', 'root', '', 'repairsystem');
    if ($db->connect_error) {
        echo "[✗] Database connection failed: " . $db->connect_error . "\n";
    } else {
        echo "[✓] Database connected successfully\n";
        
        // Check tables
        $tables = ['customers', 'appliances', 'parts', 'staffs', 'service_reports'];
        foreach ($tables as $table) {
            $result = $db->query("SELECT COUNT(*) as cnt FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "  - $table: {$row['cnt']} records\n";
            }
        }
    }
} catch (Exception $e) {
    echo "[✗] Database error: " . $e->getMessage() . "\n";
}

// Test 3: Check staff form file
echo "\n[TEST 4] Checking Staff Form File\n";
$form_file = __DIR__ . '/../staff/staff_service_report.php';
if (file_exists($form_file)) {
    echo "[✓] Form file exists\n";
    
    $content = file_get_contents($form_file);
    
    // Check for required elements
    $checks = [
        'id="customer-search"' => 'Customer search input',
        'id="customer-select"' => 'Hidden customer select',
        'id="customer-id"' => 'Hidden customer-id field',
        'loadInitialData' => 'loadInitialData function',
        'renderCustomerSuggestions' => 'renderCustomerSuggestions function',
        'CUSTOMER_APPLIANCE_API_URL' => 'API URL constant',
        'window.customersList' => 'Customer list global'
    ];
    
    foreach ($checks as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "  [✓] $description\n";
        } else {
            echo "  [✗] $description - NOT FOUND\n";
        }
    }
} else {
    echo "[✗] Form file not found: $form_file\n";
}

// Test 4: Test API calls directly
echo "\n[TEST 5] Testing API Endpoints\n";
$base_url = 'http://localhost/RepairSystem-main/backend/api/';
$endpoints = [
    'customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=5' => 'Get Customers',
    'customer_appliance_api.php?action=getAllAppliances&page=1&itemsPerPage=5' => 'Get Appliances',
    'parts_api.php?action=getAllParts&page=1&itemsPerPage=5' => 'Get Parts',
    'staff_api.php?action=getAllStaffs&page=1&itemsPerPage=5' => 'Get Staff',
    'service_price_api.php?action=getPrices' => 'Get Service Prices'
];

foreach ($endpoints as $endpoint => $desc) {
    $url = $base_url . $endpoint;
    $response = @file_get_contents($url);
    
    if ($response === false) {
        echo "[✗] $desc - Failed to reach\n";
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                $count = 0;
                if (isset($data['data'])) {
                    if (is_array($data['data'])) {
                        $count = count($data['data']);
                    } elseif (isset($data['data']['customers'])) {
                        $count = count($data['data']['customers']);
                    } elseif (isset($data['data']['appliances'])) {
                        $count = count($data['data']['appliances']);
                    } elseif (isset($data['data']['parts'])) {
                        $count = count($data['data']['parts']);
                    } elseif (isset($data['data']['staffs'])) {
                        $count = count($data['data']['staffs']);
                    }
                }
                echo "[✓] $desc - Success ($count items)\n";
            } else {
                echo "[⚠] $desc - Response: {$data['message']}\n";
            }
        } else {
            echo "[⚠] $desc - Invalid JSON response\n";
        }
    }
}

echo "\n=== DIAGNOSTICS COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Open staff/staff_service_report.php in browser\n";
echo "2. Press F12 to open Developer Console\n";
echo "3. Look for [DEBUG] messages to track data loading\n";
echo "4. Check Network tab to see API responses\n";
echo "5. Refer to TROUBLESHOOT_CUSTOMER_LOADING.md for more help\n\n";
?>
