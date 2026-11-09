<?php
require_once 'database.php';

class TransactionHandler {
    private $conn;
    private $table_name = "transaction";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createTransaction($report_id) {
        $query = "INSERT INTO " . $this->table_name . " 
                (ReportID, Payment_Status) 
                VALUES (?, 'Pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        
        return $stmt->execute();
    }

    public function updateTransactionOnCompletion($report_id, $total_amount) {
        // Calculate parts total
        $query = "SELECT COALESCE(SUM(pu.Parts_Total), 0) as parts_total
                 FROM partsused pu
                 JOIN servicedetail sd ON pu.ServiceDetailID = sd.ServiceDetailID
                 WHERE sd.ReportID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $parts_total = $result->fetch_assoc()['parts_total'];

        // Calculate labor total
        $query = "SELECT COALESCE(SUM(Labor_Cost), 0) as labor_total
                 FROM servicedetail
                 WHERE ReportID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $labor_total = $result->fetch_assoc()['labor_total'];

        // Update transaction
        $query = "UPDATE " . $this->table_name . "
                 SET Parts_Total = ?,
                     Labor_Total = ?,
                     Total_Amount = ?
                 WHERE ReportID = ?";
        $stmt = $this->conn->prepare($query);
        $total = $parts_total + $labor_total;
        $stmt->bind_param("dddi", $parts_total, $labor_total, $total, $report_id);
        
        return $stmt->execute();
    }

    public function updatePaymentStatus($transaction_id, $status, $staff_id) {
        $query = "UPDATE " . $this->table_name . "
                 SET Payment_Status = ?,
                     Payment_Date = CASE WHEN ? = 'Paid' THEN CURRENT_DATE ELSE NULL END,
                     Received_By = ?
                 WHERE TransactionID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $status, $status, $staff_id, $transaction_id);
        
        return $stmt->execute();
    }

    public function getTransactionById($id) {
        $query = "SELECT t.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName,
                        s.Fullname as StaffName
                 FROM " . $this->table_name . " t
                 JOIN servicereport sr ON t.ReportID = sr.ReportID
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 LEFT JOIN staff s ON t.Received_By = s.StaffID
                 WHERE t.TransactionID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllTransactions() {
        $query = "SELECT t.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName,
                        s.Fullname as StaffName
                 FROM " . $this->table_name . " t
                 JOIN servicereport sr ON t.ReportID = sr.ReportID
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 LEFT JOIN staff s ON t.Received_By = s.StaffID
                 ORDER BY t.TransactionID DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    public function getPendingTransactions() {
        $query = "SELECT t.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName
                 FROM " . $this->table_name . " t
                 JOIN servicereport sr ON t.ReportID = sr.ReportID
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 WHERE t.Payment_Status = 'Pending'
                 ORDER BY t.TransactionID DESC";
        $result = $this->conn->query($query);
        return $result;
    }
}
?> 