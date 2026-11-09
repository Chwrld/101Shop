<?php
require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user']['session_token'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once __DIR__ . '/../backend/handlers/authHandler.php';
$auth =  new AuthHandler();

$action = $_GET['action'] ?? '';

switch($action) {
    case 'extend':
        $result = $auth->extendSession();
        echo json_encode(['success' => $result]);
        break;

    case 'timeleft':
        $timeleft = $auth->getSessionTimeLeft();
        echo json_encode(['success' => true, 'time_left' => $timeleft]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>