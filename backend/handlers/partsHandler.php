<?php
require_once __DIR__ . '/auditLogger.php';

class PartsHandler
{
    private $conn;
    private $table_name = "parts";

    private function formatResponse($success, $data = null, $message = '')
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message ?: ($success ? 'Operation successful' : 'Operation failed')
        ];
    }

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllParts()
    {
        try {
            $query = "SELECT * FROM " . $this->table_name;

            $result = $this->conn->query($query);

            $parts = [];
            while ($row = $result->fetch_assoc()) {
                $parts[] = $row;
            }

            return $this->formatResponse(true, $parts);
        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getAllPartsPaginated($page = 1, $itemsPerPage = 10, $search = '')
    {
        try {
            $page = max(1, intval($page));
            $itemsPerPage = max(1, intval($itemsPerPage));
            $offset = ($page - 1) * $itemsPerPage;

            $where = '';
            $params = [];
            $types = '';
            if (!empty($search)) {
                $where = " WHERE part_no LIKE ? OR description LIKE ?";
                $like = "%{$search}%";
                $params = [$like, $like];
                $types = 'ss';
            }

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

            $dataSql = "SELECT * FROM {$this->table_name}{$where} ORDER BY part_id DESC LIMIT ? OFFSET ?";
            if (!empty($search)) {
                $stmt = $this->conn->prepare($dataSql);
                $types2 = $types . 'ii';
                $params2 = array_merge($params, [$itemsPerPage, $offset]);
                $stmt->bind_param($types2, ...$params2);
            } else {
                $stmt = $this->conn->prepare($dataSql);
                $stmt->bind_param('ii', $itemsPerPage, $offset);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $parts = [];
            while ($row = $result->fetch_assoc()) {
                // normalize numeric fields
                if (isset($row['price'])) $row['price'] = (float)$row['price'];
                if (isset($row['quantity_stock'])) $row['quantity_stock'] = (int)$row['quantity_stock'];
                $parts[] = $row;
            }

            $totalPages = (int)ceil($total / $itemsPerPage);
            return $this->formatResponse(true, [
                'parts' => $parts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $itemsPerPage
            ]);
        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getLowStockParts($limit = 5)
    {
        try {
            $query = "SELECT part_no, description, quantity_stock, price 
                     FROM " . $this->table_name . " 
                     WHERE quantity_stock <= 5 
                     ORDER BY quantity_stock ASC 
                     LIMIT ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $parts = [];
            while ($row = $result->fetch_assoc()) {
                $row['price'] = (float)$row['price'];
                $row['quantity_stock'] = (int)$row['quantity_stock'];
                $parts[] = $row;
            }

            return $parts;
        } catch (Exception $e) {
            error_log('Error getting low stock parts: ' . $e->getMessage());
            return [];
        }
    }

    public function getPartsById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE part_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function addParts($parts_no, $description, $price, $quantity_stock)
    {
        $query = "INSERT INTO " . $this->table_name . "
                 (part_no, description, price, quantity_stock) VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssdi', $parts_no, $description, $price, $quantity_stock);

        if ($stmt->execute()) {
            $part_id = $stmt->insert_id;

            // Log the activity
            try {
                $new_data = $this->getPartById($part_id);
                AuditLogger::logCreate($this->table_name, $part_id, $new_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            return $part_id;
        }
        return false;
    }

    public function updateParts($id, $parts_no, $description, $price, $quantity_stock)
    {
        // Get old data before update for audit logging
        $old_data = $this->getPartById($id);

        $query = "UPDATE " . $this->table_name . " 
                  SET part_no = ?, description = ?, price = ?, quantity_stock = ? 
                  WHERE part_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssdii', $parts_no, $description, $price, $quantity_stock, $id);

        if ($stmt->execute()) {
            // Log the activity
            try {
                $new_data = $this->getPartById($id);
                AuditLogger::logUpdate($this->table_name, $id, $old_data, $new_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            return true;
        }
        return false;
    }

    public function deductQuantity($partId, $quantity)
    {
        try {
            // Start transaction
            $this->conn->begin_transaction();

            // Get current quantity
            $query = "SELECT part_id, quantity_stock FROM " . $this->table_name . " WHERE part_id = ? FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $partId);
            $stmt->execute();
            $result = $stmt->get_result();
            $part = $result->fetch_assoc();

            if (!$part) {
                throw new Exception("Part not found");
            }

            if ($part['quantity_stock'] < $quantity) {
                throw new Exception("Insufficient quantity in stock");
            }

            // Update quantity
            $new_quantity = $part['quantity_stock'] - $quantity;
            $update_query = "UPDATE " . $this->table_name . " SET quantity_stock = ? WHERE part_id = ?";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bind_param('ii', $new_quantity, $part['part_id']);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Failed to update quantity");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getCurrentQuantity($part_no)
    {
        $query = "SELECT quantity_stock FROM " . $this->table_name . " WHERE part_no = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $part_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['quantity_stock'] : 0;
    }

    public function deleteParts($id)
    {
        try {
            // Get part data before deletion for archiving
            $part_data = $this->getPartById($id);

            if (!$part_data) {
                return $this->formatResponse(false, null, 'Part not found');
            }

            // Start transaction - we will archive first, then delete
            $this->conn->begin_transaction();

            // Archive the record first
            $archive_success = true;
            $archive_failed_items = [];
            try {
                require_once __DIR__ . '/archiveHandler.php';
                $archiveHandler = new ArchiveHandler($this->conn);
                $archiveResult = $archiveHandler->archiveRecord($this->table_name, $id, $part_data, $_SESSION['user_id'] ?? null, 'Part deleted');
                $archive_success = $archive_success && $archiveResult;
                if (!$archiveResult) {
                    $archive_failed_items[] = $id;
                    error_log('Archive logging failed for part id: ' . $id);
                }
            } catch (Exception $e) {
                error_log('Archive logging error: ' . $e->getMessage());
                $archive_success = false;
                $archive_failed_items[] = ['type' => 'parts', 'id' => $id, 'error' => $e->getMessage()];
            }

            if (!$archive_success) {
                $this->conn->rollback();
                $message = 'Part deleted but archiving failed. Failed to archive: ' . json_encode($archive_failed_items);
                return $this->formatResponse(false, null, $message);
            }

            // After successful archiving, delete the part
            $query = "DELETE FROM " . $this->table_name . " WHERE part_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                $this->conn->rollback();
                return $this->formatResponse(false, null, 'Failed to delete part');
            }

            // Commit the transaction
            $this->conn->commit();

            // Log the activity
            try {
                AuditLogger::logDelete($this->table_name, $id, $part_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            $message = 'Part deleted and archived successfully';
            return $this->formatResponse(true, null, $message);
        } catch (Exception $e) {
            if ($this->conn->in_transaction) {
                $this->conn->rollback();
            }
            return $this->formatResponse(false, null, 'Failed to archive part: ' . $e->getMessage());
        }
    }

    private function getPartById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE part_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
