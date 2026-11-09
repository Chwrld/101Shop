<?php
require_once 'parts_handler.php';

if(isset($_GET['part_id'])) {
    $parts_handler = new PartsHandler();
    $result = $parts_handler->getPartById($_GET['part_id']);
    
    if($result->num_rows > 0) {
        $part = $result->fetch_assoc();
        echo json_encode($part);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Part not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Part ID not provided']);
}
?> 