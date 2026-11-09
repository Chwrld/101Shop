<?php
require __DIR__ . "/../../backend/handlers/Database.php";
require __DIR__ . "/../../backend/handlers/servicePriceHandler.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function sendResponse($success, $data = null, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'error' => $success ? null : ($message ?: 'An error occurred')
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$servicePriceHandler = new servicePriceHandler($db);

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'getAll':
            if($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $result = $servicePriceHandler->getAllServicePrices();
            sendResponse($result['success'], $result['data'], $result['message']);
            break;

        case 'getAllForFrontend':
            if($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }
            // Optional simple list without pagination
            $result = $servicePriceHandler->getAllServicePricesFrontend();
            sendResponse($result['success'], $result['data'], $result['message']);
            break;

        case 'getAllPaginated':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            $result = $servicePriceHandler->getAllPaginated($page, $itemsPerPage, $search);
            if (!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'getById':
            if($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if(!$id) {
                sendResponse(false, null, 'Service ID is required', 400);
            }

            $result =  $servicePriceHandler->getServicePriceById($id);

            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 404);
            }
            sendResponse(true, $result['data'], 'Service retrieved successfully');
            break;

        case 'addService':
            if($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $required = ['service_name', 'service_price'];
            foreach ($required as $field) {
                if(empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $service_name = strtolower(trim($input['service_name']));
            $service_price = floatval($input['service_price']);

            if($service_price <= 0) {
                sendResponse(false, null, 'Service price must be greater then 0', 400);
            }

            $result = $servicePriceHandler->addServicePrice($service_name, $service_price);
            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 409);
            }
            sendResponse(true, $result['data'], $result['message'], 201);
            break;

        case 'updateService':
            if($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $required = ['service_id', 'service_price'];
            foreach ($required as $field) {
                if(empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $service_id = intval($input['service_id']);
            $service_price = floatval($input['service_price']);

            if($service_price <= 0) {
                sendResponse(false, null, 'Service price must be greater than 0', 400);
            }

            $result = $servicePriceHandler->updateServicePrice($service_id, $service_price);
            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, null, $result['message']);
            break;

        case 'deleteService':
            if($method !== 'DELETE') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if(!$id) {
                sendResponse(false, null, 'Service ID is required', 400);
            }

            $result = $servicePriceHandler->deleteServicePrice($id);
            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, null, $result['message']);
            break;

        default:
            sendResponse(false, null, 'Invalid action specified', 400);

    }
} catch (Exception $e) {
    error_log("Service Price API Exception: " . $e->getMessage());
    sendResponse(false, null, $e->getMessage(), 500);
}
?>