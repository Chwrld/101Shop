<?php
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../app/handlers/Database.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Get raw input
$raw_input = file_get_contents('php://input');

// Get and decode JSON input
$data = json_decode($raw_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Request data is empty'
    ]);
    exit;
}

if (!isset($data['token']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token and password are required',
        'error_code' => 'MISSING_FIELDS'
    ]);
    exit;
}

if (empty($data['token']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token and password cannot be empty',
        'error_code' => 'EMPTY_FIELDS'
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // First verify the token is valid and not expired
    $query = "SELECT staff_id, email, username FROM staffs WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("s", $data['token']);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result = $stmt->get_result();

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
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Reset token has expired',
                'error_code' => 'RESET_TOKEN_EXPIRED'
            ]);
        } else {
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

    // Hash the new password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Verify the hash works
    if (!password_verify($data['password'], $hashedPassword)) {
        throw new Exception("Password hashing failed");
    }

    // Update the password and clear the reset token
    $update_query = "UPDATE staffs SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE staff_id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

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

    if (!password_verify($data['password'], $newPass['password'])) {
        throw new Exception("Password update failed verification");
    }

    error_log('Password reset completed for staff_id=' . (int) $staff['staff_id']);
    echo json_encode([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
} catch (Throwable $e) {
    error_log("Password Reset Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to reset password due to a server error.',
        'error_code' => 'PASSWORD_RESET_EXCEPTION'
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
