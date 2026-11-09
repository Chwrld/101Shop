<?php
/**
 * Migration: Add customer_id and appliance_id foreign keys to service_reports table
 * Purpose: Enable proper database synchronization between staff and admin panels
 */

require_once __DIR__ . '/../database.php';

try {
    // Get the database connection
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'repairsystem';
    
    $db = new mysqli($host, $user, $password, $dbname);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    // Check if columns already exist
    $checkQuery = "SHOW COLUMNS FROM service_reports LIKE 'customer_id'";
    $result = $db->query($checkQuery);
    
    if ($result && $result->num_rows === 0) {
        // Column doesn't exist, add it
        $addCustomerIdQuery = "ALTER TABLE service_reports 
                              ADD COLUMN customer_id INT NULL AFTER customer_name,
                              ADD CONSTRAINT fk_service_reports_customer 
                              FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL";
        
        if (!$db->query($addCustomerIdQuery)) {
            throw new Exception("Failed to add customer_id column: " . $db->error);
        }
        echo "[SUCCESS] Added customer_id column to service_reports table\n";
    } else {
        echo "[INFO] customer_id column already exists\n";
    }
    
    // Check if appliance_id column exists
    $checkQuery = "SHOW COLUMNS FROM service_reports LIKE 'appliance_id'";
    $result = $db->query($checkQuery);
    
    if ($result && $result->num_rows === 0) {
        // Column doesn't exist, add it
        $addApplianceIdQuery = "ALTER TABLE service_reports 
                               ADD COLUMN appliance_id INT NULL AFTER appliance_name,
                               ADD CONSTRAINT fk_service_reports_appliance 
                               FOREIGN KEY (appliance_id) REFERENCES appliances(appliance_id) ON DELETE SET NULL";
        
        if (!$db->query($addApplianceIdQuery)) {
            throw new Exception("Failed to add appliance_id column: " . $db->error);
        }
        echo "[SUCCESS] Added appliance_id column to service_reports table\n";
    } else {
        echo "[INFO] appliance_id column already exists\n";
    }
    
    $db->close();
    echo "[MIGRATION COMPLETE] Database schema updated successfully\n";
    
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
