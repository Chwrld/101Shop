<?php

// Start output buffering and session before any headers/output
if (ob_get_level() === 0) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../backend/handlers/Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Prevent PHP notices/warnings from being sent to the client as HTML
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'data' => null,
            'message' => 'Server error: ' . $err['message']
        ]);
    }
});

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

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Check if connection is successful
    if (!$conn) {
        sendResponse(false, null, 'Database connection failed', 500);
    }

    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

    // Read JSON body once (php://input can only be consumed once)
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);
    if (!is_array($jsonInput)) {
        $jsonInput = [];
    }

    // For JSON POST requests, also check the JSON body for action
    if (empty($action) && isset($jsonInput['action'])) {
        $action = $jsonInput['action'];
    }

    // Handle different actions
    switch ($action) {
        case 'addProgressComment':
            handleAddProgressComment($conn, $jsonInput);
            break;
        case 'getProgressComments':
            handleGetProgressComments($conn);
            break;
        case 'deleteProgressComment':
            handleDeleteProgressComment($conn);
            break;
        default:
            sendResponse(false, null, 'Unknown action: ' . $action, 400);
    }

} catch (Exception $e) {
    sendResponse(false, null, 'Exception: ' . $e->getMessage(), 500);
}

// ============ HANDLER FUNCTIONS ============

function handleAddProgressComment($conn, $input = null)
{
    // Get POST data (passed in because php://input can only be read once)
    if ($input === null) {
        $decoded = json_decode(file_get_contents('php://input'), true);
        $input = is_array($decoded) ? $decoded : null;
    }

    if (!$input) {
        sendResponse(false, null, 'Invalid JSON input', 400);
    }

    $report_id = isset($input['report_id']) ? intval($input['report_id']) : 0;
    $progress_key = isset($input['progress_key']) ? trim($input['progress_key']) : '';
    $comment_text = isset($input['comment_text']) ? trim($input['comment_text']) : '';
    $created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;
    $created_by_name = isset($_SESSION['name']) ? trim($_SESSION['name']) : 'Unknown User';

    // Validation
    if (!$report_id || !$progress_key || !$comment_text) {
        sendResponse(false, null, 'Missing required fields: report_id=' . $report_id . ', progress_key=' . $progress_key . ', comment_text=' . strlen($comment_text), 400);
    }

    // Ensure table exists with proper schema
    $ensureTableQuery = "
        CREATE TABLE IF NOT EXISTS service_progress_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            progress_key VARCHAR(50) NOT NULL,
            comment_text LONGTEXT NOT NULL,
            created_by INT NULL,
            created_by_name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_report_id (report_id),
            KEY idx_progress_key (progress_key),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if (!$conn->query($ensureTableQuery)) {
        sendResponse(false, null, 'Failed to create comments table: ' . $conn->error, 500);
    }

    // Ensure required columns exist for older schemas
    $requiredColumns = [
        'comment_text' => "ALTER TABLE service_progress_comments ADD COLUMN comment_text LONGTEXT NULL",
        'created_by' => "ALTER TABLE service_progress_comments ADD COLUMN created_by INT NULL",
        'created_by_name' => "ALTER TABLE service_progress_comments ADD COLUMN created_by_name VARCHAR(255) NULL",
        'created_at' => "ALTER TABLE service_progress_comments ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "ALTER TABLE service_progress_comments ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    foreach ($requiredColumns as $column => $alterSql) {
        $colRes = $conn->query("SHOW COLUMNS FROM service_progress_comments LIKE '" . $conn->real_escape_string($column) . "'");
        if ($colRes && $colRes->num_rows === 0) {
            @$conn->query($alterSql); // best-effort for legacy schema drift
        }
        if ($colRes) {
            $colRes->free();
        }
    }

    // Insert comment
    $insertQuery = "
        INSERT INTO service_progress_comments 
        (report_id, progress_key, comment_text, created_by, created_by_name) 
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        sendResponse(false, null, 'Prepare failed: ' . $conn->error, 500);
    }

    // Bind parameters: i=int, s=string, s=string, i=int, s=string
    // Order: report_id(i), progress_key(s), comment_text(s), created_by(i), created_by_name(s)
    if ($created_by === null) {
        $created_by = null; // keep null for binding
    }
    $stmt->bind_param('issis', $report_id, $progress_key, $comment_text, $created_by, $created_by_name);

    if (!$stmt->execute()) {
        sendResponse(false, null, 'Failed to add comment: ' . $stmt->error, 500);
    }

    $commentId = $stmt->insert_id;
    $stmt->close();

    sendResponse(true, ['id' => $commentId], 'Comment added successfully');
}

function handleGetProgressComments($conn)
{
    $report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

    if (!$report_id) {
        sendResponse(false, null, 'Missing report_id', 400);
    }

    // Ensure table exists with proper schema
    $ensureTableQuery = "
        CREATE TABLE IF NOT EXISTS service_progress_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            progress_key VARCHAR(50) NOT NULL,
            comment_text LONGTEXT NOT NULL,
            created_by INT NULL,
            created_by_name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_report_id (report_id),
            KEY idx_progress_key (progress_key),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if (!$conn->query($ensureTableQuery)) {
        // Table creation failed, but try to continue
        error_log('Failed to create comments table: ' . $conn->error);
    }

    // Fetch all comments for the report
    $query = "
        SELECT 
            id,
            report_id,
            progress_key,
            comment_text,
            created_by_name as created_by,
            created_at
        FROM service_progress_comments
        WHERE report_id = ?
        ORDER BY created_at ASC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        sendResponse(false, null, 'Prepare failed: ' . $conn->error, 500);
    }

    $stmt->bind_param('i', $report_id);
    
    if (!$stmt->execute()) {
        sendResponse(false, null, 'Query failed: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $comments = [];

    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => intval($row['id']),
            'report_id' => intval($row['report_id']),
            'progress_key' => $row['progress_key'],
            'comment_text' => $row['comment_text'],
            'created_by' => $row['created_by'],
            'created_at' => $row['created_at']
        ];
    }

    $stmt->close();

    sendResponse(true, $comments, 'Comments retrieved successfully');
}

function handleDeleteProgressComment($conn)
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$id) {
        sendResponse(false, null, 'Missing comment id', 400);
    }

    // Check if table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'service_progress_comments'";
    $result = $conn->query($tableCheckQuery);
    
    if ($result->num_rows == 0) {
        // Table doesn't exist
        sendResponse(false, null, 'Comments table not found', 404);
    }

    // Delete comment
    $deleteQuery = "DELETE FROM service_progress_comments WHERE id = ?";

    $stmt = $conn->prepare($deleteQuery);
    if (!$stmt) {
        sendResponse(false, null, 'Prepare failed: ' . $conn->error, 500);
    }

    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        sendResponse(false, null, 'Failed to delete comment: ' . $stmt->error, 500);
    }

    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($affectedRows == 0) {
        sendResponse(false, null, 'Comment not found', 404);
    }

    sendResponse(true, null, 'Comment deleted successfully');
}

?>
