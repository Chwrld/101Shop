<?php
/*
 * test_archive.php
 * Simple CLI test script to validate archive behavior for deleting a part.
 *
 * Usage (PowerShell):
 *   php c:\xampp\htdocs\RepairSystem-main\scripts\test_archive.php
 *
 * Edit the BASE_URL and DB config as necessary if your app runs on different host/port.
 */

$BASE_URL = 'http://localhost/RepairSystem-main';
require __DIR__ . '/../database/database.php';

$config = include __DIR__ . '/../database/database.php';
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);
if ($mysqli->connect_error) {
    echo "DB connection failed: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}

function execute($mysqli, $sql) {
    if ($mysqli->query($sql) === TRUE) return true;
    echo "SQL error: " . $mysqli->error . PHP_EOL;
    return false;
}

// 1) Create a sample part to use for test
$part_no = 'TEST-' . time();
$description = 'Test part for archive script';
$price = 10.00;
$qty = 2;

// Insert part
$sql = "INSERT INTO parts (part_no, description, price, quantity_stock) VALUES ('{$mysqli->real_escape_string($part_no)}', '{$mysqli->real_escape_string($description)}', {$price}, {$qty})";
if (!execute($mysqli, $sql)) exit(1);
$partId = $mysqli->insert_id;

echo "Inserted test part with ID: $partId\n";

// 2) Call the parts delete endpoint which should archive the part (parts_api::deletePart uses GET)
$deleteUrl = "{$BASE_URL}/backend/api/parts_api.php?action=deletePart&id={$partId}";
echo "Calling delete API: $deleteUrl\n";
$response = @file_get_contents($deleteUrl);
if ($response === FALSE) {
    echo "Failed to call delete URL. Check server logs or base URL.\n";
    exit(1);
}

echo "API delete response: $response\n";

// 3) Wait a moment to be safe
sleep(1);

// 4) Verify archive_records has entry
$sql = "SELECT * FROM archive_records WHERE table_name = 'parts' AND record_id = {$partId} ORDER BY deleted_at DESC LIMIT 1";
$res = $mysqli->query($sql);
if (!$res) {
    echo "Failed to query archive_records: " . $mysqli->error . PHP_EOL;
    exit(1);
}
if ($row = $res->fetch_assoc()) {
    echo "Found archive record: id={$row['id']}, table_name={$row['table_name']}, record_id={$row['record_id']}, deleted_at={$row['deleted_at']}, deleted_by={$row['deleted_by']}\n";
    echo "Deleted data: {$row['deleted_data']}\n";
} else {
    echo "No archive record found for part id {$partId}.\n";
    // check if part still exists
    $check = $mysqli->query("SELECT * FROM parts WHERE part_id = {$partId}");
    if ($check && $check->num_rows > 0) {
        echo "Part still exists in parts table (delete possibly failed)\n";
    } else {
        echo "Part no longer in parts table and no archive entry -> delete occurred without archive.\n";
    }
}

// Cleanup: optionally remove inserted test records if needed
// (Commented out to preserve audit trail by default)
// $mysqli->query("DELETE FROM archive_records WHERE table_name = 'parts' AND record_id = {$partId}");
// $mysqli->query("DELETE FROM parts WHERE part_id = {$partId}");

$mysqli->close();

?>