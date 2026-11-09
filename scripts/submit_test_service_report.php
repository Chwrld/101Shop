<?php
// Test script: create a service report via API and verify DB record

$apiBase = 'http://localhost/RepairSystem-main/backend/api/';

function httpGet($url) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nX-Requested-With: XMLHttpRequest\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents($url, false, $context);
    if ($res === false) {
        throw new Exception("GET request failed: $url");
    }
    $json = json_decode($res, true);
    if ($json === null) throw new Exception("Invalid JSON from $url: $res");
    return $json;
}

function httpPostJson($url, $data) {
    $payload = json_encode($data);
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\nX-Requested-With: XMLHttpRequest\r\n",
            'content' => $payload,
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents($url, false, $context);
    if ($res === false) {
        throw new Exception("POST request failed: $url\nPayload: $payload");
    }
    $json = json_decode($res, true);
    if ($json === null) throw new Exception("Invalid JSON response: $res");
    return $json;
}

try {
    echo "Fetching customers...\n";
    $custResp = httpGet($apiBase . 'customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=10');
    if (empty($custResp['success']) || empty($custResp['data']['customers'])) {
        throw new Exception('No customers returned from API');
    }
    $customer = $custResp['data']['customers'][0];
    $customerId = $customer['customer_id'];
    $customerName = $customer['FullName'] ?? ($customer['first_name'] . ' ' . $customer['last_name']);
    echo "Using customer: {$customerId} - {$customerName}\n";

    echo "Fetching appliances for customer $customerId...\n";
    $appResp = httpGet($apiBase . "customer_appliance_api.php?action=getAppliancesByCustomerId&customerId={$customerId}");
    $appliances = [];
    if (!empty($appResp['success']) && !empty($appResp['data'])) {
        // Some endpoints return data array directly
        if (!empty($appResp['data']['appliances'])) $appliances = $appResp['data']['appliances'];
        elseif (is_array($appResp['data'])) $appliances = $appResp['data'];
    }
    if (empty($appliances)) {
        echo "No appliances for this customer, fetching any appliance...\n";
        $allApp = httpGet($apiBase . 'customer_appliance_api.php?action=getAllAppliances&page=1&itemsPerPage=5');
        if (!empty($allApp['data'])) {
            if (!empty($allApp['data']['appliances'])) $appliances = $allApp['data']['appliances'];
            elseif (is_array($allApp['data'])) $appliances = $allApp['data'];
        }
    }
    if (empty($appliances)) throw new Exception('No appliances available to attach to report');
    $appliance = $appliances[0];
    $applianceId = $appliance['appliance_id'];
    $applianceName = ($appliance['brand'] ?? '') . ' ' . ($appliance['model_no'] ?? $appliance['serial_no'] ?? '');
    echo "Using appliance: {$applianceId} - {$applianceName}\n";

    // Build payload
    $today = date('Y-m-d');
    $payload = [
        'customer_id' => (int)$customerId,
        'customer_name' => $customerName,
        'appliance_id' => (int)$applianceId,
        'appliance_name' => $applianceName,
        'date_in' => $today,
        'status' => 'Pending',
        'dealer' => 'TestDealer',
        'findings' => 'Test findings',
        'remarks' => 'Test remarks',
        'location' => ['shop'],
        'service_types' => ['repair'],
        'date_repaired' => null,
        'date_delivered' => null,
        'complaint' => 'Test complaint',
        'labor' => 0,
        'pullout_delivery' => 0,
        'parts' => [],
        'parts_total_charge' => 0,
        'service_charge' => 0,
        'total_amount' => 0,
        'receptionist' => '',
        'manager' => '',
        'technician' => '',
        'released_by' => ''
    ];

    echo "Posting create request to service API...\n";
    $createResp = httpPostJson($apiBase . 'service_api.php?action=create', $payload);
    echo "API response: " . json_encode($createResp) . "\n";

    if (empty($createResp['success'])) {
        throw new Exception('Service API returned failure: ' . ($createResp['message'] ?? 'no message'));
    }

    $reportId = $createResp['data']['report_id'] ?? ($createResp['data'] ?? null);
    echo "Created report id: {$reportId}\n";

    // Verify DB
    echo "Verifying database record...\n";
    $mysqli = new mysqli('localhost', 'root', '', 'repairsystem');
    if ($mysqli->connect_error) throw new Exception('DB connect failed: ' . $mysqli->connect_error);

    if ($reportId) {
        $stmt = $mysqli->prepare('SELECT report_id, customer_id, appliance_id, customer_name, appliance_name, date_in, status FROM service_reports WHERE report_id = ? LIMIT 1');
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row) {
            echo "DB record found: " . json_encode($row) . "\n";
        } else {
            echo "No DB record found for report_id {$reportId}.\n";
            // fallback: find by customer_id and date_in
            $rs = $mysqli->query("SELECT report_id, customer_id, appliance_id, date_in, status FROM service_reports WHERE customer_id = {$customerId} AND date_in = '{$today}' ORDER BY report_id DESC LIMIT 1");
            if ($rs && $rs->num_rows) {
                echo "Found recent record by customer+date: " . json_encode($rs->fetch_assoc()) . "\n";
            } else {
                echo "No recent record found either.\n";
            }
        }
    } else {
        echo "No report id returned; attempting to find a recent record by customer+date...\n";
        $rs = $mysqli->query("SELECT report_id, customer_id, appliance_id, date_in, status FROM service_reports WHERE customer_id = {$customerId} AND date_in = '{$today}' ORDER BY report_id DESC LIMIT 1");
        if ($rs && $rs->num_rows) {
            echo "Found recent record by customer+date: " . json_encode($rs->fetch_assoc()) . "\n";
        } else {
            echo "No recent record found.\n";
        }
    }

    $mysqli->close();
    echo "Done.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

?>