<?php
require_once 'database.php';

class StaffHandler {
    private $conn;
    private $table_name = "staff";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllStaff() {
        $query = "SELECT StaffID, Fullname, Role FROM " . $this->table_name;
        $result = $this->conn->query($query);
        return $result;
    }

    public function getTechnicians() {
        $query = "SELECT StaffID, Fullname 
                FROM " . $this->table_name . " 
                WHERE Role = 'Technician'";
        $result = $this->conn->query($query);
        return $result;
    }

    public function addStaff($fullname, $role) {
        $query = "INSERT INTO " . $this->table_name . " (Fullname, Role) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fullname, $role);
        
        if($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function updateStaff($id, $fullname, $role) {
        $query = "UPDATE " . $this->table_name . " SET Fullname=?, Role=? WHERE StaffID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $fullname, $role, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteStaff($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE StaffID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getStaffById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE StaffID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?> 