<?php
require_once 'database.php';

class PartsHandler {
    private $conn;
    private $table_name = "parts";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllParts() {
        $query = "SELECT * FROM " . $this->table_name;
        $result = $this->conn->query($query);
        return $result;
    }

    public function addPart($part_no, $description, $price) {
        $query = "INSERT INTO " . $this->table_name . " (Part_No, Description, Price) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssd", $part_no, $description, $price);
        
        if($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function updatePart($id, $part_no, $description, $price) {
        $query = "UPDATE " . $this->table_name . " SET Part_No=?, Description=?, Price=? WHERE PartID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssdi", $part_no, $description, $price, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deletePart($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE PartID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getPartById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PartID=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function addPartToServiceDetail($service_detail_id, $part_id, $quantity) {
        // Calculate parts total
        $part_result = $this->getPartById($part_id);
        $part = $part_result->fetch_assoc();
        $parts_total = $part['Price'] * $quantity;

        // Insert into partsused table
        $query = "INSERT INTO partsused (ServiceDetailID, PartID, Quantity, Parts_Total) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiid", $service_detail_id, $part_id, $quantity, $parts_total);
        
        if($stmt->execute()) {
            // Update transaction parts total
            $this->updateTransactionPartsTotal($service_detail_id);
            return true;
        }
        return false;
    }

    private function updateTransactionPartsTotal($service_detail_id) {
        // Get ReportID from ServiceDetail
        $query = "SELECT ReportID FROM servicedetail WHERE ServiceDetailID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $service_detail_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service_detail = $result->fetch_assoc();
        
        if($service_detail) {
            // Calculate total parts cost for this report
            $query = "SELECT SUM(pu.Parts_Total) as total_parts
                     FROM partsused pu
                     JOIN servicedetail sd ON pu.ServiceDetailID = sd.ServiceDetailID
                     WHERE sd.ReportID = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $service_detail['ReportID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->fetch_assoc();
            
            // Update transaction
            $query = "UPDATE transaction 
                     SET Parts_Total = ?, 
                         Total_Amount = Parts_Total + Labor_Total
                     WHERE ReportID = ?";
            $stmt = $this->conn->prepare($query);
            $parts_total = $total['total_parts'] ?? 0;
            $stmt->bind_param("di", $parts_total, $service_detail['ReportID']);
            $stmt->execute();
        }
    }
}
?> 