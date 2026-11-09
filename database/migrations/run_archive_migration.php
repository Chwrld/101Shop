<?php
/**
 * Database Migration - Create Archive Records Table
 * Run this once to create the archive_records table in production
 */

require __DIR__ . '/../../backend/handlers/Database.php';

header("Content-Type: application/json");

// Only allow execution via direct access or API call
$database = new Database();
$db = $database->getConnection();

if ($db->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $db->connect_error
    ]);
    exit;
}

// Check if table already exists
$checkTable = "SHOW TABLES LIKE 'archive_records'";
$result = $db->query($checkTable);

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'archive_records table already exists',
        'action' => 'none'
    ]);
    exit;
}

// Create the archive_records table
$createTableSQL = "CREATE TABLE IF NOT EXISTS `archive_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `deleted_data` longtext NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_name` (`table_name`),
  KEY `record_id` (`record_id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($db->query($createTableSQL) === TRUE) {
    // Create additional indexes
    $indexes = [
        "CREATE INDEX idx_table_record ON archive_records(table_name, record_id)",
        "CREATE INDEX idx_deleted_at_desc ON archive_records(deleted_at DESC)",
        "CREATE INDEX idx_deleted_by ON archive_records(deleted_by)"
    ];
    
    $indexResults = [];
    foreach ($indexes as $indexSQL) {
        try {
            $db->query($indexSQL);
            $indexResults[] = 'Created';
        } catch (Exception $e) {
            $indexResults[] = 'Skipped (may already exist)';
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'archive_records table created successfully',
        'action' => 'created',
        'indexes' => $indexResults
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating table: ' . $db->error
    ]);
}

$db->close();
?>
