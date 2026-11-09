<?php
require_once __DIR__ . '/bootstrap.php';
require __DIR__ . '/backend/handlers/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "✓ Database connection: OK\n";
    
    // Test a simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM staffs");
    $row = $result->fetch_assoc();
    echo "✓ Database query: OK (Found {$row['count']} staff members)\n";
    
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}
?>
