<?php
/**
 * Test Script: Verify Staff Service Report Database Synchronization
 * Tests: Customer ID, Appliance ID, and Form Data Sync
 */

require_once __DIR__ . '/../backend/handlers/serviceHandler.php';

echo "=== Staff Service Report Database Sync Test ===\n\n";

try {
    // Initialize database
    $db = new mysqli('localhost', 'root', '', 'repairsystem');
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    echo "[✓] Database connected\n";

    // 1. Verify database schema
    echo "\n[TEST 1] Verify database schema\n";
    $result = $db->query("SHOW COLUMNS FROM service_reports LIKE 'customer_id'");
    if ($result && $result->num_rows > 0) {
        echo "[✓] customer_id column exists\n";
    } else {
        throw new Exception("customer_id column NOT found");
    }

    $result = $db->query("SHOW COLUMNS FROM service_reports LIKE 'appliance_id'");
    if ($result && $result->num_rows > 0) {
        echo "[✓] appliance_id column exists\n";
    } else {
        throw new Exception("appliance_id column NOT found");
    }

    // 2. Get sample customer and appliance
    echo "\n[TEST 2] Retrieve sample customer and appliance\n";
    
    $customerResult = $db->query("SELECT customer_id, CONCAT(first_name, ' ', last_name) as name FROM customers LIMIT 1");
    if (!$customerResult || $customerResult->num_rows === 0) {
        throw new Exception("No customers found in database");
    }
    $customer = $customerResult->fetch_assoc();
    $customerId = $customer['customer_id'];
    $customerName = $customer['name'];
    echo "[✓] Sample customer: ID=$customerId, Name=$customerName\n";

    $applianceResult = $db->query("SELECT appliance_id, brand FROM appliances WHERE customer_id = $customerId LIMIT 1");
    if (!$applianceResult || $applianceResult->num_rows === 0) {
        // Try any appliance
        $applianceResult = $db->query("SELECT appliance_id, brand FROM appliances LIMIT 1");
        if (!$applianceResult || $applianceResult->num_rows === 0) {
            throw new Exception("No appliances found in database");
        }
    }
    $appliance = $applianceResult->fetch_assoc();
    $applianceId = $appliance['appliance_id'];
    $applianceName = $appliance['brand'];
    echo "[✓] Sample appliance: ID=$applianceId, Name=$applianceName\n";

    // 3. Test Service_report class with IDs
    echo "\n[TEST 3] Test Service_report class with customer_id and appliance_id\n";
    
    $dateIn = new DateTime(date('Y-m-d'));
    $report = new Service_report(
        $customerName,
        $applianceName,
        $dateIn,
        'Pending',
        'Test Dealer',
        null,
        null,
        'Test findings',
        'Test remarks',
        ['shop'],
        $customerId,
        $applianceId
    );
    
    echo "[✓] Service_report object created with:\n";
    echo "    - customer_name: {$report->customer_name}\n";
    echo "    - customer_id: {$report->customer_id}\n";
    echo "    - appliance_name: {$report->appliance_name}\n";
    echo "    - appliance_id: {$report->appliance_id}\n";
    echo "    - date_in: " . $report->date_in->format('Y-m-d') . "\n";
    echo "    - status: {$report->status}\n";

    // 4. Verify form fields exist in staff page
    echo "\n[TEST 4] Verify hidden ID fields in staff service report form\n";
    
    $staffPageContent = file_get_contents(__DIR__ . '/../staff/staff_service_report.php');
    
    if (strpos($staffPageContent, 'id="customer-id"') !== false) {
        echo "[✓] Hidden customer-id field found in form\n";
    } else {
        throw new Exception("Hidden customer-id field NOT found in form");
    }

    if (strpos($staffPageContent, 'id="appliance-id"') !== false) {
        echo "[✓] Hidden appliance-id field found in form\n";
    } else {
        throw new Exception("Hidden appliance-id field NOT found in form");
    }

    // 5. Verify JavaScript functions updated
    echo "\n[TEST 5] Verify JavaScript data collection functions\n";
    
    if (strpos($staffPageContent, "customer_id: $('#customer-id').val()") !== false) {
        echo "[✓] gatherFormData includes customer_id\n";
    } else {
        throw new Exception("gatherFormData does NOT include customer_id");
    }

    if (strpos($staffPageContent, "appliance_id: $('#appliance-id').val()") !== false) {
        echo "[✓] gatherFormData includes appliance_id\n";
    } else {
        throw new Exception("gatherFormData does NOT include appliance_id");
    }

    if (strpos($staffPageContent, "\$('#customer-id').val(id)") !== false) {
        echo "[✓] setCustomerFromSuggestion populates customer-id field\n";
    } else {
        throw new Exception("setCustomerFromSuggestion does NOT populate customer-id");
    }

    if (strpos($staffPageContent, "\$('#appliance-id').val(applianceId)") !== false) {
        echo "[✓] Appliance change handler populates appliance-id field\n";
    } else {
        throw new Exception("Appliance selection does NOT populate appliance-id");
    }

    // 6. Verify API can receive IDs
    echo "\n[TEST 6] Verify backend API configuration\n";
    
    $apiContent = file_get_contents(__DIR__ . '/../backend/api/service_api.php');
    if (strpos($apiContent, "intval(\$input['customer_id'])") !== false) {
        echo "[✓] API accepts customer_id parameter\n";
    } else {
        throw new Exception("API does NOT accept customer_id");
    }

    if (strpos($apiContent, "intval(\$input['appliance_id'])") !== false) {
        echo "[✓] API accepts appliance_id parameter\n";
    } else {
        throw new Exception("API does NOT accept appliance_id");
    }

    echo "\n=== All Tests PASSED ===\n";
    echo "\nSummary:\n";
    echo "✓ Database schema has customer_id and appliance_id columns\n";
    echo "✓ Service_report class accepts and stores customer_id and appliance_id\n";
    echo "✓ Staff form has hidden fields to capture IDs\n";
    echo "✓ JavaScript functions populate ID fields on selection\n";
    echo "✓ gatherFormData includes IDs in form submission\n";
    echo "✓ Backend API accepts and processes IDs\n";
    echo "\nNext Steps:\n";
    echo "1. Open staff/staff_service_report.php in browser\n";
    echo "2. Select a customer (customer_id should be captured)\n";
    echo "3. Select an appliance (appliance_id should be captured)\n";
    echo "4. Submit the form\n";
    echo "5. Verify in admin panel that the staff report is visible with correct IDs\n";

} catch (Exception $e) {
    echo "[✗] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

$db->close();
?>
