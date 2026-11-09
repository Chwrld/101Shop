<?php 
class Database {
    private $mysqli;

    public function __construct() {
        $config = include(__DIR__.'/../../database/database.php');

        // Log connection attempt for debugging
        error_log("Attempting DB connection to: " . $config['host']);
        
        // Set mysqli error reporting
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $this->mysqli = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );
            
            // Set charset to UTF-8
            $this->mysqli->set_charset("utf8mb4");
            
            error_log("Database connection successful");
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your database configuration.");
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