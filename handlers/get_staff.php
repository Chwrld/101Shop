<?php
require_once 'staff_handler.php';

if(isset($_GET['staff_id'])) {
    $staff_handler = new StaffHandler();
    $result = $staff_handler->getStaffById($_GET['staff_id']);
    
    if($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo json_encode($staff);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Staff member not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Staff ID not provided']);
}
?> 