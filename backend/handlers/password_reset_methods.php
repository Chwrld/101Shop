    public function validateResetToken($token)
    {
    try {
    error_log("Validating reset token: " . $token);

    $stmt = $this->conn->prepare("SELECT StaffID FROM " . $this->table_name . "
    WHERE reset_token = ? AND reset_token_expiry > UTC_TIMESTAMP() AND Status = 'Active'");

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
    error_log("Valid token found for staff ID: " . $staff['StaffID']);
    return [
    'valid' => true,
    'staff_id' => $staff['StaffID']
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
    SET Password = ? WHERE StaffID = ?");

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