<?php
// Prevent PHP from displaying errors directly
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set timezone to match MySQL server
date_default_timezone_set('Asia/Manila');

// Custom error handler to convert PHP errors to JSON responses
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => 'Please check server logs for details'
    ]);
    exit;
});

// Handle fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal Server Error',
            'error' => 'Please check server logs for details'
        ]);
    }
});

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../backend/handlers/staffsHandler.php';
require_once __DIR__ . '/../../backend/handlers/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function sendResponse($success, $data = null, $message = '', $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'error' => $success ? null : ($message ?: 'An error occurred')
    ]);
    exit;
}

function loadMailConfig()
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $configPath = __DIR__ . '/../../config/mail.php';

    if (!file_exists($configPath)) {
        throw new \Exception('Mail configuration file is missing. Copy config/mail.example.php to config/mail.php and update the SMTP credentials.');
    }

    $loadedConfig = require $configPath;
    if (!is_array($loadedConfig)) {
        throw new \Exception('Mail configuration file must return an array.');
    }

    $requiredSmtpKeys = ['host', 'username', 'password', 'port', 'from_email', 'from_name'];
    foreach ($requiredSmtpKeys as $key) {
        if (empty($loadedConfig['smtp'][$key])) {
            throw new \Exception("Mail configuration missing smtp.{$key}. Please update config/mail.php.");
        }
    }

    if (empty($loadedConfig['options']['base_url'])) {
        throw new \Exception('Mail configuration missing options.base_url.');
    }

    $config = $loadedConfig;
    return $config;
}

function sendResetEmail($email, $token, $config)
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $config['smtp']['port'];
        } catch (\Exception $e) {
            error_log('SMTP Configuration Error: ' . $e->getMessage());
            throw $e;
        }

        // Recipients
        $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mail->addAddress($email);

        // Content
        $baseUrl = rtrim($config['options']['base_url'], '/');
        $resetLink = $baseUrl . "/reset_password.php?token=" . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password. Click the link below to proceed:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

try {
    error_log("Initializing database connection...");
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        error_log("Database connection failed");
        sendResponse(false, null, 'Database connection failed', 500);
    }
    error_log("Database connection successful");

    $staffHandler = new StaffsHandler($db);
    if (!$staffHandler) {
        error_log("Failed to initialize StaffsHandler");
        sendResponse(false, null, 'Staff handler initialization failed', 500);
    }
    error_log("Staff handler initialized successfully");
} catch (\Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    sendResponse(false, null, 'Database initialization failed', 500);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    sendResponse(true);
}

if ($method !== 'POST') {
    sendResponse(false, null, 'Method not allowed', 405);
}

try {
    error_log("Processing reset password request");
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);

    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        sendResponse(false, null, 'Invalid JSON data', 400);
    }

    $email = $input['email'] ?? '';
    error_log("Email received: " . $email);

    if (empty($email)) {
        error_log("Email is empty");
        sendResponse(false, null, 'Email is required', 400);
    }

    // Check if email exists
    error_log("Checking if email exists in database");
    $staff = $staffHandler->getStaffByEmail($email);
    error_log("Staff lookup result: " . ($staff ? "Found" : "Not found"));

    if (!$staff) {
        error_log("Email not found in database: " . $email);
        sendResponse(false, null, 'Email not found', 404);
    }

    // Load configuration
    try {
        $config = loadMailConfig();
    } catch (\Exception $e) {
        error_log('Mail configuration error: ' . $e->getMessage());
        sendResponse(false, null, $e->getMessage(), 500);
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));

    // Calculate expiry time (current time + 1 hour)
    $expiry = date('Y-m-d H:i:s', time() + 3600); // 3600 seconds = 1 hour
    error_log("Setting token expiry to: " . $expiry);

    // Update staff record with reset token
    error_log("Updating reset token for staff: " . $staff['staff_id']);
    $updateResult = $staffHandler->updateResetToken($staff['staff_id'], $token, $expiry);

    if ($updateResult) {
        $emailResult = sendResetEmail($email, $token, $config);
        if ($emailResult === true) {
            sendResponse(true, null, 'Password reset link has been sent to your email');
        } else {
            // If email fails, remove the token
            $staffHandler->clearResetToken($staff['staff_id']);
            if (is_array($emailResult)) {
                sendResponse(false, null, 'Failed to send reset email: ' . $emailResult['message'], 500);
            } else {
                sendResponse(false, null, 'Failed to send reset email', 500);
            }
        }
    } else {
        sendResponse(false, null, 'Failed to process reset request', 500);
    }
} catch (\Exception $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    sendResponse(false, null, 'An error occurred while processing your request', 500);
} finally {
    $database->closeConnection();
}
