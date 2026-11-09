<?php

class transactionsHandlers {
    private $conn;
    private $service_report_table = "service_reports";
    private $service_detail_table = "service_details";
    private $staff_table = "staffs";
    private $transaction_table = "transactions";
    private $parts_used_table = "parts_used";

    public function __construct($db) {
        if(!$db instanceof mysqli) {
            throw new InvalidArgumentException("Invalid database connection");
        }
        $this->conn = $db;
    }

    private function formatResponse($success, $data = null, $message = '') {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message ?: ($success ? 'Operation successful' : 'Operation failed')
        ];
    }

    public function getAllTransactions() {
        try {
            // Check if payment_due_date column exists
            $columnCheck = $this->conn->query("SHOW COLUMNS FROM {$this->transaction_table} LIKE 'payment_due_date'");
            $hasPaymentDueDate = $columnCheck && $columnCheck->num_rows > 0;
            
            $paymentDueDateField = $hasPaymentDueDate ? "t.payment_due_date," : "NULL as payment_due_date,";
            
            $stmt = $this->conn->prepare("
                SELECT 
                    t.transaction_id as id,
                    t.report_id,
                    sr.customer_name,
                    sr.appliance_name,
                    sd.total_amount,
                    t.payment_status,
                    t.payment_date,
                    {$paymentDueDateField}
                    t.received_by,
                    s.full_name as received_by_name
                FROM {$this->transaction_table} t
                LEFT JOIN {$this->service_report_table} sr ON t.report_id = sr.report_id
                LEFT JOIN {$this->service_detail_table} sd ON t.report_id = sd.report_id
                LEFT JOIN {$this->staff_table} s ON t.received_by = s.staff_id
                ORDER BY t.payment_date DESC, t.transaction_id DESC
            ");

            $stmt->execute();
            $result = $stmt->get_result();

            $transactions = [];
            while($row = $result->fetch_assoc()) {
                $row['total_amount'] = floatval($row['total_amount'] ?? 0);
                $transactions[] = $row;
            }

            return $this->formatResponse(true, $transactions);

        } catch (Exception $e) {
            error_log("TransactionsHandler Error: " . $e->getMessage());
            return $this->formatResponse(false, null, 'Failed to load transactions: ' . $e->getMessage());
        }
    }

    public function getAllTransactionsPaginated($page = 1, $itemsPerPage = 10, $search = '') {
        try {
            $page = max(1, intval($page));
            $itemsPerPage = max(1, intval($itemsPerPage));
            $offset = ($page - 1) * $itemsPerPage;

            // Check if payment_due_date column exists
            $columnCheck = $this->conn->query("SHOW COLUMNS FROM {$this->transaction_table} LIKE 'payment_due_date'");
            $hasPaymentDueDate = $columnCheck && $columnCheck->num_rows > 0;
            
            $paymentDueDateField = $hasPaymentDueDate ? "t.payment_due_date," : "NULL as payment_due_date,";

            // Base FROM/JOIN clause reused for both count and data
            $fromJoin = " FROM {$this->transaction_table} t
                LEFT JOIN {$this->service_report_table} sr ON t.report_id = sr.report_id
                LEFT JOIN {$this->service_detail_table} sd ON t.report_id = sd.report_id
                LEFT JOIN {$this->staff_table} s ON t.received_by = s.staff_id";

            $where = '';
            $params = [];
            $types = '';
            if (!empty($search)) {
                $where = " WHERE sr.customer_name LIKE ? OR sr.appliance_name LIKE ? OR t.payment_status LIKE ?";
                $like = "%{$search}%";
                $params = [ $like, $like, $like ];
                $types = 'sss';
            }

            // Count total matching rows
            $countSql = "SELECT COUNT(*) as total" . $fromJoin . $where;
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

            // Fetch page of data
            $dataSql = "SELECT 
                    t.transaction_id as id,
                    t.report_id,
                    sr.customer_name,
                    sr.appliance_name,
                    sd.total_amount,
                    t.payment_status,
                    t.payment_date,
                    {$paymentDueDateField}
                    t.received_by,
                    s.full_name as received_by_name" . $fromJoin . $where . "
                ORDER BY t.payment_date DESC, t.transaction_id DESC
                LIMIT ? OFFSET ?";

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

            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $row['total_amount'] = floatval($row['total_amount'] ?? 0);
                $transactions[] = $row;
            }

            $totalPages = (int)ceil($total / $itemsPerPage);
            return [
                'transactions' => $transactions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $itemsPerPage
            ];
        } catch (Exception $e) {
            error_log("TransactionsHandler Error (paginated): " . $e->getMessage());
            return false;
        }
    }

    public function getTransactionById($transactionId) {
        try {
            // Check if payment_due_date column exists
            $columnCheck = $this->conn->query("SHOW COLUMNS FROM {$this->transaction_table} LIKE 'payment_due_date'");
            $hasPaymentDueDate = $columnCheck && $columnCheck->num_rows > 0;
            
            $paymentDueDateField = $hasPaymentDueDate ? "t.payment_due_date," : "NULL as payment_due_date,";
            
            $stmt = $this->conn->prepare("
                SELECT 
                    t.transaction_id as id,
                    t.report_id,
                    t.payment_status,
                    t.payment_date,
                    {$paymentDueDateField}
                    t.received_by,
                    sr.customer_name, 
                    sr.appliance_name,
                    sr.date_in, 
                    sr.status, 
                    sr.dealer, 
                    sr.dop, 
                    sr.date_pulled_out, 
                    sr.findings, 
                    sr.remarks, 
                    sr.location,
                    sd.service_types,
                    sd.service_charge,
                    sd.date_repaired, 
                    sd.date_delivered, 
                    sd.complaint, 
                    sd.labor, 
                    sd.pullout_delivery,
                    sd.parts_total_charge,
                    sd.total_amount, 
                    sd.receptionist, 
                    sd.manager, 
                    sd.technician, 
                    sd.released_by 
                FROM {$this->transaction_table} t
                LEFT JOIN {$this->service_report_table} sr ON t.report_id = sr.report_id
                LEFT JOIN {$this->service_detail_table} sd ON t.report_id = sd.report_id
                WHERE t.transaction_id = ?
            ");        

            $stmt->bind_param("i", $transactionId);

            if(!$stmt->execute()) {
                throw new RuntimeException("Failed to fetch transaction: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $transaction = $result->fetch_assoc();

            if(!$transaction) {
                return $this->formatResponse(false, null, 'Transaction not found');
            }

            // ADD THIS PART: Fetch parts data from parts_used table
            $partsStmt = $this->conn->prepare("
                SELECT part_name, quantity, unit_price 
                FROM {$this->parts_used_table} 
                WHERE report_id = ?
            ");
            $partsStmt->bind_param("i", $transaction['report_id']);
            $partsStmt->execute();
            $partsResult = $partsStmt->get_result();
            
            $parts = [];
            while($part = $partsResult->fetch_assoc()) {
                $parts[] = $part;
            }
            $transaction['parts'] = $parts;

            $transaction['location'] = !empty($transaction['location']) ? json_decode($transaction['location'], true) : [];
            $transaction['service_types'] = !empty($transaction['service_types']) ? json_decode($transaction['service_types'], true) : [];

            $transaction['total_amount'] = !empty($transaction['total_amount']) ? 
            floatval($transaction['total_amount']) : 
            (!empty($transaction['transaction_total_amount']) ? 
                floatval($transaction['transaction_total_amount']) : 0);

            return $this->formatResponse(true, $transaction, 'Transaction loaded successfully');

        } catch (Exception $e){ 
            error_log("TransactionHandler Error: " . $e->getMessage());
            return $this->formatResponse(false, null, 'Failed to load transaction: ' . $e->getMessage());
        }
    }

    public function createTransactionFromReport($reportId, $customerName, $applianceName, $totalAmount) {
        try {
            $checkStmt = $this->conn->prepare("
                SELECT transaction_id FROM {$this->transaction_table} WHERE report_id = ?
            ");
            $checkStmt->bind_param("i", $reportId);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();

            if($existing) {
                return $this->formatResponse(false, null, 'Transaction already exists for this report');
            }

            $stmt = $this->conn->prepare("
                INSERT INTO {$this->transaction_table}
                (report_id, total_amount, payment_status)
                VALUES (?, ?, 'Pending')
            ");

            $stmt->bind_param(
                'id',
                $reportId,
                $totalAmount
            );

            if($stmt->execute()) {
                $transactionId = $this->conn->insert_id;
                return $this->formatResponse(true, ['transaction_id' => $transactionId], 'Transaction created successfully');
            } else {
                error_log('Failed to create transaction: ' . $stmt->error . ' | Query: INSERT INTO ' . $this->transaction_table);
                return $this->formatResponse(false, null, 'Failed to create transaction: ' . $stmt->error);
            }

        } catch (Exception $e) {
            return $this->formatResponse(false, null, 'Create transaction failed: ' . $e->getMessage());
        }
    }

    public function updatePaymentStatus($transactionId, $paymentStatus, $receivedById, $paymentMethod = null, $referenceNumber = null, $paymentDueDate = null) {
        try {
            $paymentDate = $paymentStatus === 'Paid' ? date('Y-m-d') : null;
            
            // Ensure types are correct for binding
            $receivedById = $receivedById ? intval($receivedById) : null;
            $transactionId = intval($transactionId);
            $paymentDueDate = !empty($paymentDueDate) ? $paymentDueDate : null;
            
            $stmt = $this->conn->prepare("
                UPDATE {$this->transaction_table}
                SET payment_status = ?,
                    received_by = ?,
                    payment_date = ?,
                    payment_method = ?,
                    reference_number = ?,
                    payment_due_date = ?
                WHERE transaction_id = ?
            ");

            if (!$stmt) {
                throw new RuntimeException("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("sissisi", $paymentStatus, $receivedById, $paymentDate, $paymentMethod, $referenceNumber, $paymentDueDate, $transactionId);
            
            if(!$stmt->execute()) {
                throw new RuntimeException("Failed to update payment status: " . $stmt->error);
            }

            $receivedByName = null;
            if ($receivedById) {
                $staffStmt = $this->conn->prepare("
                    SELECT full_name FROM {$this->staff_table} WHERE staff_id = ?
                ");
                $staffStmt->bind_param("i", $receivedById);
                $staffStmt->execute();
                $staffResult = $staffStmt->get_result();
                $staff = $staffResult->fetch_assoc();
                $receivedByName = $staff ? $staff['full_name'] : null;
            }

            return $this->formatResponse(true, [
                'transaction_id' => $transactionId,
                'payment_status' => $paymentStatus,
                'received_by' => $receivedById,
                'received_by_name' => $receivedByName,
                'payment_date' => $paymentDate,
                'payment_due_date' => $paymentDueDate,
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber
            ], 'Payment status updated successfully');

        } catch (Exception $e) {
            error_log("TransactionsHandler Error: " . $e->getMessage());
            return $this->formatResponse(false, null, 'Failed to update payment status: ' . $e->getMessage());
        }
    }

    public function setPaymentDueDate($transactionId, $paymentDueDate) {
        try {
            if (empty($paymentDueDate)) {
                return $this->formatResponse(false, null, 'Payment due date is required');
            }

            // Validate date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDueDate)) {
                return $this->formatResponse(false, null, 'Invalid date format. Use YYYY-MM-DD');
            }

            $transactionId = intval($transactionId);
            $stmt = $this->conn->prepare("
                UPDATE {$this->transaction_table}
                SET payment_due_date = ?
                WHERE transaction_id = ?
            ");

            if (!$stmt) {
                throw new RuntimeException("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("si", $paymentDueDate, $transactionId);

            if (!$stmt->execute()) {
                throw new RuntimeException("Failed to set payment due date: " . $stmt->error);
            }

            return $this->formatResponse(true, [
                'transaction_id' => $transactionId,
                'payment_due_date' => $paymentDueDate
            ], 'Payment due date set successfully');

        } catch (Exception $e) {
            error_log("TransactionsHandler Error: " . $e->getMessage());
            return $this->formatResponse(false, null, 'Failed to set payment due date: ' . $e->getMessage());
        }
    }

    public function getTransactionStats() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN sd.total_amount IS NOT NULL THEN sd.total_amount ELSE 0 END) as total_revenue,
                    AVG(CASE WHEN sd.total_amount IS NOT NULL THEN sd.total_amount ELSE 0 END) as average_amount
                FROM {$this->service_report_table} sr
                LEFT JOIN {$this->service_detail_table} sd ON sr.report_id = sd.report_id
                WHERE sr.status = 'Completed'
            ");

            if(!$stmt->execute()) {
                throw new RuntimeException("Failed to fetch transaction stats: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();

            return $this->formatResponse(true, $stats, 'Transaction statistics loaded successfully');
           
        } catch (Exception $e) {
            error_log("TransactionsHandler Error: " . $e->getMessage());
            return $this->formatResponse(false, null, 'Failed to load transaction: ' . $e->getMessage());
        }
    }
} 
?>