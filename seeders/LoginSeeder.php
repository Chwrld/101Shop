<?php
require_once __DIR__ . '/../database/database.php';

class LoginSeeder {
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
        $this->createLoginTable();
        $this->seedLoginAccounts();

        echo "Login accounts seeded successfully!\n";
        echo "Test credentials created:\n";
        echo "admin123 / admin123\n";
        echo "sampleadmin123 / admin321\n";
        echo "prototype_admin123 / 123admin\n";
    }

    private function createLoginTable() {
        $sql = "CREATE TABLE IF NOT EXISTS login_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $this->pdo->exec($sql);
    }

    private function seedLoginAccounts() {
        $accounts = [
            ['admin123', 'admin123'],
            ['sampleadmin123', 'admin321'],
            ['prototype_admin123', '123admin']
        ];

        $this->pdo->exec("TRUNCATE TABLE login_users");

        $stmt = $this->pdo->prepare(
            "INSERT INTO login_users (username, password) VALUES (?, ?)"
        );

        foreach ($accounts as $account) {
            $stmt->execute([
                $account[0],
                password_hash($account[1], PASSWORD_DEFAULT)   
            ]);
        }
    }
}

$seeder = new LoginSeeder();
$seeder->run();
?>