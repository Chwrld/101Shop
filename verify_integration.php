#!/usr/bin/env php
<?php
/**
 * Command-line verification script for Staff Service Report Integration
 * Run: php verify_integration.php
 */

echo "\n========================================\n";
echo "Staff Service Report Integration Check\n";
echo "========================================\n\n";

$checks = [];

// 1. Check file existence
echo "[1/6] Checking file existence...\n";
$files = [
    'staff/staff_service_report.php' => 'New staff service report page',
    'staff/staff_sidebar.php' => 'Staff sidebar (navigation)',
    'staff/staffnavbar.php' => 'Staff navbar (header)',
    'backend/handlers/Database.php' => 'Database handler',
    'backend/handlers/authHandler.php' => 'Auth handler',
    'backend/handlers/serviceHandler.php' => 'Service handler',
    'database/database.php' => 'Database config',
];

$baseDir = __DIR__;
foreach ($files as $file => $description) {
    $path = "$baseDir/$file";
    if (file_exists($path)) {
        echo "  ✓ $file\n";
        $checks['files'][$file] = 'PASS';
    } else {
        echo "  ✗ $file (MISSING)\n";
        $checks['files'][$file] = 'FAIL';
    }
}

// 2. Check PHP syntax
echo "\n[2/6] Checking PHP syntax...\n";
$phpFiles = [
    'staff/staff_service_report.php',
    'staff/staff_sidebar.php',
    'staff/staffnavbar.php',
];

foreach ($phpFiles as $file) {
    $path = "$baseDir/$file";
    $output = shell_exec("php -l \"$path\" 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "  ✓ $file\n";
        $checks['syntax'][$file] = 'PASS';
    } else {
        echo "  ✗ $file (SYNTAX ERROR)\n";
        $checks['syntax'][$file] = 'FAIL';
    }
}

// 3. Check database configuration
echo "\n[3/6] Checking database configuration...\n";
$dbConfig = include "$baseDir/database/database.php";
if ($dbConfig && isset($dbConfig['host'], $dbConfig['dbname'], $dbConfig['username'])) {
    echo "  ✓ Database config found\n";
    echo "    - Host: {$dbConfig['host']}\n";
    echo "    - Database: {$dbConfig['dbname']}\n";
    echo "    - User: {$dbConfig['username']}\n";
    $checks['database'] = 'PASS';
} else {
    echo "  ✗ Database config incomplete\n";
    $checks['database'] = 'FAIL';
}

// 4. Check API endpoints
echo "\n[4/6] Checking API endpoints...\n";
$apis = [
    'backend/api/service_api.php',
    'backend/api/parts_api.php',
    'backend/api/customer_appliance_api.php',
    'backend/api/staff_api.php',
    'backend/api/service_price_api.php',
    'backend/api/transaction_api.php',
];

foreach ($apis as $api) {
    $path = "$baseDir/$api";
    if (file_exists($path)) {
        echo "  ✓ $api\n";
        $checks['apis'][$api] = 'PASS';
    } else {
        echo "  ✗ $api (MISSING)\n";
        $checks['apis'][$api] = 'FAIL';
    }
}

// 5. Check sidebar contains new page link
echo "\n[5/6] Checking sidebar integration...\n";
$sidebarContent = file_get_contents("$baseDir/staff/staff_sidebar.php");
if (strpos($sidebarContent, 'staff_service_report.php') !== false) {
    echo "  ✓ Sidebar links to staff_service_report.php\n";
    $checks['sidebar'] = 'PASS';
} else {
    echo "  ✗ Sidebar does not link to staff_service_report.php\n";
    $checks['sidebar'] = 'FAIL';
}

// 6. Check auth handler has staff role validation
echo "\n[6/6] Checking auth role validation...\n";
$authContent = file_get_contents("$baseDir/backend/handlers/authHandler.php");
if (strpos($authContent, "case 'staff':") !== false && strpos($authContent, "'staff'") !== false) {
    echo "  ✓ Auth handler includes staff role validation\n";
    $checks['auth'] = 'PASS';
} else {
    echo "  ✗ Auth handler missing staff role validation\n";
    $checks['auth'] = 'FAIL';
}

// Summary
echo "\n========================================\n";
echo "Summary\n";
echo "========================================\n";

$totalChecks = 0;
$passedChecks = 0;

foreach ($checks as $category => $items) {
    if (is_array($items)) {
        foreach ($items as $item => $status) {
            $totalChecks++;
            if ($status === 'PASS') $passedChecks++;
        }
    } else {
        $totalChecks++;
        if ($items === 'PASS') $passedChecks++;
    }
}

echo "Passed: $passedChecks / $totalChecks\n";

if ($passedChecks === $totalChecks) {
    echo "\n✓ All checks passed! Integration is complete.\n";
    echo "\nNext steps:\n";
    echo "1. Start XAMPP/Apache\n";
    echo "2. Log in as a staff user\n";
    echo "3. Navigate to Service Report from the sidebar\n";
    echo "4. Test creating a service report\n";
    echo "5. Verify data in the database\n";
    exit(0);
} else {
    echo "\n✗ Some checks failed. Please review the errors above.\n";
    exit(1);
}
?>
