<?php 
class Database {
    private $mysqli;

    public function __construct() {
        $config = include(__DIR__ . '/../config/database.php');

        // Set mysqli error reporting
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->mysqli = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['dbname'],
                (int) ($config['port'] ?? 3306)
            );

            // Set charset to UTF-8
            $this->mysqli->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new RuntimeException("Database connection failed.");
        }
    }

    public function getConnection() {
        return $this->mysqli;
    }

    public function closeConnection() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}
?>