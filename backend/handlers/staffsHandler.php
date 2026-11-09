<?php
require_once __DIR__ . '/auditLogger.php';

class StaffsHandler
{
    private $conn;
    private $table_name = "staffs";

    public function getTotalTechnicians()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table_name} WHERE role = 'technician'";
        $result = $this->conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return intval($row['total']);
        }
        return 0;
    }

    private function formatResponse($success, $data = null, $message = '')
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message ?: ($success ? 'Operation successful' : 'Operation failed')
        ];
    }

    private function formatTime($datetime)
    {
        if (empty($datetime)) return '';
        $timestamp = strtotime($datetime);
        return date('Y-m-d g:ia', $timestamp);
    }

    private function formatDate($datetime)
    {
        if (empty($datetime)) return '';
        $datestamp = strtotime($datetime);
        return date("Y-m-d", $datestamp);
    }

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getStaffByEmail($email)
    {
        try {
            error_log("Searching for staff with email: " . $email);
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE email = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }

            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            error_log("Staff lookup result: " . ($staff ? "Found" : "Not found"));
            return $staff;
        } catch (Exception $e) {
            error_log("GetStaffByEmail Error: " . $e->getMessage());
            return false;
        }
    }


    public function updateResetToken($staffId, $token, $expiry)
    {
        try {
            error_log("Updating reset token for staff ID: " . $staffId);

            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET reset_token = ?, reset_token_expiry = ? WHERE staff_id = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }

            $stmt->bind_param("ssi", $token, $expiry, $staffId);
            $result = $stmt->execute();

            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            error_log("Reset token updated successfully for staff ID: " . $staffId);
            return true;
        } catch (Exception $e) {
            error_log("UpdateResetToken Error: " . $e->getMessage());
            return false;
        }
    }

    public function clearResetToken($staffId)
    {
        try {
            error_log("Clearing reset token for staff ID: " . $staffId);

            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET reset_token = NULL, reset_token_expiry = NULL WHERE staff_id = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }

            $stmt->bind_param("i", $staffId);
            $result = $stmt->execute();

            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            error_log("Reset token cleared successfully");
            return true;
        } catch (Exception $e) {
            error_log("ClearResetToken Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllStaffs($page = 1, $itemsPerPage = 10, $search = '')
    {
        try {
            // Calculate offset
            $offset = ($page - 1) * $itemsPerPage;
            
            // Base query
            $baseQuery = "FROM " . $this->table_name;
            
            // Add search condition if search term is provided
            if (!empty($search)) {
                $baseQuery .= " WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ? OR role LIKE ?";
            }
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
            
            if (!empty($search)) {
                $stmt = $this->conn->prepare($countQuery);
                $searchTerm = "%{$search}%";
                $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $totalResult = $stmt->get_result();
            } else {
                $totalResult = $this->conn->query($countQuery);
            }
            
            $totalRow = $totalResult->fetch_assoc();
            $total = $totalRow['total'];
            
            // Get paginated data
            $query = "SELECT * " . $baseQuery . " ORDER BY date_created DESC LIMIT ? OFFSET ?";
            
            if (!empty($search)) {
                $stmt = $this->conn->prepare($query);
                $searchTerm = "%{$search}%";
                $stmt->bind_param("ssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $itemsPerPage, $offset);
            } else {
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $itemsPerPage, $offset);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $staffs = [];
            while ($row = $result->fetch_assoc()) {
                $row['last_login'] = $this->formatTime($row['last_login']);
                $row['date_created'] = $this->formatDate($row['date_created']);
                $staffs[] = $row;
            }
            
            $totalPages = ceil($total / $itemsPerPage);
            
            return $this->formatResponse(true, [
                'staffs' => $staffs,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $itemsPerPage
            ]);
        } catch (Exception $e) {
            error_log('Error in getAllStaffs: ' . $e->getMessage());
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function getStaffsById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE staff_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function addStaffs($fullname, $username, $email, $password, $role, $status)
    {
        //check first if username exists
        $checkQuery = "SELECT staff_id FROM " . $this->table_name . " WHERE username = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param('s', $username);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            return $this->formatResponse(false, null, 'Username already exists.');
        }

        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table_name . " 
                    (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssss', $fullname, $username, $email, $hashedpassword, $role, $status);

        if ($stmt->execute()) {
            $staff_id = $stmt->insert_id;
            
            // Log the activity
            try {
                $new_data = $this->getStaffsById($staff_id);
                AuditLogger::logCreate($this->table_name, $staff_id, $new_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }
            
            return $this->formatResponse(true, ['id' => $staff_id], 'Staff added successfully');
        }

        return $this->formatResponse(false, null, 'Failed to add staff');
    }

    public function updateStaffs($id, $fullname, $username, $email, $password, $role, $status)
    {
        try {
            // Get old data before update for audit logging
            $old_data = $this->getStaffsById($id);
            
            //check if new username conflicts with others
            $checkQuery = "SELECT staff_id FROM " . $this->table_name . " WHERE username = ? AND staff_id != ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bind_param('si', $username, $id);
            $checkStmt->execute();

            if ($checkStmt->get_result()->num_rows > 0) {
                return $this->formatResponse(false, null, 'Username already taken by another staff');
            }

            if ($password !== null) {
                $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

                $query = "UPDATE " . $this->table_name . "  SET full_name = ?, username = ?, email = ?, password = ?, role = ?, status = ?
                            WHERE staff_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('ssssssi', $fullname, $username, $email, $hashedpassword, $role, $status, $id);
            } else {
                $query = "UPDATE " . $this->table_name . "
                        SET full_name = ?, username = ?, email = ?, role = ?, status = ?
                        WHERE staff_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('sssssi', $fullname, $username, $email, $role, $status, $id);
            }

            if ($stmt->execute()) {
                // Log the activity
                try {
                    $new_data = $this->getStaffsById($id);
                    AuditLogger::logUpdate($this->table_name, $id, $old_data, $new_data);
                } catch (Exception $e) {
                    error_log('Audit logging error: ' . $e->getMessage());
                }
                
                return $this->formatResponse(true, null, 'Staff updated successfully');
            } else {
                return $this->formatResponse(false, null, 'Failed to update staff');
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, null, $e->getMessage());
        }
    }

    public function verifyCurrentPassword($staffId, $currentPassword)
    {
        try {
            $query = "SELECT Password FROM " . $this->table_name . " WHERE staff_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $staffId);
            $stmt->execute();

            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            if (!$staff) {
                return [
                    'success' => false,
                    'verified' => false,
                    'message' => 'Staff not found'
                ];
            }

            $isValid = password_verify($currentPassword, $staff['Password']);

            return [
                'success' => true,
                'verified' => $isValid,
                'message' => $isValid ? 'Password verified' : 'Incorrect password'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'verified' => false,
                'message' => 'Verification error: ' . $e->getMessage()
            ];
        }
    }

    public function deleteStaffs($id)
    {
        try {
            // Get staff data before deletion for archiving
            $staff_data = $this->getStaffsById($id);
            
            if (!$staff_data) {
                return $this->formatResponse(false, null, 'Staff not found');
            }

            // Start transaction - archive first, then delete staff
            $this->conn->begin_transaction();

            // Archive the record first
            $archive_success = true;
            $archive_failed_items = [];
            try {
                require_once __DIR__ . '/archiveHandler.php';
                $archiveHandler = new ArchiveHandler($this->conn);
                $archiveResult = $archiveHandler->archiveRecord($this->table_name, $id, $staff_data, $_SESSION['user_id'] ?? null, 'Staff deleted');
                $archive_success = $archive_success && $archiveResult;
                if (!$archiveResult) {
                    $archive_failed_items[] = $id;
                    error_log('Archive logging failed for staff id: ' . $id);
                }
            } catch (Exception $e) {
                error_log('Archive logging error: ' . $e->getMessage());
                $archive_success = false;
                $archive_failed_items[] = ['type' => 'staffs', 'id' => $id, 'error' => $e->getMessage()];
            }

            if (!$archive_success) {
                $this->conn->rollback();
                $message = 'Staff deleted but archiving failed. Failed to archive: ' . json_encode($archive_failed_items);
                return $this->formatResponse(false, null, $message);
            }

            // Proceed to delete the staff only if archiving succeeded
            $query = "DELETE FROM " . $this->table_name . " WHERE staff_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);

            if (!$stmt->execute()) {
                $this->conn->rollback();
                return $this->formatResponse(false, null, 'Failed to delete staff');
            }

            // Commit the transaction
            $this->conn->commit();

            // Log the activity
            try {
                AuditLogger::logDelete($this->table_name, $id, $staff_data);
            } catch (Exception $e) {
                error_log('Audit logging error: ' . $e->getMessage());
            }

            return $this->formatResponse(true, null, 'Staff deleted and archived successfully');

        } catch (Exception $e) {
            if ($this->conn->in_transaction) {
                $this->conn->rollback();
            }
            return $this->formatResponse(false, null, 'Failed to archive staff: ' . $e->getMessage());
        }
    }

    //used as in service report page as a feature
    public function getStaffsbyRole($role = null)
    {
        try {
            $query = "SELECT staff_id, username, role FROM " . $this->table_name . " WHERE status = 'Active'";

            //role filter if specified
            if ($role !== null) {
                $query .= " AND role = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $role);
            } else {
                $stmt = $this->conn->prepare($query);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $staffs = [];

            while ($row = $result->fetch_assoc()) {
                $staffs[] = $row;
            }

            return $this->formatResponse(
                true,
                $staffs,
                empty($staffs) ? 'No active staffs found' . ($role ? ' for role: ' . $role : '') : ''
            );
        } catch (Exception $e) {
            return $this->formatResponse(false, null, 'Failed to load Staff by role:' . $e->getMessage());
        }
    }

    public function validateResetToken($token)
    {
        try {
            error_log("Validating reset token: " . $token);

            $stmt = $this->conn->prepare("SELECT staff_id FROM " . $this->table_name . " 
                WHERE reset_token = ? AND reset_token_expiry > NOW() AND status = 'Active'");

            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return ['valid' => false];
            }

            $stmt->bind_param("s", $token);

            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return ['valid' => false];
            }

            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            if ($staff) {
                error_log("Valid token found for staff ID: " . $staff['staff_id']);
                return [
                    'valid' => true,
                    'staff_id' => $staff['staff_id']
                ];
            }

            error_log("Token is invalid or expired");
            return ['valid' => false];
        } catch (Exception $e) {
            error_log("ValidateResetToken Error: " . $e->getMessage());
            return ['valid' => false];
        }
    }

    public function updatePassword($staffId, $newPassword)
    {
        try {
            error_log("Updating password for staff ID: " . $staffId);

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " 
                SET password = ? WHERE staff_id = ?");

            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }

            $stmt->bind_param("si", $hashedPassword, $staffId);

            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            error_log("Password updated successfully");
            return true;
        } catch (Exception $e) {
            error_log("UpdatePassword Error: " . $e->getMessage());
            return false;
        }
    }
}
?>