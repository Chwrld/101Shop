<?php
require_once __DIR__ . '/auditLogger.php';

class servicePriceHandler {
    private $conn;
    private $table_name = "service_prices";

    private function formatResponse($success, $data = null, $message = '') {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message ?: ($success ? 'Operation successful' : 'Operation failed')
        ];
    }

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllServicePrices() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY service_name";
            $result = $this->conn->query($query);

            $prices = [];
            while ($row = $result->fetch_assoc()) {
                $prices[$row['service_name']] = (float)$row['service_price'];
            }

            return $this->formatResponse(true, $prices);

        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getAllServicePricesFrontend() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY service_name";
            $result = $this->conn->query($query);

            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }

            return $this->formatResponse(true, $services);

        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getAllPaginated($page = 1, $itemsPerPage = 10, $search = '') {
        try {
            $page = max(1, intval($page));
            $itemsPerPage = max(1, intval($itemsPerPage));
            $offset = ($page - 1) * $itemsPerPage;

            $where = '';
            $params = [];
            $types = '';
            if (!empty($search)) {
                $where = " WHERE service_name LIKE ?";
                $like = "%{$search}%";
                $params[] = $like;
                $types .= 's';
            }

            // total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table_name}{$where}";
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

            // data query
            $dataSql = "SELECT * FROM {$this->table_name}{$where} ORDER BY service_name LIMIT ? OFFSET ?";
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

            $services = [];
            while ($row = $result->fetch_assoc()) {
                // normalize price to float
                if (isset($row['service_price'])) {
                    $row['service_price'] = (float)$row['service_price'];
                }
                $services[] = $row;
            }

            $totalPages = (int)ceil($total / $itemsPerPage);
            return $this->formatResponse(true, [
                'services' => $services,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $itemsPerPage
            ]);
        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getServicePriceById($service_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE service_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $service = $result->fetch_assoc();

            if(!$service) {
                return $this->formatResponse(false, null, 'Service not found');
            }

            return $this->formatResponse(true, $service);

        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function addServicePrice($service_name, $service_price) {
        try {
            // check if service name already exists in db 
            $check_query = "SELECT service_id FROM " . $this->table_name . " WHERE service_name = ?";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bind_param("s", $service_name);
            $check_stmt->execute();

            if($check_stmt->get_result()->num_rows > 0) {
                return $this->formatResponse(false, null, 'Service name already exists');
            }

            $query = "INSERT INTO " . $this->table_name . " (service_name, service_price) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sd", $service_name, $service_price);

            if($stmt->execute()) {
                $service_id = $stmt->insert_id;
                
                // Log the activity
                try {
                    $new_data = $this->getServicePriceById($service_id);
                    AuditLogger::logCreate($this->table_name, $service_id, $new_data);
                } catch (Exception $e) {
                    error_log('Audit logging error: ' . $e->getMessage());
                }
                
                return $this->formatResponse(true, ['id' => $service_id], 'Service price added successfully');
            } else {
                return $this->formatResponse(false, null, 'Failed to add service price');
            }

        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function updateServicePrice($service_id, $service_price) {
        try {
            // Get old data before update for audit logging
            $old_data = $this->getServicePriceById($service_id);
            
            $query = "UPDATE " . $this->table_name . " SET service_price = ? WHERE service_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("di", $service_price, $service_id);

            if($stmt->execute()) {
                // Log the activity
                try {
                    $new_data = $this->getServicePriceById($service_id);
                    AuditLogger::logUpdate($this->table_name, $service_id, $old_data, $new_data);
                } catch (Exception $e) {
                    error_log('Audit logging error: ' . $e->getMessage());
                }
                
                return $this->formatResponse(true, null, 'Service price updated successfully');
            } else {
                return $this->formatResponse(false, null, 'Failed to update service price');
            }

        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function deleteServicePrice($service_id) {
        try {
            // Get service data before deletion for archiving
            $service_data = $this->getServicePriceById($service_id);
            
            if (!$service_data) {
                return $this->formatResponse(false, null, 'Service price not found');
            }

            // Start transaction - archive first, then delete
            $this->conn->begin_transaction();

            // Archive the record first
            $archive_success = true;
            $archive_failed_items = [];
            try {
                require_once __DIR__ . '/archiveHandler.php';
                $archiveHandler = new ArchiveHandler($this->conn);
                $archiveResult = $archiveHandler->archiveRecord($this->table_name, $service_id, $service_data, $_SESSION['user_id'] ?? null, 'Service price deleted');
                $archive_success = $archive_success && $archiveResult;
                if (!$archiveResult) { $archive_failed_items[] = $service_id; error_log('Archive logging failed for service price id: ' . $service_id); }
            } catch (Exception $e) {
                error_log('Archive logging error: ' . $e->getMessage());
                $archive_success = false;
                $archive_failed_items[] = ['type' => 'service_prices', 'id' => $service_id, 'error' => $e->getMessage()];
            }

            if (!$archive_success) {
                $this->conn->rollback();
                return $this->formatResponse(false, null, 'Service price deletion aborted: failed to archive. Failed to archive: ' . json_encode($archive_failed_items));
            }

            // Delete the service price after successful archiving
            $query = "DELETE FROM " . $this->table_name . " WHERE service_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $service_id);

            if(!$stmt->execute()) {
                $this->conn->rollback();
                return $this->formatResponse(false, null, 'Failed to delete service price');
            }

            // Commit the transaction
            $this->conn->commit();

            // Log the activity
            try {
                AuditLogger::logDelete($this->table_name, $service_id, $service_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            return $this->formatResponse(true, null, 'Service price archived and deleted successfully');

        } catch (Exception $e) {
            if ($this->conn->in_transaction) {
                $this->conn->rollback();
            }
            return $this->formatResponse(false, null, 'Failed to archive service price: ' . $e->getMessage());
        }
    }

}
?>