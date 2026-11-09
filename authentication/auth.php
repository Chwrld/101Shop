<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../backend/handlers/authHandler.php';
require_once __DIR__ . '/../backend/handlers/staffsHandler.php';
require_once __DIR__ . '/../backend/handlers/Database.php';

// Set up logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/auth_debug.log');
error_log("=== AUTH DEBUG ===");

// Start session and clean up old messages
session_start();
unset($_SESSION['login_error']);
unset($_SESSION['reset_error']);

// Base URL for redirects
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = "$protocol://$host$scriptDir";

try {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();

    // Password Reset Flow
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate password fields
        if (empty($password) || empty($confirmPassword)) {
            $_SESSION['reset_error'] = 'Please enter both password fields';
            header('Location: ../reset_password.php?token=' . $token);
            exit();
        }

        if ($password !== $confirmPassword) {
            $_SESSION['reset_error'] = 'Passwords do not match';
            header('Location: ../reset_password.php?token=' . $token);
            exit();
        }

        // Process password reset
        $staffsHandler = new StaffsHandler($db);
        $result = $staffsHandler->validateResetToken($token);

        if (!$result['valid']) {
            $_SESSION['reset_error'] = 'Invalid or expired reset token';
            header('Location: ../index.php');
            exit();
        }

        if ($staffsHandler->updatePassword($result['staff_id'], $password)) {
            $staffsHandler->clearResetToken($result['staff_id']);
            $_SESSION['success_message'] = 'Password has been reset successfully. Please login with your new password.';
            header('Location: ../index.php');
            exit();
        }

        $_SESSION['reset_error'] = 'Failed to update password';
        header('Location: ../reset_password.php?token=' . $token);
        exit();
    }

    // Regular Login Flow
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header('Location: ../index.php?error=auth');
        exit();
    }

    $auth = new AuthHandler();
    $auth->cleanExpiredSessions();
    $result = $auth->login($username, $password);

    if ($result['success']) {
        if($result['user_type'] === 'admin') {
            $redirectUrl = "$baseUrl/views/home.php";
        } else {
            $redirectUrl = "$baseUrl/staff/staff_dashboard.php";
        }

        error_log("Redirecting user type '{$result['user_type']}' to: $redirectUrl");
        header("Location: $redirectUrl");
        exit();
    }

    $_SESSION['login_error'] = $result['message'];
    $_SESSION['attempted_username'] = $username;
    header("Location: $baseUrl/index.php");
    exit();
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show more detailed error in development, generic in production
    $isDevelopment = !isset($_ENV['APP_ENV']) || $_ENV['APP_ENV'] !== 'production';
    if ($isDevelopment) {
        $_SESSION['login_error'] = "System error: " . $e->getMessage();
    } else {
        $_SESSION['login_error'] = "System error occurred. Please contact support if this persists.";
    }
    header("Location: $baseUrl/index.php");
    exit();
}
