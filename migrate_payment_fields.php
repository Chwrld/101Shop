<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/backend/handlers/Database.php';

$db = new Database();
$conn = $db->getConnection();

echo "Checking and adding missing columns to transactions table...\n\n";

// Check if payment_method column exists
$checkPaymentMethod = $conn->query("SHOW COLUMNS FROM transactions LIKE 'payment_method'");
if ($checkPaymentMethod->num_rows == 0) {
    echo "Adding payment_method column...\n";
    $sql1 = "ALTER TABLE transactions ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER payment_date";
    if ($conn->query($sql1)) {
        echo "✓ payment_method column added successfully\n";
    } else {
        echo "✗ Error adding payment_method column: " . $conn->error . "\n";
    }
} else {
    echo "✓ payment_method column already exists\n";
}

// Check if reference_number column exists
$checkReferenceNumber = $conn->query("SHOW COLUMNS FROM transactions LIKE 'reference_number'");
if ($checkReferenceNumber->num_rows == 0) {
    echo "Adding reference_number column...\n";
    $sql2 = "ALTER TABLE transactions ADD COLUMN reference_number VARCHAR(255) DEFAULT NULL AFTER payment_method";
    if ($conn->query($sql2)) {
        echo "✓ reference_number column added successfully\n";
    } else {
        echo "✗ Error adding reference_number column: " . $conn->error . "\n";
    }
} else {
    echo "✓ reference_number column already exists\n";
}

echo "\n✓ Database migration completed successfully!\n";
?>
