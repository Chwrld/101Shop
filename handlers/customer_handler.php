<?php
require_once 'database.php';

class CustomerHandler {
    private $conn;
    private $table_name = "customer";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllCustomers() {
        $query = "SELECT CustomerID, CONCAT(First_name, ' ', Last_name) as FullName, Address, Phone_no FROM " . $this->table_name;
        $result = $this->conn->query($query);
        return $result;
    }

    public function addCustomer($first_name, $last_name, $address, $phone_no) {
        $query = "INSERT INTO " . $this->table_name . " (First_name, Last_name, Address, Phone_no) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $first_name, $last_name, $address, $phone_no);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateCustomer($id, $first_name, $last_name, $address, $phone_no) {
        $query = "UPDATE " . $this->table_name . " SET First_name=?, Last_name=?, Address=?, Phone_no=? WHERE CustomerID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $address, $phone_no, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteCustomer($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE CustomerID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getCustomerById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE CustomerID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?> 