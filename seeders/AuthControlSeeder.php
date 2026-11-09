<?php
require_once __DIR__ . '/../database/database.php';

class AuthControlSeeder {
    private $pdo;

    public function __construct() {
        $config = include(__DIR__ . '/../database/database.php');

        try {
            $this->pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']}",
                $config['username'],
                $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function run() {
        $this->seedAuthUsers();

        echo "users inserted in the table\n";
    }

    private function createTables() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                UserID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Username varchar(50) UNIQUE NOT NULL,
                Password varchat(255) NOT NULL,
                Email varchar(100) DEFAULT NULL,
                UserType enum('admin', 'staff') NOT NULL,
                FullName varchar(100) NOT NULL,
                Status enum('active', 'inactive') DEFAULT 'active',
                DateCreated datetime DEFAULT CURRENT_TIMESTAMP,
                LastLogin datetime DEFAULT NULL,
            );
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                SessionID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                UserID int NOT NULL,
                SessionToken varchar(64) UNIQUE NOT NULL,
                CreatedAt timestamp DEFAULT CURRENT_TIMESTAMP,
                ExpiresAt timestamp NOT NULL,
                IsActive boolean DEFAULT TRUE,
                IPAddress varchar(45) DEFAULT NULL,
                UserAgent text DEFAULT NULL,
                FOREIGN KEY (UserID) REFERENCES 'users' (UserID) ON DELETE CASCADE
            );
        ");

        echo "tables created\n";
    }

    private function seedAuthUsers() {
        $accounts = [
            ['admin', 'admin123', 'admin@example.com', 'admin', 'adminfullname', 'active'],
            ['staff', 'staff123', 'staff@example.com', 'staff', 'stafffullname', 'active']
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO users (Username, Password, Email, UserType, FullName, Status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

      $inserted = 0;
      foreach ($accounts as $account) {
        try {
            $stmt->execute([
                $account[0],
                password_hash($account[1], PASSWORD_DEFAULT),
                $account[2],
                $account[3],
                $account[4],
                $account[5],
            ]);

            if($stmt->rowCount() > 0) {
                $inserted++;
                echo "User {$account[0]} inserted\n";
            } else {
                echo "User {$account[0]} already exists\n";
            }
        } catch (PDOException $e) {
            echo "Error inserting user '{$account[0]}': " , $e->getMessage() . "\n";
        }
      }
      echo "$inserted users insterted\n";
    }
}

$seeder = new AuthControlSeeder();
$seeder->run();
?>