<?php

require __DIR__ . '/../../backend/handlers/Database.php';
require __DIR__ . '/../../backend/handlers/serviceHandler.php';
require __DIR__ . '/../../backend/handlers/partsHandler.php';

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

/**
 * Safely parse a date string into a DateTime object or return null.
 * Accepts 'Y-m-d' or other parseable formats; returns null on failure.
 */
function safeParseDate($value) {
    // Handle null, empty string, whitespace, or "null" string
    if (empty($value) || (is_string($value) && trim($value) === '') || $value === 'null') {
        return null;
    }
    
    // If already a DateTime, return as-is
    if ($value instanceof DateTime) return $value;

    // Trim whitespace from string values
    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') return null;
    }

    // Try strict Y-m-d first
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if ($dt !== false) return $dt;

    // Fallback to flexible parsing with exception handling
    try {
        return new DateTime($value);
    } catch (Exception $e) {
        error_log('safeParseDate: failed to parse date "' . $value . '": ' . $e->getMessage());
        return null;
    }
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

// DEBUG: Log raw input for create action only
if ($_GET['action'] === 'create' && $method === 'POST') {
    error_log("====== CREATE API DEBUG START ======");
    error_log("Raw php://input body: " . $rawBody);
    error_log("Decoded JSON: " . print_r($input, true));
    error_log("====== CREATE API DEBUG END ======");
}

