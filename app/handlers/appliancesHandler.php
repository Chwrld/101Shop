<?php
require_once __DIR__ . '/auditLogger.php';

class ApplianceHandler {
    private $conn;
    private $table_name = "appliances";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllAppliances() {
        $query = "SELECT * FROM " . $this->table_name;
        $result = $this->conn->query($query);

        $appliances = [];
        while($row = $result->fetch_assoc()) {
            $appliances[] = $row;
        }

        return $appliances;
    }

    public function getAppliancesByCustomerId($customerId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE customer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $appliances = [];
        while ($row = $result->fetch_assoc()) {
            $appliances[] = $row;
        }
        return $appliances;
    }

    public function addAppliance($customerId, $brand, $product, $modelNo, $serialNo, $dateIn, $warrantyEnd, $category, $status) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (customer_id, brand, product, model_no, serial_no, date_in, warranty_end, category, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Normalize empty values to null so DB fields can be NULL
        $modelNo = $modelNo ? $modelNo : null;
        $serialNo = $serialNo ? $serialNo : null;
        $dateIn = $dateIn ? $dateIn : null;
        $warrantyEnd = $warrantyEnd ? $warrantyEnd : null;

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "issssssss", 
            $customerId, $brand, $product, $modelNo, 
            $serialNo, $dateIn, $warrantyEnd, $category, $status
        );
        
        if ($stmt->execute()) {
            $appliance_id = $stmt->insert_id;
            
            // Log the activity
            try {
                $new_values = [
                    'appliance_id' => $appliance_id,
                    'customer_id' => $customerId,
                    'brand' => $brand,
                    'product' => $product,
                    'model_no' => $modelNo,
                    'serial_no' => $serialNo,
                    'date_in' => $dateIn,
                    'warranty_end' => $warrantyEnd,
                    'category' => $category,
                    'status' => $status
                ];
                AuditLogger::logCreate($this->table_name, $appliance_id, $new_values);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }
            
            return $appliance_id;
        }
        return false;
    }

    public function updateAppliance($applianceId, $brand, $product, $modelNo, $serialNo, $dateIn, $warrantyEnd, $category, $status) {
        // Get old values for audit log
        $old_appliance = $this->getApplianceById($applianceId);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET brand = ?, product = ?, model_no = ?, 
                      serial_no = ?, warranty_end = ?, 
                      category = ?, status = ?, date_in = ?
                  WHERE appliance_id = ?";
        
        // Normalize empty values to null for update
        $modelNo = $modelNo ? $modelNo : null;
        $serialNo = $serialNo ? $serialNo : null;
        $dateIn = $dateIn ? $dateIn : null;
        $warrantyEnd = $warrantyEnd ? $warrantyEnd : null;

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssssssi",
            $brand, $product, $modelNo, $serialNo, 
            $warrantyEnd, $category, $status, $dateIn, $applianceId
        );
        
        if ($stmt->execute()) {
            // Log the activity
            try {
                $old_values = [
                    'appliance_id' => $old_appliance['appliance_id'],
                    'customer_id' => $old_appliance['customer_id'],
                    'brand' => $old_appliance['brand'],
                    'product' => $old_appliance['product'],
                    'model_no' => $old_appliance['model_no'],
                    'serial_no' => $old_appliance['serial_no'],
                    'date_in' => $old_appliance['date_in'],
                    'warranty_end' => $old_appliance['warranty_end'],
                    'category' => $old_appliance['category'],
                    'status' => $old_appliance['status']
                ];
                
                $new_values = [
                    'appliance_id' => $applianceId,
                    'customer_id' => $old_appliance['customer_id'],
                    'brand' => $brand,
                    'product' => $product,
                    'model_no' => $modelNo,
                    'serial_no' => $serialNo,
                    'date_in' => $dateIn,
                    'warranty_end' => $warrantyEnd,
                    'category' => $category,
                    'status' => $status
                ];
                
                AuditLogger::logUpdate($this->table_name, $applianceId, $old_values, $new_values);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }
            
            return true;
        }
        return false;
    }

    public function getApplianceById($applianceId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE appliance_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $applianceId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function deleteAppliance($applianceId) {
        // Get appliance data before deletion for archiving
        $appliance_data = $this->getApplianceById($applianceId);

        // Start transaction - archive first then delete
        $this->conn->begin_transaction();
        try {
            $archive_success = true;
            $archive_failed_items = [];
            try {
                require_once __DIR__ . '/archiveHandler.php';
                $archiveHandler = new ArchiveHandler($this->conn);
                $archiveResult = $archiveHandler->archiveRecord($this->table_name, $applianceId, $appliance_data, $_SESSION['user_id'] ?? null, 'Appliance deleted');
                if (!$archiveResult) {
                    $archive_success = false;
                    $archive_failed_items[] = $applianceId;
                    error_log('Archive logging failed for appliance id: ' . $applianceId);
                }
            } catch (Exception $e) {
                error_log('Archive logging error: ' . $e->getMessage());
                $archive_success = false;
                $archive_failed_items[] = ['type' => 'appliances', 'id' => $applianceId, 'error' => $e->getMessage()];
            }

            if (!$archive_success) {
                $this->conn->rollback();
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE appliance_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $applianceId);

            if ($stmt->execute()) {
                // Commit deletion transaction
                $this->conn->commit();

                // Log the activity
                try {
                    AuditLogger::logDelete($this->table_name, $applianceId, $appliance_data);
                } catch (Exception $e) {
                    error_log('Audit logging error: ' . $e->getMessage());
                }

                return true;
            }
            $this->conn->rollback();
            return false;
        } catch (Exception $e) {
            if ($this->conn->in_transaction) {
                $this->conn->rollback();
            }
            error_log('Error deleting appliance: ' . $e->getMessage());
            return false;
        }
    }
}
?>