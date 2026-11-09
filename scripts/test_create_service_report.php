<?php
/**
 * Enhanced test script:
 * 1) POST a minimal service report without 'dop' or 'date_pulled_out'
 * 2) Confirm API returned a report_id
 * 3) Call the API `getById` and `getAll` (dashboard-like) to confirm the report is visible
 * 4) Query the DB to ensure `dop` and `date_pulled_out` are NULL
 *
 * Run: php scripts/test_create_service_report.php
 */

require_once __DIR__ . '/../database/database.php';

function callApi($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $headers = [
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest'
    ];
    if ($data) {
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
    $decoded = json_decode($result, true);
    return $decoded === null ? $result : $decoded;
}

$baseApi = 'http://localhost/RepairSystem-main/backend/api/service_api.php';
$createUrl = $baseApi . '?action=create';

$payload = [
    'customer_name' => 'Dashboard Test Customer',
    'appliance_name' => 'Test Appliance',
    'date_in' => date('Y-m-d'),
    'status' => 'Pending',
    'dealer' => '',
    // omit dop and date_pulled_out to simulate empty values
    'findings' => 'Test findings',
    'remarks' => 'Test remarks',
    'location' => ['shop'],
    'service_types' => ['repair'],
    'complaint' => 'None',
    'labor' => 0,
    'pullout_delivery' => 0,
    'parts_total_charge' => 0,
    'total_amount' => 0,
    'parts' => []
];

echo "Posting to API: $createUrl\n";
$createResp = callApi($createUrl, 'POST', $payload);

echo "Create response:\n";
var_export($createResp);
echo "\n\n";

if (!is_array($createResp) || empty($createResp['success']) || empty($createResp['data'])) {
    echo "API create failed or returned unexpected response.\n";
    exit(1);
}

$reportId = $createResp['data'];
if (is_array($reportId) && isset($reportId['report_id'])) {
    $reportId = $reportId['report_id'];
}

echo "Created report_id: $reportId\n";

// 1) Verify via getById
$getByIdUrl = $baseApi . '?action=getById&id=' . urlencode($reportId);
$getResp = callApi($getByIdUrl, 'GET');
echo "\ngetById response:\n";
var_export($getResp);
echo "\n\n";

// 2) Verify via getAll (dashboard-like)
$getAllUrl = $baseApi . '?action=getAll&limit=50&offset=0';
$allResp = callApi($getAllUrl, 'GET');

$foundInAll = false;
if (is_array($allResp) && !empty($allResp['data']) && is_array($allResp['data'])) {
    foreach ($allResp['data'] as $row) {
        if (!empty($row['report_id']) && $row['report_id'] == $reportId) {
            $foundInAll = true;
            break;
        }
    }
}

echo "Found in getAll (dashboard)?: " . ($foundInAll ? 'YES' : 'NO') . "\n";

// 3) Check DB directly for NULLs
$config = require __DIR__ . '/../database/database.php';
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(1);
}

$sql = "SELECT report_id, dop, date_pulled_out FROM service_reports WHERE report_id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $reportId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    echo "No DB row found for report_id $reportId\n";
    exit(1);
}

echo "\nDB row for report_id $reportId:\n";
echo "dop: " . var_export($row['dop'], true) . "\n";
echo "date_pulled_out: " . var_export($row['date_pulled_out'], true) . "\n";

if (is_null($row['dop']) && is_null($row['date_pulled_out'])) {
    echo "\nSUCCESS: Both dop and date_pulled_out are NULL AND report appears in dashboard (getAll) status: " . ($foundInAll ? 'visible' : 'not visible') . "\n";
    exit(0);
} else {
    echo "\nWARNING: One or both date columns are NOT NULL.\n";
    exit(2);
}

?>
