<?php
require_once __DIR__ . '/../bootstrap.php';

session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';

$auth = new AuthHandler();
$auth->logout();

header("Location: ../index.php");
exit();
?>