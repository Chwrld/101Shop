<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../backend/handlers/Database.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/update_password_errors.log');

error_log("Received request: " . file_get_contents('php://input'));

// Get raw input
$raw_input = file_get_contents('php://input');
error_log("Raw input received: " . $raw_input);

// Get and decode JSON input
$data = json_decode($raw_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

error_log("Decoded data: " . print_r($data, true));

if (empty($data)) {
    error_log("Empty request data");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Request data is empty'
    ]);
    exit;
}

if (!isset($data['token']) || !isset($data['password'])) {
    $error = "Missing required fields. Token: " . (isset($data['token']) ? 'yes' : 'no') .
        ", Password: " . (isset($data['password']) ? 'yes' : 'no');
    error_log($error);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token and password are required',
        'error_code' => 'MISSING_FIELDS',
        'details' => $error
    ]);
    exit;
}

if (empty($data['token']) || empty($data['password'])) {
    $error = "Empty required fields. Token length: " . strlen($data['token'] ?? '') .
        ", Password length: " . strlen($data['password'] ?? '');
    error_log($error);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token and password cannot be empty',
        'error_code' => 'EMPTY_FIELDS',
        'details' => $error
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    error_log("Starting token verification process");

    // First verify the token is valid and not expired
    $query = "SELECT staff_id, email, username FROM staffs WHERE reset_token = ? AND reset_token_expiry > NOW()";
    error_log("Token verification query: " . $query);
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    error_log("Checking token: " . $data['token']);
    $stmt->bind_param("s", $data['token']);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();
    error_log("Token check result rows: " . $result->num_rows);

    if ($result->num_rows === 0) {
        // Check if token exists but is expired
        $stmt->close();
        $stmt = null;
        $expiry_query = "SELECT reset_token_expiry FROM staffs WHERE reset_token = ?";
        $stmt = $conn->prepare($expiry_query);
        $stmt->bind_param("s", $data['token']);
        $stmt->execute();
        $expiry_result = $stmt->get_result();

        if ($expiry_result->num_rows > 0) {
            $expiry_data = $expiry_result->fetch_assoc();
            error_log("Token found but expired. Expiry was: " . $expiry_data['reset_token_expiry']);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Reset token has expired',
                'error_code' => 'RESET_TOKEN_EXPIRED',
                'details' => $expiry_data['reset_token_expiry']
            ]);
        } else {
            error_log("Token not found in database");
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid reset token',
                'error_code' => 'RESET_TOKEN_INVALID'
            ]);
        }
        exit;
    }

    $staff = $result->fetch_assoc();
    $stmt->close();
    $stmt = null;

    error_log("Found valid token for staff ID: " . $staff['staff_id']);

    // Hash the new password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    error_log("Password hashed successfully. Hash length: " . strlen($hashedPassword));

    // Verify the hash works
    if (!password_verify($data['password'], $hashedPassword)) {
        error_log("Password hash verification failed!");
        throw new Exception("Password hashing failed");
    }
    error_log("Password hash verified successfully");

    // Update the password and clear the reset token
    $update_query = "UPDATE staffs SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE staff_id = ?";
    error_log("Update query: " . $update_query);
    error_log("Hashed password length: " . strlen($hashedPassword));

    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    error_log("Executing password update for staff ID: " . $staff['staff_id']);
    $stmt->bind_param("si", $hashedPassword, $staff['staff_id']);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No rows were updated");
    }

    // Verify the new password was saved correctly
    $verifyStmt = $conn->prepare("SELECT password FROM staffs WHERE staff_id = ?");
    $verifyStmt->bind_param("i", $staff['staff_id']);
    $verifyStmt->execute();
    $newPass = $verifyStmt->get_result()->fetch_assoc();
    error_log("New password hash in DB after update: " . $newPass['password']);

    if (!password_verify($data['password'], $newPass['password'])) {
        error_log("Final password verification failed!");
        throw new Exception("Password update failed verification");
    }
    error_log("Final password verification successful");

    error_log("Password updated successfully for user " . $staff['username']);
    echo json_encode([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
} catch (Exception $e) {
    $errorMessage = $e->getMessage() ?: 'Unexpected server error';
    error_log("Password Reset Error: " . $errorMessage);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to reset password due to a server error.',
        'error_code' => 'PASSWORD_RESET_EXCEPTION',
        'details' => $errorMessage
    ]);
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($verifyStmt) && $verifyStmt instanceof mysqli_stmt) {
        $verifyStmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
