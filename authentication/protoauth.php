<?php
session_start();
require_once '../database/database.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$config = include('../database/database.php');

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM login_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'username' => $user['username'],
            'login_time' => time()
        ];
        header('Location: ../views/home.php');
        exit();
    } else {
        header('Location: ../index.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: ../index.php?error=2');
    exit();
}