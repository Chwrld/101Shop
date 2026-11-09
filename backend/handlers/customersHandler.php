<?php
require_once __DIR__ . '/auditLogger.php';

class CustomerHandler
{
    private $conn;
    private $table_name = "customers";

    private function formatResponse($success, $data = null, $message = '')
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message ?: ($success ? 'Operation successful' : 'Operation failed')
        ];
    }

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getTotalCustomers()
    {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return (int)$row['total'];
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting total customers: ' . $e->getMessage());
            return 0;
        }
    }

    public function getAllCustomers()
    {
        try {
            $query = "SELECT 
                    customer_id, 
                    CONCAT(first_name, ' ', last_name) as FullName, 
                    address, 
                    phone_no 
                    FROM " . $this->table_name;

            $result = $this->conn->query($query);

            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            return $this->formatResponse(true, $customers);
        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }

        return $customers;
    }

    public function getAllCustomersPaginated($page = 1, $itemsPerPage = 10, $search = '')
    {
        try {
            $page = max(1, intval($page));
            $itemsPerPage = max(1, intval($itemsPerPage));
            $offset = ($page - 1) * $itemsPerPage;

            $baseFrom = " FROM {$this->table_name} ";
            $where = '';
            $params = [];
            $types = '';
            if (!empty($search)) {
                $where = " WHERE first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name,' ',last_name) LIKE ? OR address LIKE ? OR phone_no LIKE ?";
                $like = "%{$search}%";
                $params = [ $like, $like, $like, $like, $like ];
                $types = 'sssss';
            }

            $countSql = "SELECT COUNT(*) as total" . $baseFrom . $where;
            if (!empty($search)) {
                $stmt = $this->conn->prepare($countSql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $countRes = $stmt->get_result();
            } else {
                $countRes = $this->conn->query($countSql);
            }
            $totalRow = $countRes->fetch_assoc();
            $total = intval($totalRow['total'] ?? 0);

            $dataSql = "SELECT customer_id, CONCAT(first_name,' ',last_name) as FullName, address, phone_no" . $baseFrom . $where . " ORDER BY customer_id DESC LIMIT ? OFFSET ?";
            if (!empty($search)) {
                $stmt = $this->conn->prepare($dataSql);
                $types2 = $types . 'ii';
                $params2 = array_merge($params, [ $itemsPerPage, $offset ]);
                $stmt->bind_param($types2, ...$params2);
            } else {
                $stmt = $this->conn->prepare($dataSql);
                $stmt->bind_param('ii', $itemsPerPage, $offset);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            $totalPages = (int)ceil($total / $itemsPerPage);
            return [
                'customers' => $customers,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $itemsPerPage
            ];
        } catch (Exception $e) {
            error_log('Error in getAllCustomersPaginated: ' . $e->getMessage());
            return false;
        }
    }

    public function getCustomerById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE customer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function addCustomer($first_name, $last_name, $address, $phone_no)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, address, phone_no) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $first_name, $last_name, $address, $phone_no);

        if ($stmt->execute()) {
            $customer_id = $stmt->insert_id;
            
            // Log the activity
            try {
                $new_values = [
                    'customer_id' => $customer_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'address' => $address,
                    'phone_no' => $phone_no
                ];
                AuditLogger::logCreate($this->table_name, $customer_id, $new_values);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }
            
            return $customer_id;
        }
        return false;
    }

    public function updateCustomer($id, $first_name, $last_name, $address, $phone_no)
    {
        // Get old values for audit log
        $old_customer = $this->getCustomerById($id);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = ?, last_name = ?, address = ?, phone_no = ?
                  WHERE customer_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $address, $phone_no, $id);
        
        if ($stmt->execute()) {
            // Log the activity
            $old_values = [
                'customer_id' => $old_customer['customer_id'],
                'first_name' => $old_customer['first_name'],
                'last_name' => $old_customer['last_name'],
                'address' => $old_customer['address'],
                'phone_no' => $old_customer['phone_no']
            ];
            
            $new_values = [
                'customer_id' => $id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address' => $address,
                'phone_no' => $phone_no
            ];
            
            try {
                AuditLogger::logUpdate($this->table_name, $id, $old_values, $new_values);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }
            return true;
        }
        return false;
    }

    public function deleteCustomer($id)
    {
        // Get customer data and appliances before deletion for archiving
        $customer_data = $this->getCustomerById($id);

        $appliances_query = "SELECT * FROM appliances WHERE customer_id = ?";
        $appliances_stmt = $this->conn->prepare($appliances_query);
        $appliances_stmt->bind_param("i", $id);
        $appliances_stmt->execute();
        $appliances_result = $appliances_stmt->get_result();

        $appliances = [];
        while ($row = $appliances_result->fetch_assoc()) {
            $appliances[] = $row;
        }

        // Instantiate archive handler
        require_once __DIR__ . '/archiveHandler.php';
        $archiveHandler = new ArchiveHandler($this->conn);

        // Start transaction to ensure we only delete if archiving succeeds
        $this->conn->begin_transaction();
        try {
            $archive_success = true;
            $archive_failed_items = [];

            // Archive the customer record first
            $archiveResult = $archiveHandler->archiveRecord($this->table_name, $id, $customer_data, $_SESSION['user_id'] ?? null, 'Customer and all appliances deleted');
            $archive_success = $archive_success && $archiveResult;
            if (!$archiveResult) {
                $archive_failed_items[] = ['type' => 'customer', 'id' => $id];
                error_log('Archive logging failed for customer id: ' . $id);
            }

            // Archive each appliance record
            foreach ($appliances as $appliance) {
                $apRes = $archiveHandler->archiveRecord('appliances', $appliance['appliance_id'], $appliance, $_SESSION['user_id'] ?? null, 'Appliance deleted with customer');
                if (!$apRes) {
                    error_log('Archive logging failed for appliance id: ' . $appliance['appliance_id']);
                    $archive_success = false;
                        $archive_failed_items[] = ['type' => 'appliances', 'id' => $appliance['appliance_id'], 'error' => 'Failed to archive appliance'];
                }
            }

            if (!$archive_success) {
                // If archiving failed, rollback transaction and return error
                $this->conn->rollback();
                $message = 'Customer and appliances archival failed. Failed to archive: ' . json_encode($archive_failed_items);
                return $this->formatResponse(false, null, $message);
            }

            // If archiving succeeded, proceed to delete appliances and customer
            $delete_appliances_query = "DELETE FROM appliances WHERE customer_id = ?";
            $delete_appliances_stmt = $this->conn->prepare($delete_appliances_query);
            $delete_appliances_stmt->bind_param("i", $id);
            $delete_appliances_stmt->execute();

            // Now delete the customer
            $delete_customer_query = "DELETE FROM " . $this->table_name . " WHERE customer_id = ?";
            $delete_customer_stmt = $this->conn->prepare($delete_customer_query);
            $delete_customer_stmt->bind_param("i", $id);
            $delete_customer_stmt->execute();

            // Commit the transaction
            $this->conn->commit();
            
            // Log the customer deletion activity
            try {
                AuditLogger::logDelete($this->table_name, $id, $customer_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            return $this->formatResponse(true, null, 'Customer deleted and archived successfully');
            
        } catch (Exception $e) {
            // Rollback the transaction on error
            $this->conn->rollback();
            error_log('Error deleting customer: ' . $e->getMessage());
            return false;
        }
    }
}
?>