<?php 

require __DIR__.'/../handlers/Database.php';
require __DIR__.'/../handlers/customersHandler.php';
require __DIR__.'/../handlers/appliancesHandler.php';

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
        'error' => $success ? null : ($message ?: 'An error occured') 
    ]);
    exit;
}

// create connection with the database
$database = new Database();
$db = $database->getConnection();

//Instantiate handlers
$customerHandler = new CustomerHandler($db);
$applianceHandler = new ApplianceHandler($db);

//Get request method
$method = $_SERVER['REQUEST_METHOD'];

//get input data
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        //customer endpoints
        case 'getAllCustomers': 
            try {
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';

                $result = $customerHandler->getAllCustomersPaginated($page, $itemsPerPage, $search);
                if ($result === false) {
                     sendResponse(false, null, 'Failed to retrieve customers', 500);
                }
                sendResponse(true, $result, 'Customers loaded');
            } catch (Exception $e) {
                sendResponse(false, null, $e->getMessage(), 400);
            }     
            break;

        case 'getCustomerById': 
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Customer ID is required', 400);
            }
            
            $result = $customerHandler->getCustomerById($id);

            if (!$result) {
                sendResponse(false, null, 'Customer not found', 400);
            }
            sendResponse(true, $result, 'Customer retrieved successfully');
            break;

        case 'addCustomer': 
            $required = ['first_name', 'last_name', 'address', 'phone_no'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $result = $customerHandler->addCustomer(
                $input['first_name'],
                $input['last_name'],
                $input['address'],
                $input['phone_no']
            );

            if ($result === false) {
                sendResponse(false, null, 'Failed to add customer', 500);
            }
            sendResponse(true, ['id' => $result], 'Customer added successfully', 201);
            break;

        case 'updateCustomer':
            $required = ['customer_id', 'first_name', 'last_name', 'address', 'phone_no'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $result = $customerHandler->updateCustomer(
                $input['customer_id'],
                $input['first_name'],
                $input['last_name'],
                $input['address'],
                $input['phone_no']
            );

            if ($result === false) {
                sendResponse(false, null, 'Failed to update', 500);
            }
            sendResponse(true, null, 'Customer updated successfully');
            break;

        case 'deleteCustomer': 
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Customer ID is required', 400);
            }

            $result = $customerHandler->deleteCustomer($id);
            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === false) {
                    sendResponse(false, null, $result['message'] ?: 'Failed to delete customer', 500);
                }
                sendResponse(true, null, $result['message'] ?: 'Customer deleted successfully');
            } else {
                if ($result === false) {
                    sendResponse(false, null, 'Failed to delete customer', 500);
                }
                sendResponse(true, null, 'Customer deleted successfully');
            }
            break;

        //appliances endpoints
        case 'getAllAppliances':
            try {
                $result = $applianceHandler->getAllAppliances();
                if($result === false) {
                    sendResponse(false, null, 'Failed to retrive appliances', 500);
                }
                sendResponse(true, $result, count($result) . ' Appliances found');
            } catch(Exception $e) {
                sendResponse(false, null, $e->getMessage(), 400);
            }
            break;


        case 'getAppliancesByCustomerId':
            $customerId = $_GET['customerId'] ?? null;
            if (!$customerId) {
                sendResponse(false, null, 'Customer ID is required', 400);
            } 
            
            $result = $applianceHandler->getAppliancesByCustomerId($customerId);
            if ($result === false) {
                sendResponse(false, null, 'Failed to retrieve appliances', 500);
            }

            sendResponse(true, $result, count($result) . ' appliances found');
            break;

        case 'addAppliance':
            $required = [
                'customer_id', 'brand', 'product', 'category', 'status'
            ];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            // $warranty_end = isset($input['warranty_end']) && $input['warranty_end'] !== '' ? $input['warranty_end'] : null;
            $warranty_end = null;
            if(isset($input['warranty_end']) && $input['warranty_end'] !== '' && $input['warranty_end'] !== 'Other') {
                $warranty_end = $input['warranty_end'];
            }

            $result = $applianceHandler->addAppliance(
                $input['customer_id'],
                $input['brand'],
                $input['product'],
                $input['model_no'],
                $input['serial_no'],
                $input['date_in'] ?? null,
                $warranty_end,
                $input['category'],
                $input['status'] ?? 'Active',
            );

            if ($result === false) {
                 sendResponse(false, null, 'Failed to add appliance', 500);
            } 
            sendResponse(true, ['id' => $result], 'Appliance added successfully', 201);
            break;
        
        case 'updateAppliance': 
            $required = [
                'appliance_id', 'brand', 'product', 'category', 'status'
            ];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            // $warranty_end = isset($input['warranty_end']) && $input['warranty_end'] !== '' ? $input['warranty_end'] : null;
            $warranty_end = null;
            if(isset($input['warranty_end']) && $input['warranty_end'] !== '' && $input['warranty_end'] !== 'Other') {
                $warranty_end = $input['warranty_end'];
            }

            $result = $applianceHandler->updateAppliance(
                $input['appliance_id'],
                $input['brand'],
                $input['product'],
                $input['model_no'],
                $input['serial_no'],
                $input['date_in'] ?? null,
                $warranty_end,
                $input['category'],
                $input['status'] ?? 'Active',
            );

            if ($result === false) {
                sendResponse(false, null, 'Failed to update appliance', 500);
            }
            sendResponse(true, null, 'Appliance updated successfully');
            break;

        case 'deleteAppliance':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Appliance ID is required', 400);
            }
            $result = $applianceHandler->deleteAppliance($id);

            if ($result === false) {
                sendResponse(false, null, 'Failed to delete appliance', 500);
            }
            sendResponse(true, null, 'Appliance deleted successfully');
            break;

        default: 
            sendResponse(false, null, 'Endpoint not found', 404);
    }
}  catch (Exception $e) {
     sendResponse(false, null, $e->getMessage(), 400);
}
