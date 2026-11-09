<?php 

require __DIR__.'/../../backend/handlers/Database.php';
require __DIR__.'/../../backend/handlers/partsHandler.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Header: Content-Type, Authorization");

function sendResponse($success, $data = null, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'error' => $success ? null : ($message ?: 'An error occured')
    ]);
    exit;
}

$database = new Database();
$db =  $database->getConnection();

$partHandler = new PartsHandler($db);

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

try{
    switch($action) {

        case 'getAllParts':
            try {
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';

                // Use paginated path
                $result = $partHandler->getAllPartsPaginated($page, $itemsPerPage, $search);
                if (!$result['success']) {
                    sendResponse(false, null, $result['message'], 400);
                }
                sendResponse(true, $result['data'], $result['message'] ?: 'Parts loaded');
            } catch (Exception $e) {
                sendResponse(false, null, $e->getMessage(), 400);
            }
            break;

        case 'getPartsById': 
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Part ID is required', 400);
            }

            $result = $partHandler->getPartsById($id);
            if (!$result) {
                sendResponse(false, null, 'Part not found', 400);
            }
            sendResponse(true, $result, 'Part retrieved successfully');
            break;

        case 'addPart':
            $required = [
                'parts_no', 'description', 'price', 'quantity_stock'
            ];

            foreach($required as $field) {
                if(empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $result = $partHandler->addParts(
                $input['parts_no'],
                $input['description'],
                $input['price'],
                $input['quantity_stock']
            );

            if($result === false) {
                sendResponse(false, null, 'Failed to add part', 500);
            }
            sendResponse(true, ['id' => $result], 'Part added successfully', 201);
            break;

        case 'updatePart':
            $required = [
                'part_id', 'parts_no', 'description', 'price', 'quantity_stock'
            ];
            foreach($required as $field) {
                if(empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $result = $partHandler->updateParts(
                $input['part_id'],
                $input['parts_no'],
                $input['description'],
                $input['price'],
                $input['quantity_stock'],
            );

            if($result === false) {
                sendResponse(false, null, 'Failed to update', 500);
            }
            sendResponse(true, null, 'Part updated successfully');
            break;

        case 'deletePart':
            $id = $_GET['id'] ?? null;
            if(!$id) {
                sendResponse(false, null, 'Part ID required', 400);
            }

            $result = $partHandler->deleteParts($id);
            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === false) {
                    sendResponse(false, null, $result['message'] ?: 'Failed to delete part', 500);
                }
                sendResponse(true, null, $result['message'] ?: 'Part archived successfully');
            } else {
                // Legacy boolean return
                if ($result === false) {
                    sendResponse(false, null, 'Failed to delete part', 500);
                }
                sendResponse(true, null, 'Part archived successfully');
            }
            break;
            
    }
} catch (Exception $e) {
    sendResponse(false, null, $e->getMessage(), 400);
}
?>