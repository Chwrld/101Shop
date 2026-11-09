<?php
/**
 * API Fix Test - Simulates the exact request from service_report_admin_v2.php
 * Tests against the actual Render deployment
 */

// The exact data structure from your console log
$testData = [
    'customer_name' => 'Bobong Marco',
    'customer_id' => 22,
    'appliance_name' => 'Samsung - No Serial (Oven)',
    'appliance_id' => 22,
    'date_in' => '2025-12-11',
    'status' => 'Pending',
    'dealer' => '',
    'findings' => '',
    'remarks' => '',
    'location' => ['shop'],
    'service_types' => ['repair'],
    'complaint' => '',
    'labor' => 0,
    'pullout_delivery' => 0,
    'parts_total_charge' => 0,
    'service_charge' => 0,
    'total_amount' => 0,
    'receptionist' => '',
    'manager' => '',
    'technician' => '',
    'released_by' => '',
    'parts' => []
];

echo "=== Testing Service Report API Fix ===\n";
echo "Testing against: https://repairservice.onrender.com\n\n";

// Test the actual Render API endpoint
$apiUrl = 'https://repairservice.onrender.com/backend/api/service_api.php?action=create';

echo "Sending POST request to: $apiUrl\n";
echo "Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute request
echo "Executing API call...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\n=== Response ===\n";
echo "HTTP Status Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

echo "Response Body:\n";
echo $response . "\n\n";

// Parse response
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Invalid JSON response\n";
    exit(1);
}

echo "=== Test Results ===\n";

if ($httpCode === 201 || $httpCode === 200) {
    if ($responseData['success'] ?? false) {
        echo "✅ SUCCESS! Service report created without errors\n";
        echo "✅ Report ID: " . ($responseData['data']['report_id'] ?? 'N/A') . "\n";
        echo "✅ Message: " . ($responseData['message'] ?? 'N/A') . "\n";
        echo "\n🎉 The fix works! No 'Incorrect date value' errors!\n";
        
        // Clean up the test record if you want
        $reportId = $responseData['data']['report_id'] ?? null;
        if ($reportId) {
            echo "\nℹ️  Test record created with ID: $reportId\n";
            echo "   You may want to delete this test record from the database.\n";
        }
    } else {
        echo "❌ FAILED: API returned success=false\n";
        echo "   Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else if ($httpCode === 400) {
    echo "❌ FAILED: 400 Bad Request\n";
    echo "   Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    echo "\n   This is the error we're trying to fix!\n";
    if (strpos($responseData['message'] ?? '', 'Incorrect date value') !== false) {
        echo "   ⚠️  The 'Incorrect date value' error still exists\n";
        echo "   ⚠️  Make sure the updated serviceHandler.php is deployed to Render\n";
    }
} else {
    echo "❌ FAILED: Unexpected HTTP status code $httpCode\n";
    echo "   Response: " . ($responseData['message'] ?? 'No message') . "\n";
}

echo "\n=== Local File Check ===\n";
echo "Checking if local serviceHandler.php has the fix...\n";

$filePath = __DIR__ . '/backend/handlers/serviceHandler.php';
$content = file_get_contents($filePath);

$hasNullFix = strpos($content, "? \$detail->date_repaired->format('Y-m-d') : null") !== false;
$noNullIf = strpos($content, "NULLIF(?, '')") === false;

if ($hasNullFix) {
    echo "✅ Local file has the null fix for date_repaired\n";
} else {
    echo "❌ Local file missing the null fix\n";
}

if ($noNullIf) {
    echo "✅ Local file has NULLIF removed (correct)\n";
} else {
    echo "⚠️  Local file still has NULLIF (may need update)\n";
}

echo "\n=== Deployment Check ===\n";
if ($httpCode === 400 && isset($responseData['message']) && strpos($responseData['message'], 'Incorrect date value') !== false) {
    echo "⚠️  WARNING: The error still occurs on Render\n";
    echo "   This means the updated code is not yet deployed to Render.\n";
    echo "\n   Steps to fix:\n";
    echo "   1. Commit the changes: git add backend/handlers/serviceHandler.php\n";
    echo "   2. Commit: git commit -m 'Fix: Empty date values causing API errors'\n";
    echo "   3. Push: git push origin main\n";
    echo "   4. Wait for Render to redeploy (check Render dashboard)\n";
    echo "   5. Run this test again\n";
} else if ($httpCode === 201 || $httpCode === 200) {
    echo "✅ The fix is working on Render!\n";
    echo "   You can now submit service reports without API errors.\n";
}

echo "\n=== Test Complete ===\n";
