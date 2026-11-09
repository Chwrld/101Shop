#!/usr/bin/env php
<?php
/**
 * DEEP DIAGNOSTIC: Staff Service Report Data Loading Issue
 * Checks: Database, API, File, Form HTML, JavaScript
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║     STAFF SERVICE REPORT - COMPREHENSIVE DIAGNOSTIC            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// ===== TEST 1: DATABASE =====
echo "[TEST 1] DATABASE EXAMINATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $db = new mysqli('localhost', 'root', '', 'repairsystem');
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    echo "[✓] Database connected\n";

    // Check tables
    $tables_to_check = [
        'customers' => 'SELECT COUNT(*) as cnt FROM customers',
        'appliances' => 'SELECT COUNT(*) as cnt FROM appliances',
        'parts' => 'SELECT COUNT(*) as cnt FROM parts',
        'staffs' => 'SELECT COUNT(*) as cnt FROM staffs',
        'service_reports' => 'SELECT COUNT(*) as cnt FROM service_reports'
    ];

    foreach ($tables_to_check as $table => $query) {
        $result = $db->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            echo "[✓] $table: {$row['cnt']} records\n";
        } else {
            echo "[✗] $table: ERROR - " . $db->error . "\n";
        }
    }

    // Check service_reports schema
    echo "\n[INFO] service_reports table structure:\n";
    $schema = $db->query("DESCRIBE service_reports");
    $columns = [];
    while ($col = $schema->fetch_assoc()) {
        $columns[] = $col['Field'];
        echo "      - {$col['Field']} ({$col['Type']})\n";
    }

    if (in_array('customer_id', $columns) && in_array('appliance_id', $columns)) {
        echo "  [✓] customer_id and appliance_id columns exist\n";
    } else {
        echo "  [⚠] customer_id or appliance_id missing!\n";
    }

    // Sample data
    echo "\n[INFO] Sample customer records:\n";
    $customers = $db->query("SELECT customer_id, first_name, last_name FROM customers LIMIT 3");
    while ($cust = $customers->fetch_assoc()) {
        echo "      - ID:{$cust['customer_id']} - {$cust['first_name']} {$cust['last_name']}\n";
    }

    echo "\n[INFO] Sample appliance records:\n";
    $appliances = $db->query("SELECT appliance_id, customer_id, brand FROM appliances LIMIT 3");
    while ($app = $appliances->fetch_assoc()) {
        echo "      - ID:{$app['appliance_id']} - {$app['brand']} (Customer:{$app['customer_id']})\n";
    }

} catch (Exception $e) {
    echo "[✗] Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// ===== TEST 2: API RESPONSES =====
echo "\n[TEST 2] API RESPONSE EXAMINATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$base_url = 'http://localhost/RepairSystem-main/backend/api/';
$api_tests = [
    'getAllCustomers' => 'customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=5',
    'getAllAppliances' => 'customer_appliance_api.php?action=getAllAppliances&page=1&itemsPerPage=5',
    'getAllParts' => 'parts_api.php?action=getAllParts&page=1&itemsPerPage=5'
];

foreach ($api_tests as $name => $endpoint) {
    $url = $base_url . $endpoint;
    echo "\n[$name]\n";
    echo "URL: $url\n";
    
    $response = @file_get_contents($url);
    if ($response === false) {
        echo "[✗] Failed to reach API\n";
        continue;
    }
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        echo "[✗] Invalid JSON response\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
        continue;
    }
    
    if (!isset($data['success'])) {
        echo "[⚠] No 'success' field in response\n";
        continue;
    }
    
    if (!$data['success']) {
        echo "[⚠] API returned success=false\n";
        echo "Message: " . ($data['message'] ?? 'No message') . "\n";
        continue;
    }
    
    // Check data structure
    $count = 0;
    if (isset($data['data']['customers'])) {
        $count = count($data['data']['customers']);
        echo "[✓] Response has 'data.customers' with $count items\n";
        echo "  Sample: " . json_encode($data['data']['customers'][0] ?? []) . "\n";
    } elseif (isset($data['data']['appliances'])) {
        $count = count($data['data']['appliances']);
        echo "[✓] Response has 'data.appliances' with $count items\n";
        echo "  Sample: " . json_encode($data['data']['appliances'][0] ?? []) . "\n";
    } elseif (isset($data['data']['parts'])) {
        $count = count($data['data']['parts']);
        echo "[✓] Response has 'data.parts' with $count items\n";
        echo "  Sample: " . json_encode($data['data']['parts'][0] ?? []) . "\n";
    } elseif (is_array($data['data'])) {
        $count = count($data['data']);
        echo "[✓] Response has 'data' array with $count items\n";
        echo "  Sample: " . json_encode($data['data'][0] ?? []) . "\n";
    } else {
        echo "[⚠] Unexpected data structure: " . json_encode($data['data']) . "\n";
    }
}

// ===== TEST 3: FILE STRUCTURE =====
echo "\n\n[TEST 3] FILE STRUCTURE EXAMINATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$form_file = __DIR__ . '/../staff/staff_service_report.php';
if (!file_exists($form_file)) {
    echo "[✗] Form file not found: $form_file\n";
    exit(1);
}

echo "[✓] Form file exists\n";

$content = file_get_contents($form_file);
$size = strlen($content);
echo "[✓] File size: " . number_format($size) . " bytes\n";

// Check for key elements
$checks = [
    'id="customer-select"' => 'Customer select element',
    'id="appliance-select"' => 'Appliance select element',
    'id="customer-search"' => 'Customer search input',
    'API_BASE_URL' => 'API URL constant',
    'loadInitialData' => 'loadInitialData function',
    'loadDropdown' => 'loadDropdown function',
    '$(document).ready' => 'jQuery ready handler',
    'initializeServiceReport' => 'Initialization function'
];

echo "\n[INFO] Required elements in form:\n";
foreach ($checks as $pattern => $description) {
    $found = strpos($content, $pattern) !== false;
    echo ($found ? "[✓]" : "[✗]") . " $description\n";
}

// ===== TEST 4: JAVASCRIPT CONFIGURATION =====
echo "\n\n[TEST 4] JAVASCRIPT CONFIGURATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Extract API URLs from file
if (preg_match('/const API_BASE_URL = [\'"]([^\'"]+)[\'"];/', $content, $matches)) {
    echo "[✓] API_BASE_URL found: " . $matches[1] . "\n";
} else {
    echo "[✗] API_BASE_URL not found\n";
}

if (preg_match('/const CUSTOMER_APPLIANCE_API_URL = /', $content)) {
    echo "[✓] CUSTOMER_APPLIANCE_API_URL configured\n";
} else {
    echo "[✗] CUSTOMER_APPLIANCE_API_URL not configured\n";
}

// ===== TEST 5: FORM HTML ANALYSIS =====
echo "\n\n[TEST 5] FORM HTML ANALYSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Check if customer select is visible or hidden
if (strpos($content, 'id="customer-select"') !== false) {
    if (strpos($content, 'id="customer-select"" aria-hidden="true"') !== false || 
        strpos($content, 'd-none') !== false) {
        echo "[⚠] WARNING: customer-select appears to be HIDDEN (d-none or aria-hidden)\n";
        echo "    This might prevent dropdown from showing!\n";
    } else {
        echo "[✓] customer-select appears to be VISIBLE\n";
    }
}

// ===== TEST 6: RECOMMENDATIONS =====
echo "\n\n[RECOMMENDATIONS]\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

echo "1. If customer-select is hidden (d-none), customers won't show\n";
echo "   → Solution: Make the select visible (remove d-none or aria-hidden)\n\n";

echo "2. Check browser console when form loads:\n";
echo "   → Open staff_service_report.php\n";
echo "   → Press F12 for Developer Tools\n";
echo "   → Look for [INIT] and [DEBUG] messages\n";
echo "   → Check Network tab for API calls\n\n";

echo "3. Verify API is being called:\n";
echo "   → Network tab should show customer_appliance_api.php request\n";
echo "   → Response should have status 200\n";
echo "   → Response should have customers array\n\n";

echo "4. Common issues:\n";
echo "   → API path incorrect → Fixed: using absolute path /RepairSystem-main/backend/api/\n";
echo "   → Select element hidden → Check HTML classes\n";
echo "   → JavaScript errors → Check console for red errors\n";
echo "   → Auth redirect → Make sure logged in as staff\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "Diagnostic complete. Review the output above for issues.\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$db->close();
?>
