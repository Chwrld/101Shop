<?php
$appConfig = require __DIR__ . '/config/app.php';

date_default_timezone_set($appConfig['timezone']);

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure a session is started for every request that includes bootstrap
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Send headers to prevent caching of authenticated pages by the browser.
// This helps ensure that after logout the back button won't show protected pages from cache.
// These headers must be sent before any output — `bootstrap.php` should be included at the top of pages.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
?>