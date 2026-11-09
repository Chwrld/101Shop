<?php

require __DIR__ . '/../../backend/handlers/Database.php';
require __DIR__ . '/../../backend/handlers/staffsHandler.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function sendResponse($success, $data = null, $message = '', $httpCode = 200)
{
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
$db = $database->getConnection();

$staffHandler = new StaffsHandler($db);

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'getAllStaffs':
            try {
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';

                $result = $staffHandler->getAllStaffs($page, $itemsPerPage, $search);
                if (!$result || (isset($result['success']) && $result['success'] === false)) {
                    $message = is_array($result) && isset($result['message']) ? $result['message'] : 'Failed to retrieve the staffs';
                    sendResponse(false, null, $message, 500);
                }

                // If handler returns formatted response
                if (isset($result['success']) && isset($result['data'])) {
                    $data = $result['data'];
                    $count = isset($data['staffs']) && is_array($data['staffs']) ? count($data['staffs']) : 0;
                    sendResponse(true, $data, $count . ' staffs found');
                } else {
                    // Backward compatibility: assume $result is the data payload
                    $count = isset($result['staffs']) && is_array($result['staffs']) ? count($result['staffs']) : 0;
                    sendResponse(true, $result, $count . ' staffs found');
                }
            } catch (Exception $e) {
                sendResponse(false, null, $e->getMessage(), 400);
            }
            break;

        case 'getStaffsById':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Staff ID is required', 400);
            }

            $result = $staffHandler->getStaffsById($id);
            if (!$result) {
                sendResponse(false, null, 'Staff not found', 400);
            }
            sendResponse(true, $result, 'Staff retrieved successfully');
            break;

        //used in the service report page as a feature 
        case 'getStaffsByRole':
            $role = $_GET['role'] ?? null;

            $result = $staffHandler->getStaffsbyRole($role);
            if (!$result['success']) {
                sendResponse(false, null, $result['message'], 400);
            }
            sendResponse(true, $result['data'], $result['message']);
            break;

        case 'addStaff':
            $required = [
                'fullname',
                'username',
                'email',
                'password1',
                'password',
                'role',
                'status'
            ];

            if ($input['password1'] !== $input['password']) {
                sendResponse(false, null, "Passwords do not match!", 400);
            }

            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            $result = $staffHandler->addStaffs(
                $input['fullname'],
                $input['username'],
                $input['email'],
                $input['password'],
                $input['role'],
                $input['status']
            );

            if ($result['success'] === false) {
                sendResponse(false, null, $result['message'], 409);
            }

            if ($result == false) {
                sendResponse(false, null, 'Failed to add staff', 500);
            }
            sendResponse($result['success'], $result['data'], $result['message'], $result['success'] ? 201 : 400);
            break;

        case 'updateStaff':
            $required = [
                'staff_id',
                'fullname',
                'username',
                'email',
                'role',
                'status'
            ];

            foreach ($required as $field) {
                if (empty($input[$field])) {
                    sendResponse(false, null, "Missing required field: $field", 400);
                }
            }

            //handle password change if requested
            if (!empty($input['currentPassword']) && !empty($input['password'])) {
                //verify current password
                $verify = $staffHandler->verifyCurrentPassword(
                    $input['staff_id'],
                    $input['currentPassword']
                );

                if (!$verify['verified']) {
                    sendResponse(false, null, 'Current password is incorrect', 401);
                }

                //if verified use the new password
                $password = $input['password'];
            } else {
                //no password change
                $password = null;
            }

            $result = $staffHandler->updateStaffs(
                $input['staff_id'],
                $input['fullname'],
                $input['username'],
                $input['email'],
                $password,
                $input['role'],
                $input['status']
            );

            // Check if result is an array with success key (from formatResponse)
            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === false) {
                    sendResponse(false, null, $result['message'], 409);
                } else {
                    sendResponse(true, null, $result['message']);
                }
            } else {
                // Handle boolean return (legacy)
                if ($result === false) {
                    sendResponse(false, null, 'Failed to update', 500);
                } else {
                    sendResponse(true, null, 'Staff updated successfully');
                }
            }
            break;

        case 'deleteStaff':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendResponse(false, null, 'Staff ID required', 400);
            }

            $result = $staffHandler->deleteStaffs($id);
            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === false) {
                    sendResponse(false, null, $result['message'] ?: 'Failed to delete staff', 500);
                }
                sendResponse(true, null, $result['message'] ?: 'Staff archived successfully');
            } else {
                if ($result === false) {
                    sendResponse(false, null, 'Failed to delete staff', 500);
                }
                sendResponse(true, null, 'Staff archived successfully');
            }
            break;

        case 'verifyCurrentPassword':
            if (empty($input['staff_id']) || empty($input['currentPassword'])) {
                sendResponse(false, null, 'Staff ID and current password are required', 400);
            }

            try {
                $result = $staffHandler->verifyCurrentPassword(
                    $input['staff_id'],
                    $input['currentPassword']
                );

                if ($result['success'] && $result['verified']) {
                    sendResponse(true, null, $result['message']);
                } else {
                    sendResponse(false, null, $result['message'], 401);
                }
            } catch (Exception $e) {
                sendResponse(false, null, $e->getMessage(), 400);
            }

            break;

        case 'checkUsername':
            $username = $_GET['username'] ?? '';

            if (empty($username)) {
                sendResponse(false, null, 'Username required', 400);
            }

            $query = "SELECT StaffID FROM staff WHERE Username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();

            sendResponse(true, [
                'exists' => $stmt->get_result()->num_rows > 0
            ]);

            break;

        case 'addStaffComment':
            if (empty($input['staff_id']) || empty($input['comment_text'])) {
                sendResponse(false, null, 'Staff ID and comment text are required', 400);
            }

            $staffId = intval($input['staff_id']);
            $commentText = trim($input['comment_text']);
            $createdBy = intval($input['created_by'] ?? 1);

            if (empty($commentText)) {
                sendResponse(false, null, 'Comment cannot be empty', 400);
            }

            $query = "INSERT INTO staff_comments (staff_id, comment_text, created_by, created_at) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bind_param('isi', $staffId, $commentText, $createdBy);

            if ($stmt->execute()) {
                sendResponse(true, ['id' => $db->insert_id], 'Comment added successfully');
            } else {
                sendResponse(false, null, 'Failed to add comment', 500);
            }
            break;

        case 'getStaffComments':
            if (empty($_GET['staff_id'])) {
                sendResponse(false, null, 'Staff ID required', 400);
            }

            $staffId = intval($_GET['staff_id']);

            $query = "SELECT sc.id, sc.comment_text, sc.created_at, 
                             s.full_name as author
                      FROM staff_comments sc
                      JOIN staffs s ON sc.created_by = s.staff_id
                      WHERE sc.staff_id = ?
                      ORDER BY sc.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->bind_param('i', $staffId);
            $stmt->execute();
            $result = $stmt->get_result();

            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = [
                    'id' => $row['id'],
                    'text' => $row['comment_text'],
                    'timestamp' => date('M d, Y h:i A', strtotime($row['created_at'])),
                    'author' => $row['author']
                ];
            }

            sendResponse(true, ['comments' => $comments], 'Comments retrieved successfully');
            break;

        case 'deleteStaffComment':
            if (empty($_GET['id'])) {
                sendResponse(false, null, 'Comment ID required', 400);
            }

            $commentId = intval($_GET['id']);

            $query = "DELETE FROM staff_comments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('i', $commentId);

            if ($stmt->execute()) {
                sendResponse(true, null, 'Comment deleted successfully');
            } else {
                sendResponse(false, null, 'Failed to delete comment', 500);
            }
            break;
    }
} catch (Exception $e) {
    sendResponse(false, null, $e->getMessage(), 400);
}
?>