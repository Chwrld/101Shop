<?php
/**
 * Test script to update a service report status to Completed with missing optional fields
 * Run this from the server (e.g., `php scripts/test_update_service_report_completed.php <report_id>`)
 */

if ($argc < 2) {
    echo "Usage: php scripts/test_update_service_report_completed.php <report_id>\n";
    exit(1);
}

$reportId = intval($argv[1]);
$apiBase = 'http://localhost/RepairSystem-main/backend/api/service_api.php';
$url = $apiBase . '?action=update&id=' . $reportId;

function callApi($url, $method = 'PUT', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $headers = [
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest'
    ];
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch) . PHP_EOL;
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($result, true);
}

// Minimal update payload that intentionally omits optional date fields and service_types
$payload = [
    'customer_name' => 'Test Customer',
    'appliance_name' => 'Test Appliance',
    'date_in' => date('Y-m-d'),
    'status' => 'Completed',
    'dealer' => '',
    'findings' => 'Completed via test',
    'remarks' => 'Completed remark',
    'location' => ['shop'],
    // intentionally missing 'dop' and 'date_pulled_out' and 'service_types' to test defaults
    'labor' => 0,
    'pullout_delivery' => 0,
    'parts_total_charge' => 0,
    'total_amount' => 0
];

echo "PUT $url\nPayload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
$response = callApi($url, 'PUT', $payload);

var_dump($response);

?>
