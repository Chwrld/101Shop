<?php
require_once 'customer_handler.php';

if(isset($_GET['customer_id'])) {
    $customer_handler = new CustomerHandler();
    $result = $customer_handler->getCustomerById($_GET['customer_id']);
    
    if($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode($customer);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Customer ID not provided']);
}
?> 