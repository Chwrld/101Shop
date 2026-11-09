<?php
require_once 'database.php';
require_once 'transaction_handler.php';
require_once 'parts_handler.php';

class ServiceReportHandler {
    private $conn;
    private $table_name = "servicereport";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllServiceReports() {
        $query = "SELECT sr.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName,
                        s.Fullname as TechnicianName
                 FROM " . $this->table_name . " sr
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 LEFT JOIN staff s ON sr.StaffID = s.StaffID
                 ORDER BY sr.ReportID DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    public function createServiceReport($customer_id, $appliance_id, $staff_id, $date_in, $service_type, $complaint, $parts = [], $quantities = []) {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert service report
            $query = "INSERT INTO " . $this->table_name . " 
                    (CustomerID, ApplianceID, StaffID, Date_In, Service_type, Status, Complaint) 
                    VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iiisss", $customer_id, $appliance_id, $staff_id, $date_in, $service_type, $complaint);
            
            if($stmt->execute()) {
                $report_id = $this->conn->insert_id;
                
                // Create initial service detail
                $query = "INSERT INTO servicedetail (ReportID, StaffID, Service_Type) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("iis", $report_id, $staff_id, $service_type);
                
                if($stmt->execute()) {
                    $service_detail_id = $this->conn->insert_id;
                    
                    // Add parts if provided
                    $parts_handler = new PartsHandler();
                    for($i = 0; $i < count($parts); $i++) {
                        if(!empty($parts[$i]) && !empty($quantities[$i])) {
                            if(!$parts_handler->addPartToServiceDetail($service_detail_id, $parts[$i], $quantities[$i])) {
                                $this->conn->rollback();
                                return false;
                            }
                        }
                    }
                }
                
                // Create initial transaction record
                $transaction_handler = new TransactionHandler();
                $transaction_handler->createTransaction($report_id);
                
                $this->conn->commit();
                return $report_id;
            }
            
            $this->conn->rollback();
            return false;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function updateServiceReport($report_id, $status, $date_repaired = null, $date_delivered = null, $cost = null) {
        $query = "UPDATE " . $this->table_name . " 
                 SET Status = ?, 
                     Date_Repaired = ?, 
                     Date_Delivered = ?,
                     Cost = ?
                 WHERE ReportID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssdi", $status, $date_repaired, $date_delivered, $cost, $report_id);
        
        if($stmt->execute()) {
            // If status is completed, update transaction
            if($status == 'Completed') {
                $transaction_handler = new TransactionHandler();
                $transaction_handler->updateTransactionOnCompletion($report_id, $cost);
            }
            return true;
        }
        return false;
    }

    public function getServiceReportById($id) {
        $query = "SELECT sr.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName,
                        s.Fullname as TechnicianName
                 FROM " . $this->table_name . " sr
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 LEFT JOIN staff s ON sr.StaffID = s.StaffID
                 WHERE sr.ReportID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getServiceReportsByStaff($staff_id) {
        $query = "SELECT sr.*, 
                        CONCAT(c.First_name, ' ', c.Last_name) as CustomerName,
                        CONCAT(a.Brand, ' ', a.Product) as ApplianceName
                 FROM " . $this->table_name . " sr
                 LEFT JOIN customer c ON sr.CustomerID = c.CustomerID
                 LEFT JOIN appliances a ON sr.ApplianceID = a.ApplianceID
                 WHERE sr.StaffID = ?
                 ORDER BY sr.Date_In DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?> 