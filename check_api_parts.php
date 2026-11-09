<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/backend/handlers/Database.php';
require_once __DIR__ . '/backend/handlers/partsHandler.php';

$db = new Database();
$conn = $db->getConnection();
$partsHandler = new PartsHandler($conn);

// Get all parts using the same method as the API
$result = $partsHandler->getAllPartsPaginated(1, 1000);

echo "API Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT);

if ($result['success'] && !empty($result['data']['parts'])) {
    $parts = $result['data']['parts'];
    $partNames = [];
    $duplicates = [];
    
    foreach ($parts as $part) {
        $name = $part['part_no'];
        if (isset($partNames[$name])) {
            if (!isset($duplicates[$name])) {
                $duplicates[$name] = 2;
            } else {
                $duplicates[$name]++;
            }
        } else {
            $partNames[$name] = 1;
        }
    }
    
    if (!empty($duplicates)) {
        echo "\n\nDuplicate parts found:\n";
        foreach ($duplicates as $name => $count) {
            echo "$name: appears $count times\n";
        }
    } else {
        echo "\n\nNo duplicates found in API response\n";
    }
}
?>
