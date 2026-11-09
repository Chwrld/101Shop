<?php
require_once 'database.php';

class ApplianceHandler {
    private $conn;
    private $table_name = "appliances";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAppliancesByCustomerId($customer_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE CustomerID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function addAppliance($customer_id, $brand, $product, $model_no, $serial_no, $warranty_end, $category, $status) {
        $query = "INSERT INTO " . $this->table_name . " 
                (CustomerID, Brand, Product, Model_No, Serial_No, Warranty_end, Category, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        // normalize empty values to null
        $model_no = $model_no ? $model_no : null;
        $serial_no = $serial_no ? $serial_no : null;
        $warranty_end = $warranty_end ? $warranty_end : null;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isssssss", $customer_id, $brand, $product, $model_no, $serial_no, $warranty_end, $category, $status);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateAppliance($id, $brand, $product, $model_no, $serial_no, $warranty_end, $category, $status) {
        $query = "UPDATE " . $this->table_name . " 
                SET Brand=?, Product=?, Model_No=?, Serial_No=?, Warranty_end=?, Category=?, Status=? 
                WHERE ApplianceID=?";
        // normalize empty values to null for update
        $model_no = $model_no ? $model_no : null;
        $serial_no = $serial_no ? $serial_no : null;
        $warranty_end = $warranty_end ? $warranty_end : null;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssi", $brand, $product, $model_no, $serial_no, $warranty_end, $category, $status, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteAppliance($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ApplianceID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 