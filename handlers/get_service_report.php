<?php
require_once 'service_report_handler.php';

if(isset($_GET['report_id'])) {
    $service_report_handler = new ServiceReportHandler();
    $result = $service_report_handler->getServiceReportById($_GET['report_id']);
    
    if($result->num_rows > 0) {
        $report = $result->fetch_assoc();
        echo json_encode($report);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Service report not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Report ID not provided']);
}
?> 