<?php

require __DIR__ . '/../../backend/handlers/Database.php';
require __DIR__ . '/../../backend/handlers/serviceHandler.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Prevent PHP notices/warnings from being sent to the client as HTML
ini_set('display_errors', '0');
error_reporting(E_ALL);

function sendResponse($success, $data = null, $message = '', $httpCode = 200)
{
    // Clear any accidental output (warnings/notices) so client receives valid JSON
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db->connect_error) {
    sendResponse(false, null, 'Database connection failed: ' . $db->connect_error, 500);
}

$serviceHandler = new ServiceHandler($db);
$method = $_SERVER['REQUEST_METHOD'];
$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

if (($method === 'POST' || $method === 'PUT') && json_last_error() !== JSON_ERROR_NONE) {
    sendResponse(false, null, 'Invalid JSON input', 400);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $limit = $_GET['limit'] ?? 100;
            $offset = $_GET['offset'] ?? 0;
            $result = $serviceHandler->getAll($limit, $offset);
            sendResponse($result['success'], $result['data'], $result['message']);
            break;

        case 'getById':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if (!$id || !is_numeric($id)) {
                sendResponse(false, null, 'Valid Report ID required', 400);
            }

            $result = $serviceHandler->getById($id);
            sendResponse($result['success'], $result['data'], $result['message']);
            break;

        case 'create':
            if ($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            // Only validate the 4 required fields
            $required = ['customer_name', 'appliance_name', 'date_in', 'status'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            // Validate date format
            $dateIn = DateTime::createFromFormat('Y-m-d', $input['date_in']);
            if (!$dateIn) {
                sendResponse(false, null, 'Invalid date_in format (expected Y-m-d)', 400);
            }

            // Parse optional date_pulled_out
            $datePulledOut = null;
            if (!empty($input['date_pulled_out'])) {
                $datePulledOut = DateTime::createFromFormat('Y-m-d', $input['date_pulled_out']);
                if (!$datePulledOut) {
                    sendResponse(false, null, 'Invalid date_pulled_out format (expected Y-m-d)', 400);
                }
            }

            // Check for duplicate records before creating
            $checkDuplicateQuery = "
                SELECT report_id FROM service_reports 
                WHERE customer_name = ? AND appliance_name = ? AND date_in = ?
                LIMIT 1
            ";
            $stmt = $db->prepare($checkDuplicateQuery);
            if ($stmt) {
                $stmt->bind_param('sss', $input['customer_name'], $input['appliance_name'], $input['date_in']);
                $stmt->execute();
                $stmt->bind_result($existingReportId);
                
                if ($stmt->fetch()) {
                    // Duplicate record found
                    $stmt->close();
                    sendResponse(false, ['existing_report_id' => $existingReportId], 'This service report already exists (Report ID: ' . $existingReportId . '). Cannot create duplicate records.', 409);
                }
                $stmt->close();
            }

            // Create service report with minimal required data
            $report = new Service_report(
                $input['customer_name'],
                $input['appliance_name'],
                $dateIn,
                $input['status'],
                $input['dealer'] ?? '',
                null, // dop
                $datePulledOut, // date_pulled_out (now populated from input)
                $input['findings'] ?? '',
                $input['remarks'] ?? '',
                ['shop'], // default location
                null, // customer_id
                null  // appliance_id
            );

            // Create minimal service detail with empty/default values
            $detail = new Service_detail(
                ['repair'], // default service_types
                0, // service_charge
                null, // date_repaired
                null, // date_delivered
                $input['complaint'] ?? '',
                0, // labor
                0, // pullout_delivery
                0, // parts_total_charge
                0, // total_amount
                '', // receptionist
                '', // manager
                '', // technician
                ''  // released_by
            );

            // Save to database
            $result = $serviceHandler->create($report, $detail, []); // empty parts array

            if ($result['success']) {
                sendResponse(true, ['report_id' => $result['data']], 'Service report created successfully');
            } else {
                sendResponse(false, null, $result['message'], 500);
            }
            break;

        case 'update':
            if ($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if (!$id || !is_numeric($id)) {
                sendResponse(false, null, 'Valid Report ID required', 400);
            }

            // Validate required fields
            $required = ['customer_name', 'appliance_name', 'date_in', 'status'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            // Validate date format
            $dateIn = DateTime::createFromFormat('Y-m-d', $input['date_in']);
            if (!$dateIn) {
                sendResponse(false, null, 'Invalid date_in format (expected Y-m-d)', 400);
            }

            // Parse optional date_pulled_out
            $datePulledOut = null;
            if (!empty($input['date_pulled_out'])) {
                $datePulledOut = DateTime::createFromFormat('Y-m-d', $input['date_pulled_out']);
                if (!$datePulledOut) {
                    sendResponse(false, null, 'Invalid date_pulled_out format (expected Y-m-d)', 400);
                }
            }

            // Create updated report object
            $report = new Service_report(
                $input['customer_name'],
                $input['appliance_name'],
                $dateIn,
                $input['status'],
                $input['dealer'] ?? '',
                null, // dop
                $datePulledOut, // date_pulled_out (now populated from input)
                $input['findings'] ?? '',
                $input['remarks'] ?? '',
                ['shop'], // default location
                null, // customer_id
                null  // appliance_id
            );

            // Create minimal service detail
            $detail = new Service_detail(
                ['repair'],
                0,
                null,
                null,
                $input['complaint'] ?? '',
                0,
                0,
                0,
                0,
                '',
                '',
                '',
                ''
            );

            // Update in database
            $result = $serviceHandler->update($id, $report, $detail, []);

            if ($result['success']) {
                sendResponse(true, null, 'Service report updated successfully');
            } else {
                sendResponse(false, null, $result['message'], 500);
            }
            break;

        case 'delete':
            if ($method !== 'DELETE') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if (!$id || !is_numeric($id)) {
                sendResponse(false, null, 'Valid Report ID required', 400);
            }

            $result = $serviceHandler->delete($id);
            sendResponse($result['success'], null, $result['message']);
            break;

        default:
            sendResponse(false, null, 'Invalid action', 400);
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
}