if (($method === 'POST' || $method === 'PUT') && json_last_error() !== JSON_ERROR_NONE) {
    sendResponse(false, null, 'Invalid JSON input', 400);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            if ($method !== 'GET') {
                sendResponse(false, null, 'Method is incorrect', 405);
            }

            error_log("SERVICE API: getAll called");
            $limit = $_GET['limit'] ?? 100;
            $offset = $_GET['offset'] ?? 0;
            error_log("SERVICE API: limit=$limit, offset=$offset");
            $result = $serviceHandler->getAll($limit, $offset);
            error_log("SERVICE API: getAll result - success: " . ($result['success'] ? 'true' : 'false') . ", data count: " . (is_array($result['data']) ? count($result['data']) : 'null'));
            sendResponse($result['success'], $result['data'], $result['message']);

            break;

        case 'getById':
            try {
                if ($method !== 'GET') {
                    sendResponse(false, null, 'Method is incorrect', 405);
                }

                $id = $_GET['id'] ?? null;
                if (!$id || !is_numeric($id)) {
                    sendResponse(false, null, 'Valid Report ID required', 400);
                }

                $result = $serviceHandler->getById($id);
                if (!$result['success']) {
                    error_log("Error fetching report by ID: $id. Error: " . $result['message'] .
                        ". Data: " . var_export($result['data'], true));
                }
                sendResponse($result['success'], $result['data'], $result['message']);
            } catch (Exception $e) {
                error_log("Create API Error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                sendResponse(false, null, 'Create failed: ' . $e->getMessage(), 500);
            }
            break;

        case 'create':
            if ($method !== 'POST') {
                sendResponse(false, null, 'Method is incorrect', 405);
            }

            try {

                error_log("Received input data: " . print_r($input, true));

                $required = ['customer_name', 'appliance_name', 'date_in', 'status'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        sendResponse(false, null, "Missing required field: $field", 400);
                    }
                }

                // Create new parts handler for quantity management
                $partsHandler = new PartsHandler($db);

                // Verify parts quantities before proceeding (only if parts are provided)
                if (!empty($input['parts']) && is_array($input['parts'])) {
                    foreach ($input['parts'] as $part) {
                        // Skip empty parts
                        if (empty($part['part_name']) || empty($part['quantity']) || $part['quantity'] <= 0) {
                            continue;
                        }
                        
                        $partId = isset($part['part_id']) ? $part['part_id'] : null;
                        if (!$partId) {
                            error_log("Part ID missing for part: " . print_r($part, true));
                            sendResponse(false, null, "Part ID not found for {$part['part_name']}", 400);
                        }
                        
                        error_log("Checking stock for part ID: {$partId}, requested quantity: {$part['quantity']}");
                        $partDetails = $partsHandler->getPartsById($partId);
                        error_log("Part details: " . print_r($partDetails, true));
                        
                        if (!$partDetails) {
                            sendResponse(false, null, "Part not found with ID: {$partId}", 400);
                        }
                        
                        $availableStock = intval($partDetails['quantity_stock'] ?? 0);
                        $requestedQty = intval($part['quantity']);
                        
                        error_log("Stock check - Available: {$availableStock}, Requested: {$requestedQty}");
                        
                        if ($availableStock < $requestedQty) {
                            sendResponse(false, null, "Insufficient stock for part '{$part['part_name']}'. Requested: {$requestedQty}, Available: {$availableStock}", 400);
                        }
                    }
                }

                // Clean all date fields - convert empty strings to null
                $dopVal = isset($input['dop']) ? $input['dop'] : null;
                $datePulledVal = isset($input['date_pulled_out']) ? $input['date_pulled_out'] : null;

                // Treat empty, "null" string, or whitespace as null
                $dopVal = (empty(trim($dopVal ?? '')) || $dopVal === 'null') ? null : $dopVal;
                $datePulledVal = (empty(trim($datePulledVal ?? '')) || $datePulledVal === 'null') ? null : $datePulledVal;

                // Defensive parsing of required date_in
                $dateInObj = safeParseDate($input['date_in'] ?? null);
                if (!$dateInObj) {
                    sendResponse(false, null, 'Invalid or missing date_in (expected Y-m-d)', 400);
                }

                // Parse optional dates defensively using helper
                $dopObj = safeParseDate($dopVal);
                $datePulledObj = safeParseDate($datePulledVal);

                $report = new Service_report(
                    $input['customer_name'],
                    $input['appliance_name'],
                    $dateInObj,
                    $input['status'],
                    $input['dealer'] ?? '',
                    $dopObj,
                    $datePulledObj,
                    $input['findings'] ?? '',
                    $input['remarks'] ?? '',
                    $input['location'] ?? ['shop']
                );
                error_log("Service report object created");

                $service_types_list = (!empty($input['service_types']) && is_array($input['service_types'])) ? $input['service_types'] : ['repair'];
                // Defensive numeric defaults
                $service_charge_val = isset($input['service_charge']) ? floatval($input['service_charge']) : 0.0;
                $labor_val = isset($input['labor']) ? floatval($input['labor']) : 0.0;
                $pullout_val = isset($input['pullout_delivery']) ? floatval($input['pullout_delivery']) : 0.0;
                $parts_total_val = isset($input['parts_total_charge']) ? floatval($input['parts_total_charge']) : 0.0;
                $total_amount_val = isset($input['total_amount']) ? floatval($input['total_amount']) : 0.0;

                // Parse optional detail dates defensively - clean empty strings
                $dateRepairedInput = $input['date_repaired'] ?? null;
                $dateDeliveredInput = $input['date_delivered'] ?? null;
                
                // Clean empty strings before parsing
                if (is_string($dateRepairedInput) && trim($dateRepairedInput) === '') {
                    $dateRepairedInput = null;
                }
                if (is_string($dateDeliveredInput) && trim($dateDeliveredInput) === '') {
                    $dateDeliveredInput = null;
                }
                
                $dateRepairedObj = safeParseDate($dateRepairedInput);
                $dateDeliveredObj = safeParseDate($dateDeliveredInput);
                
                error_log("Date parsed - Repaired: " . ($dateRepairedObj ? $dateRepairedObj->format('Y-m-d') : 'null') . 
                         ", Delivered: " . ($dateDeliveredObj ? $dateDeliveredObj->format('Y-m-d') : 'null'));

                $detail = new Service_detail(
                    $service_types_list, // default to ['repair'] if missing or empty
                    $service_charge_val,
                    $dateRepairedObj,
                    $dateDeliveredObj,
                    $input['complaint'] ?? '',
                    $labor_val,
                    $pullout_val,
                    $parts_total_val,
                    $total_amount_val,
                    $input['receptionist'] ?? '',
                    $input['manager'] ?? '',
                    $input['technician'] ?? '',
                    $input['released_by'] ?? ''
                );
                error_log("Service detail object created");

                $partsData = [];
                if (!empty($input['parts'])) {
                    foreach ($input['parts'] as $part) {
                        $partsData[] = [
                            'part_name' => $part['Part_Name'] ?? $part['part_name'] ?? '',
                            'quantity' => (int)($part['Quantity'] ?? $part['quantity'] ?? 0),
                            'unit_price' => (float)($part['Unit_Price'] ?? $part['unit_price'] ?? 0)
                        ];
                    }
                }
                $partsUsed = new Parts_used(['parts' => $partsData]);

                $result = $serviceHandler->createCompleteServiceReport($report, $detail, $partsUsed);
                error_log("Create result: " . print_r($result, true));

                if (!$result || !$result['success']) {
                    sendResponse(false, null, $result['message'] ?? 'Failed to create service report', 400);
                }

                // Deduct parts quantities after successful service report creation
                if (!empty($input['parts']) && is_array($input['parts'])) {
                    foreach ($input['parts'] as $part) {
                        // Skip empty parts
                        if (empty($part['part_name']) || empty($part['quantity']) || $part['quantity'] <= 0) {
                            continue;
                        }
                        
                        try {
                            // Get the part ID from the selected option value
                            $partId = isset($part['part_id']) ? $part['part_id'] : null;
                            if (!$partId) {
                                error_log("Missing partId in input: " . print_r($part, true));
                                throw new Exception("Part ID not found for {$part['part_name']}");
                            }
                            $partsHandler->deductQuantity($partId, $part['quantity']);
                        } catch (Exception $e) {
                            error_log("Failed to deduct part quantity: " . $e->getMessage());
                            sendResponse(false, null, "Failed to update quantity for part {$part['part_name']}: " . $e->getMessage(), 500);
                        }
                    }
                }

                // Return a consistent report_id key for client consumption
                sendResponse(true, ['report_id' => $result['data']], 'Service report created successfully', 201);
            } catch (Exception $e) {
                error_log("Create API Error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                sendResponse(false, null, 'Create failed: ' . $e->getMessage(), 500);
            }
            break;

        case 'update':
            try {

                if ($method !== 'PUT') {
                    sendResponse(false, null, 'Method is incorrect', 405);
                }

                error_log("====== UPDATE API DEBUG START ======");
                error_log("Received input data: " . print_r($input, true));
                error_log("Status value received: '" . ($input['status'] ?? 'MISSING') . "' (type: " . gettype($input['status'] ?? null) . ")");
                error_log("====== UPDATE API DEBUG END ======");

                $id = $_GET['id'] ?? null;
                if (!$id) {
                    sendResponse(false, null, 'Report ID required', 400);
                }

                $required = ['customer_name', 'appliance_name', 'date_in', 'status'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        sendResponse(false, null, "Missing required field: $field", 400);
                    }
                }

                // Normalize date inputs for update
                $dopVal = isset($input['dop']) ? $input['dop'] : null;
                $datePulledVal = isset($input['date_pulled_out']) ? $input['date_pulled_out'] : null;
                $dopVal = ($dopVal === '' || $dopVal === 'null') ? null : $dopVal;
                $datePulledVal = ($datePulledVal === '' || $datePulledVal === 'null') ? null : $datePulledVal;

                // Defensive parsing for required date_in on update
                $dateInObj = null;
                $dateInObj = safeParseDate($input['date_in'] ?? null);
                if (!$dateInObj) {
                    // fallback to current date to prevent invalid DateTime on update
                    $dateInObj = new DateTime();
                }

                $dopObj = safeParseDate($dopVal);
                $datePulledObj = safeParseDate($datePulledVal);

                //update service report
                $report = new Service_report(
                    $input['customer_name'] ?? '',
                    $input['appliance_name'] ?? '',
                    $dateInObj,
                    $input['status'] ?? '',
                    $input['dealer'] ?? '',
                    $dopObj,
                    $datePulledObj,
                    $input['findings'] ?? '',
                    $input['remarks'] ?? '',
                    $input['location'] ?? ['shop']
                );
                error_log("Service report object created");

                $result = $serviceHandler->updateReport($id, $report);
                if (!$result['success']) {
                    sendResponse(false, null, $result['message'], 400);
                }

                //update service details
                $service_types_list = (!empty($input['service_types']) && is_array($input['service_types'])) ? $input['service_types'] : ['repair'];

                // Defensive numeric/parsing defaults for update
                $service_charge_val = isset($input['service_charge']) ? floatval($input['service_charge']) : 0.0;
                $labor_val = isset($input['labor']) ? floatval($input['labor']) : 0.0;
                $pullout_val = isset($input['pullout_delivery']) ? floatval($input['pullout_delivery']) : 0.0;
                $parts_total_val = isset($input['parts_total_charge']) ? floatval($input['parts_total_charge']) : 0.0;
                $total_amount_val = isset($input['total_amount']) ? floatval($input['total_amount']) : 0.0;

                $dateRepairedObj = null;
                $dateRepairedObj = safeParseDate($input['date_repaired'] ?? null);
                $dateDeliveredObj = safeParseDate($input['date_delivered'] ?? null);

                $detail = new Service_detail(
                    $service_types_list,
                    $service_charge_val,
                    $dateRepairedObj,
                    $dateDeliveredObj,
                    $input['complaint'] ?? '',
                    $labor_val,
                    $pullout_val,
                    $parts_total_val,
                    $total_amount_val,
                    $input['receptionist'] ?? '',
                    $input['manager'] ?? '',
                    $input['technician'] ?? '',
                    $input['released_by'] ?? ''
                );
                error_log("Service detail object created");

                $result = $serviceHandler->updateDetails($id, $detail);
                if (!$result['success']) {
                    sendResponse(false, null, $result['message'], 400);
                }

                $partsData = [];
                if (!empty($input['parts'])) {
                    foreach ($input['parts'] as $part) {
                        $partsData[] = [
                            'part_name' => $part['Part_Name'] ?? $part['part_name'] ?? '',
                            'quantity' => (int)($part['Quantity'] ?? $part['quantity'] ?? 0),
                            'unit_price' => (float)($part['Unit_Price'] ?? $part['unit_price'] ?? 0)
                        ];
                    }
                }
                $partsUsed = new Parts_used(['parts' => $partsData]);

                $result = $serviceHandler->updatePartsUsed($id, $partsUsed);
                error_log("Create result: " . print_r($result, true));

                if (!$result['success']) {
                    sendResponse(false, null, $result['message'], 400);
                }

                sendResponse(true, null, 'Service report updated successfully');
            } catch (Exception $e) {
                error_log("Update API Error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                sendResponse(false, null, 'Update failed: ' . $e->getMessage(), 500);
            }

            break;

        case 'assign':
            // Allows updating/claiming assignment fields (technician, manager, receptionist, released_by)
            if ($method !== 'POST') {
                sendResponse(false, null, 'Method is incorrect', 405);
            }

            try {
                $reportId = $input['report_id'] ?? $_GET['id'] ?? null;
                if (!$reportId || !is_numeric($reportId)) {
                    sendResponse(false, null, 'Valid Report ID required', 400);
                }

                $assignments = [];
                foreach (['receptionist', 'manager', 'technician', 'released_by'] as $field) {
                    if (array_key_exists($field, $input)) {
                        $assignments[$field] = $input[$field];
                    }
                }

                if (empty($assignments)) {
                    sendResponse(false, null, 'No assignment fields provided', 400);
                }

                $result = $serviceHandler->updateAssignment((int)$reportId, $assignments);
                sendResponse($result['success'], $result['data'] ?? null, $result['message'] ?? '');
            } catch (Exception $e) {
                error_log('Assign API Error: ' . $e->getMessage());
                sendResponse(false, null, 'Assign failed: ' . $e->getMessage(), 500);
            }

            break;

        case 'delete':
            if ($method !== 'DELETE') {
                sendResponse(false, null, 'Method is incorrect', 405);
            }

            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Report ID required', 400);
            }

            error_log("Service API delete called: id=" . $id . ", method=" . $method);
            $result = $serviceHandler->delete($id);
            // log returned result for debugging
            if (is_array($result) && isset($result['success'])) {
                error_log('Service API delete result: ' . json_encode($result));
                sendResponse($result['success'], null, $result['message'] ?? '');
            } else {
                error_log('Service API delete returned unexpected result: ' . var_export($result, true));
                // if boolean false, send generic error
                if ($result === false) {
                    sendResponse(false, null, 'Failed to delete service report', 500);
                }
                // if true without format (unlikely), treat as success
                sendResponse(true, null, 'Service report deleted successfully');
            }

            break;

        default:
            sendResponse(false, null, 'Invalid action specified', 400);

            break;
    }
} catch (Exception $e) {
    error_log("API Exception: " . $e->getMessage());
    sendResponse(false, null, $e->getMessage(), 500);
}
