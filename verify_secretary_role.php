<?php
// This script verifies that staff members with Secretary role are in the database

require __DIR__.'/backend/handlers/Database.php';
require __DIR__.'/backend/handlers/staffsHandler.php';

$database = new Database();
$db = $database->getConnection();

if($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$staffHandler = new staffsHandler($db);

// Check for Secretary role
echo "=== Checking for Secretary role ===\n";
$result = $staffHandler->getStaffsbyRole('Secretary');
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Check for Cashier role (old)
echo "=== Checking for Cashier role (should be empty) ===\n";
$result2 = $staffHandler->getStaffsbyRole('Cashier');
echo json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";

// Show all active staff
echo "=== All Active Staff ===\n";
$result3 = $staffHandler->getStaffsbyRole(null);
echo json_encode($result3, JSON_PRETTY_PRINT) . "\n";
?>
