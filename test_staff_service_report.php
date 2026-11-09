<?php
/**
 * Test script to verify staff_service_report.php integration with:
 * - Database connection
 * - Auth handler (staff auth)
 * - Sidebar navigation
 * - API endpoints
 * 
 * Run this in browser: http://localhost/RepairSystem-main/test_staff_service_report.php
 */

require_once __DIR__ . '/bootstrap.php';
require __DIR__ . '/backend/handlers/authHandler.php';
require __DIR__ . '/backend/handlers/Database.php';

$results = [];

// Test 1: Database Connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        $results['Database'] = ['status' => 'PASS', 'message' => 'Database connection successful'];
    }
} catch (Exception $e) {
    $results['Database'] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// Test 2: AuthHandler Initialization
try {
    $auth = new AuthHandler();
    $results['AuthHandler'] = ['status' => 'PASS', 'message' => 'AuthHandler initialized successfully'];
} catch (Exception $e) {
    $results['AuthHandler'] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// Test 3: Check if Staff Sidebar exists
$sidebarPath = __DIR__ . '/staff/staff_sidebar.php';
if (file_exists($sidebarPath)) {
    $results['Staff Sidebar'] = ['status' => 'PASS', 'message' => 'staff_sidebar.php found'];
} else {
    $results['Staff Sidebar'] = ['status' => 'FAIL', 'message' => 'staff_sidebar.php not found'];
}

// Test 4: Check if Staff Navbar exists
$navbarPath = __DIR__ . '/staff/staffnavbar.php';
if (file_exists($navbarPath)) {
    $results['Staff Navbar'] = ['status' => 'PASS', 'message' => 'staffnavbar.php found'];
} else {
    $results['Staff Navbar'] = ['status' => 'FAIL', 'message' => 'staffnavbar.php not found'];
}

// Test 5: Check if API endpoints exist
$apiEndpoints = [
    'service_api.php',
    'parts_api.php',
    'staff_api.php',
    'customer_appliance_api.php',
    'service_price_api.php',
    'transaction_api.php'
];

foreach ($apiEndpoints as $endpoint) {
    $path = __DIR__ . "/backend/api/$endpoint";
    if (file_exists($path)) {
        $results["API: $endpoint"] = ['status' => 'PASS', 'message' => "Endpoint found at $path"];
    } else {
        $results["API: $endpoint"] = ['status' => 'FAIL', 'message' => "Endpoint not found at $path"];
    }
}

// Test 6: Check if staff_service_report.php exists and is syntactically valid
$staffReportPath = __DIR__ . '/staff/staff_service_report.php';
if (file_exists($staffReportPath)) {
    $lintOutput = shell_exec("php -l \"$staffReportPath\" 2>&1");
    if (strpos($lintOutput, 'No syntax errors') !== false) {
        $results['Staff Service Report'] = ['status' => 'PASS', 'message' => 'staff_service_report.php syntax is valid'];
    } else {
        $results['Staff Service Report'] = ['status' => 'FAIL', 'message' => 'Syntax error: ' . $lintOutput];
    }
} else {
    $results['Staff Service Report'] = ['status' => 'FAIL', 'message' => 'staff_service_report.php not found'];
}

// Display Results
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Service Report - Integration Test</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #0066e6; }
        .test-row { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
        .test-name { font-weight: 600; color: #333; }
        .pass { color: #28a745; font-weight: 600; }
        .fail { color: #dc3545; font-weight: 600; }
        .message { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Staff Service Report Integration Test</h1>
        
        <?php foreach ($results as $component => $result): ?>
            <div class="test-row">
                <div class="test-name"><?php echo $component; ?></div>
                <div class="<?php echo $result['status'] === 'PASS' ? 'pass' : 'fail'; ?>">
                    <?php echo $result['status']; ?>
                </div>
            </div>
            <div style="padding: 0 15px; margin-bottom: 10px;">
                <div class="message"><?php echo $result['message']; ?></div>
            </div>
        <?php endforeach; ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h5>Next Steps:</h5>
            <ul>
                <li><strong>Test Auth:</strong> Log in as a staff user and navigate to <a href="staff/staff_service_report.php" target="_blank">/staff/staff_service_report.php</a></li>
                <li><strong>Verify Sidebar:</strong> The "Service Report" link should be active when on the staff_service_report.php page</li>
                <li><strong>Test APIs:</strong> Open browser DevTools (F12) → Network tab → submit a test form to verify API calls work</li>
                <li><strong>Check Database:</strong> Verify that submitted service reports appear in the database</li>
            </ul>
        </div>
    </div>
</body>
</html>
