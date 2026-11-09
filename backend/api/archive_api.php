<?php

require __DIR__ . '/../handlers/archiveHandler.php';
require __DIR__ . '/../handlers/Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function sendResponse($success, $data = null, $message = '', $httpCode = 200) {
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

$archiveHandler = new ArchiveHandler($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            $result = $archiveHandler->getArchivedRecords($page, $itemsPerPage, $search);
            
            sendResponse(true, $result, 'Archive records loaded successfully');
            break;

        case 'restore':
            if ($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $archiveId = $input['archive_id'] ?? null;

            if (!$archiveId) {
                sendResponse(false, null, 'Archive ID is required', 400);
            }

            $result = $archiveHandler->restoreRecord($archiveId);
            
            if ($result) {
                sendResponse(true, null, 'Record restored successfully');
            } else {
                sendResponse(false, null, 'Failed to restore record', 500);
            }
            break;

        default:
            sendResponse(false, null, 'Invalid action specified', 400);
            break;
    }
} catch (Exception $e) {
    error_log("Archive API Exception: " . $e->getMessage());
    sendResponse(false, null, $e->getMessage(), 500);
}
