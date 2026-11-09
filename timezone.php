<?php
echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "PHP Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "UTC Time: " . gmdate('Y-m-d H:i:s') . "\n";

// Check MySQL time if you have database access
try {
    $config = include('database/database.php');
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']}",
        $config['username'],
        $config['password']
    );
    $stmt = $pdo->query("SELECT NOW() as mysql_time, @@global.time_zone as mysql_tz, @@session.time_zone as session_tz");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL Time: " . $result['mysql_time'] . "\n";
    echo "MySQL Global Timezone: " . $result['mysql_tz'] . "\n";
    echo "MySQL Session Timezone: " . $result['session_tz'] . "\n";
} catch (Exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "\n";
}
?>