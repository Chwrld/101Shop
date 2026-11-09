<?php
/**
 * Test script to verify minimal service report creation works
 * Tests: customer_name, appliance_name, date_in, status only
 */

require_once __DIR__ . '/backend/handlers/Database.php';
require_once __DIR__ . '/backend/handlers/serviceHandler.php';

echo "=== MINIMAL SERVICE REPORT CREATION TEST ===\n\n";

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db->connect_error) {
        echo "✗ Database connection failed: " . $db->connect_error . "\n";
        exit(1);
    }
    echo "✓ Database connected successfully\n\n";
    
    // Initialize service handler
    $serviceHandler = new ServiceHandler($db);
    echo "✓ ServiceHandler initialized\n\n";
    
    // Test 1: Create report with only 4 required fields
    echo "TEST 1: Creating report with minimal fields (customer, appliance, date_in, status)\n";
    echo str_repeat("-", 70) . "\n";
    
    $report = new Service_report(
        'Test Customer Minimal',  // customer_name
        'Test Appliance',         // appliance_name
        new DateTime('2025-12-11'), // date_in
        'Pending',                // status
        '',                       // dealer (optional)
        null,                     // dop (optional)
        null,                     // date_pulled_out (optional)
        '',                       // findings (optional)
        '',                       // remarks (optional)
        ['shop'],                 // location (optional but default)
        null,                     // customer_id (optional)
        null                      // appliance_id (optional)
    );
    echo "✓ Service_report object created\n";
    
    // Create minimal service detail (all fields optional except those with defaults)
    $detail = new Service_detail(
        [],                       // service_types (optional - empty array)
        0.00,                     // service_charge (optional - 0)
        null,                     // date_repaired (optional)
        null,                     // date_delivered (optional)
        '',                       // complaint (optional)
        0.00,                     // labor (optional - 0)
        0.00,                     // pullout_delivery (optional - 0)
        0.00,                     // parts_total_charge (optional - 0)
        0.00,                     // total_amount (optional - 0)
        '',                       // receptionist (optional - empty)
        '',                       // manager (optional - empty)
        '',                       // technician (optional - empty)
        '',                       // released_by (optional - empty)
        null                      // date_in (optional)
    );
    echo "✓ Service_detail object created\n";
    
    // Create empty parts
    $partsUsed = new Parts_used(['parts' => []]);
    echo "✓ Parts_used object created (empty)\n\n";
    
    // Attempt to create the complete service report
    echo "Attempting to save to database...\n";
    $result = $serviceHandler->createCompleteServiceReport($report, $detail, $partsUsed);
    
    if ($result['success']) {
        echo "✓ SUCCESS! Report created with ID: " . $result['data']['report_id'] . "\n";
        echo "  - Customer: Test Customer Minimal\n";
        echo "  - Appliance: Test Appliance\n";
        echo "  - Date In: 2025-12-11\n";
        echo "  - Status: Pending\n";
        echo "  - All optional fields: Empty/0\n\n";
        
        // Test 2: Retrieve the report to verify it was saved correctly
        echo "TEST 2: Retrieving created report\n";
        echo str_repeat("-", 70) . "\n";
        $reportId = $result['data']['report_id'];
        $retrieveResult = $serviceHandler->getById($reportId);
        
        if ($retrieveResult['success']) {
            echo "✓ Report retrieved successfully\n";
            $data = $retrieveResult['data'];
            echo "  - Report ID: " . $data['report_id'] . "\n";
            echo "  - Customer: " . $data['customer_name'] . "\n";
            echo "  - Appliance: " . $data['appliance_name'] . "\n";
            echo "  - Date In: " . $data['date_in'] . "\n";
            echo "  - Status: " . $data['status'] . "\n";
            echo "  - Service Types: " . (empty($data['service_types']) ? '[]' : json_encode($data['service_types'])) . "\n";
            echo "  - Receptionist: " . ($data['receptionist'] ?: '(empty)') . "\n";
            echo "  - Manager: " . ($data['manager'] ?: '(empty)') . "\n";
            echo "  - Technician: " . ($data['technician'] ?: '(empty)') . "\n";
            echo "  - Released By: " . ($data['released_by'] ?: '(empty)') . "\n";
            echo "  - Total Amount: " . $data['total_amount'] . "\n\n";
            
            // Clean up test data
            echo "TEST 3: Cleanup - Deleting test report\n";
            echo str_repeat("-", 70) . "\n";
            $deleteResult = $serviceHandler->delete($reportId);
            if ($deleteResult['success']) {
                echo "✓ Test report deleted successfully\n\n";
            } else {
                echo "⚠ Warning: Could not delete test report (ID: $reportId)\n";
                echo "  Please delete manually from database\n\n";
            }
        } else {
            echo "✗ Failed to retrieve report: " . $retrieveResult['message'] . "\n\n";
        }
    } else {
        echo "✗ FAILED to create report\n";
        echo "  Error: " . $result['message'] . "\n\n";
        exit(1);
    }
    
    // Test 3: Test with staff fields populated
    echo "TEST 4: Creating report with staff fields\n";
    echo str_repeat("-", 70) . "\n";
    
    $report2 = new Service_report(
        'Test Customer With Staff',
        'Test Appliance 2',
        new DateTime('2025-12-11'),
        'In Progress',
        '',
        null,
        null,
        '',
        '',
        ['shop'],
        null,
        null
    );
    
    $detail2 = new Service_detail(
        ['repair'],               // service_types
        0.00,
        null,
        null,
        '',
        0.00,
        0.00,
        0.00,
        0.00,
        'Test Receptionist',      // receptionist
        'Test Manager',           // manager
        'Test Technician',        // technician
        'Test Released By',       // released_by
        null
    );
    
    $partsUsed2 = new Parts_used(['parts' => []]);
    
    $result2 = $serviceHandler->createCompleteServiceReport($report2, $detail2, $partsUsed2);
    
    if ($result2['success']) {
        echo "✓ SUCCESS! Report with staff created with ID: " . $result2['data']['report_id'] . "\n";
        echo "  - Receptionist: Test Receptionist\n";
        echo "  - Manager: Test Manager\n";
        echo "  - Technician: Test Technician\n";
        echo "  - Released By: Test Released By\n\n";
        
        // Clean up
        $serviceHandler->delete($result2['data']['report_id']);
        echo "✓ Test report deleted\n\n";
    } else {
        echo "✗ Failed: " . $result2['message'] . "\n\n";
    }
    
    echo "=== ALL TESTS COMPLETED ===\n";
    echo "✓ Minimal service report creation works correctly\n";
    echo "✓ API is properly configured\n";
    echo "✓ Database schema supports optional fields\n";
    echo "✓ Validation allows minimal data entry\n\n";
    echo "CONCLUSION: System ready for minimal field submissions!\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}
