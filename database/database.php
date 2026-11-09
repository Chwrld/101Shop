<?php

//Supports PDO and mysqli

// Check for Render.com's DATABASE_URL format (postgresql://user:pass@host:port/dbname)
// or MySQL format (mysql://user:pass@host:port/dbname)
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl && (strpos($databaseUrl, 'mysql://') === 0)) {
    // Parse MySQL URL from Render.com
    $url = parse_url($databaseUrl);
    $config = [
        'host' => $url['host'] ?? 'localhost',
        'username' => $url['user'] ?? 'root',
        'password' => $url['pass'] ?? '',
        'dbname' => ltrim($url['path'] ?? '/repairsystem', '/'),
        'port' => $url['port'] ?? 3306,
    ];
} else {
    // Use individual environment variables or fallback to local development values
    $config = [
        'host' => getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'host.docker.internal',
        'username' => getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
        'dbname' => getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'repairsystem',
        'port' => getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306,
    ];
}

// Log configuration (without password) for debugging
error_log("Database config - Host: {$config['host']}, User: {$config['username']}, DB: {$config['dbname']}");

//for PDO and mysqli
return $config;
