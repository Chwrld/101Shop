<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/backend/handlers/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Check for duplicate part names
$sql = "SELECT COUNT(*) as count, part_no FROM parts GROUP BY part_no HAVING COUNT(*) > 1";
$result = $conn->query($sql);

echo "Duplicate Parts:\n";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Part: {$row['part_no']}, Count: {$row['count']}\n";
    }
} else {
    echo "No duplicates found by part_no\n";
}

// Check total parts count
$sql2 = "SELECT COUNT(*) as total FROM parts";
$result2 = $conn->query($sql2);
$total = $result2->fetch_assoc();
echo "\nTotal parts in database: {$total['total']}\n";

// Show all parts
$sql3 = "SELECT part_id, part_no FROM parts ORDER BY part_no";
$result3 = $conn->query($sql3);
echo "\nAll parts:\n";
$parts = [];
while ($row = $result3->fetch_assoc()) {
    $parts[] = $row;
    echo "ID: {$row['part_id']}, Name: {$row['part_no']}\n";
}
?>
