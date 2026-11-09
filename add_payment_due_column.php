<?php
/**
 * Migration script to add payment_due_date column to transactions table
 * This should be run once to set up the database properly
 */

require __DIR__ . '/backend/handlers/Database.php';

$database = new Database();
$db = $database->getConnection();

if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}

// Check if payment_due_date column exists
$result = $db->query("SHOW COLUMNS FROM transactions LIKE 'payment_due_date'");

if ($result && $result->num_rows === 0) {
    // Add the payment_due_date column
    $sql = "ALTER TABLE transactions ADD COLUMN payment_due_date DATE NULL DEFAULT NULL COMMENT 'Date when payment is due'";
    
    if ($db->query($sql)) {
        echo "✅ Column 'payment_due_date' added successfully to transactions table!";
        http_response_code(200);
    } else {
        echo "❌ Error adding column: " . $db->error;
        http_response_code(500);
    }
} else {
    echo "✅ Column 'payment_due_date' already exists in transactions table.";
    http_response_code(200);
}

$db->close();
?>
