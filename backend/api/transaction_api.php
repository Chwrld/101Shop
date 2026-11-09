<?php

require __DIR__.'/../../backend/handlers/transactionsHandler.php';
require __DIR__.'/../../backend/handlers/Database.php';

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
if($db->connect_error) {
    sendResponse(false, null, 'Database connection failed: ' . $db->connect_error, 500);
}
$transactionHandler = new transactionsHandlers($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

$action = $_GET['action'] ?? '';

try {
    switch($action) {

        case 'getAll':
            if($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Use paginated path for consistency with others
            $result = $transactionHandler->getAllTransactionsPaginated($page, $itemsPerPage, $search);
            if ($result === false) {
                sendResponse(false, null, 'Failed to retrieve transactions', 500);
            }
            sendResponse(true, $result, 'Transactions loaded');
            break;

        case 'getById':
            if($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $id = $_GET['id'] ?? null;
            if(!$id || !is_numeric($id)) {
                sendResponse(false, null, 'Valid Transaction ID required', 400);
            }

            $result = $transactionHandler->getTransactionById($id);
            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 404);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'createFromReport':
            if($method !== 'POST') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $reportId = isset($input['report_id']) ? (int)$input['report_id'] : null;
            $customerName = isset($input['customer_name']) ? trim($input['customer_name']) : null;
            $applianceName = isset($input['appliance_name']) ? trim($input['appliance_name']) : null;
            $totalAmount = isset($input['total_amount']) ? $input['total_amount'] : null;

            // validate fields: allow zero totalAmount but ensure not null and reportId numeric
            if (!is_numeric($reportId) || $reportId <= 0 || empty($customerName) || empty($applianceName) || $totalAmount === null) {
                sendResponse(false, null, 'All fields are required: report_id (numeric >0), customer_name, appliance_name, total_amount', 400);
            }
            error_log("createFromReport called with: reportId=$reportId, customerName={".addslashes($customerName)."}, applianceName={".addslashes($applianceName)."}, totalAmount=".json_encode($totalAmount));

            $result = $transactionHandler->createTransactionFromReport($reportId, $customerName, $applianceName, $totalAmount);
            if(!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'updatePayment':
            if($method !== 'PUT') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $transactionId = isset($input['transaction_id']) ? intval($input['transaction_id']) : null;
            $paymentStatus = $input['payment_status'] ?? null;
            $receivedById = isset($input['received_by']) ? intval($input['received_by']) : null;
            $paymentMethod = $input['payment_method'] ?? null;
            $referenceNumber = $input['reference_number'] ?? null;
            $paymentDueDate = $input['payment_due_date'] ?? null;

            if(!$transactionId || !$paymentStatus) {
                sendResponse(false, null, 'Transaction ID and payment status are required', 400);
            }

            if(!in_array($paymentStatus, ['Paid', 'Pending'])) {
                sendResponse(false, null, 'Invalid payment status', 400);
            }

            if(!$paymentMethod) {
                sendResponse(false, null, 'Payment method is required', 400);
            }

            if(!in_array($paymentMethod, ['Cash', 'GCash'])) {
                sendResponse(false, null, 'Invalid payment method', 400);
            }

            // Validate reference number if GCash
            if($paymentMethod === 'GCash' && empty($referenceNumber)) {
                sendResponse(false, null, 'Reference number is required for GCash payments', 400);
            }

            $result = $transactionHandler->updatePaymentStatus($transactionId, $paymentStatus, $receivedById, $paymentMethod, $referenceNumber, $paymentDueDate);
            if (!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'setPaymentDueDate':
            if($method !== 'PUT') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $transactionId = isset($input['transaction_id']) ? intval($input['transaction_id']) : null;
            $paymentDueDate = isset($input['payment_due_date']) ? trim($input['payment_due_date']) : null;

            if(!$transactionId || !$paymentDueDate) {
                sendResponse(false, null, 'Transaction ID and payment due date are required', 400);
            }

            $result = $transactionHandler->setPaymentDueDate($transactionId, $paymentDueDate);
            if (!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'getStats':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method not allowed', 405);
            }

            $result = $transactionHandler->getTransactionStats();
            if (!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;
 
        default:
            sendResponse(false, null, 'Invalid action specified', 400);
            break;
    }
} catch(Exception $e) {
    error_log("API Exception: " . $e->getMessage());
    sendResponse(false, null, $e->getMessage(), 500);
}
