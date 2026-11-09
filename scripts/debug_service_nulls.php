<?php
require_once __DIR__ . '/../backend/handlers/Database.php';

// Simple debug script to print last 10 rows from service_reports
try {
    $db = new Database();
    $conn = $db->getConnection();

    if ($conn->connect_error) {
        throw new Exception('DB connect error: ' . $conn->connect_error);
    }

    $sql = "SELECT report_id, customer_name, date_in, dop, date_pulled_out FROM service_reports ORDER BY report_id DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        // Keep raw values so NULL remains NULL
        $rows[] = $r;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => count($rows),
        'rows' => $rows
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
