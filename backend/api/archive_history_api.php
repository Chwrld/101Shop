<?php 
require __DIR__.'/../handlers/auditHandler.php';
require __DIR__.'/../handlers/archiveHandler.php';
require __DIR__.'/../handlers/Database.php';

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

// Create connection with the database
$database = new Database();
$db = $database->getConnection();

// Instantiate handlers
$auditHandler = new AuditHandler($db);
$archiveHandler = new ArchiveHandler($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getActivityLog':
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
            $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            $result = $auditHandler->getActivityLog($page, $itemsPerPage, $filter, $search);
            sendResponse(true, $result, 'Activity log retrieved successfully');
            break;
            
        case 'getArchivedRecords':
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            error_log('ArchiveHistory API: getArchivedRecords called - page=' . $page . ' itemsPerPage=' . $itemsPerPage . ' search=' . $search);
            
            $result = $archiveHandler->getArchivedRecords($page, $itemsPerPage, $search);
            sendResponse(true, $result, 'Archived records retrieved successfully');
            break;
            
        case 'restoreRecord':
            $archive_id = isset($_GET['id']) ? intval($_GET['id']) : null;
            if (!$archive_id) {
                sendResponse(false, null, 'Archive ID is required', 400);
            }
            
            $result = $archiveHandler->restoreRecord($archive_id);
            if ($result) {
                sendResponse(true, null, 'Record restored successfully');
            } else {
                sendResponse(false, null, 'Failed to restore record', 500);
            }
            break;
            
        default:
            sendResponse(false, null, 'Endpoint not found', 404);
    }
} catch (Exception $e) {
    sendResponse(false, null, $e->getMessage(), 400);
}
?>
