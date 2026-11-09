<?php

class AuthHandler {
    private $pdo;

    public function __construct() {
        $config = include(__DIR__ . '/../../database/database.php');
        $this->pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password']
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM staffs WHERE username = ? AND status = 'active'
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                error_log("Password verified for staff: " . $user['staff_id']);
                $userType = $this->mapRoleToUserType($user['role']);
                error_log("Role mapping: {$user['role']} => {$userType}");

                //generare session token
                $sessionToken = bin2hex(random_bytes(32));

                //store session in dv
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_sessions (staff_id, session_token, expires_at, ip_address, user_agent)
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 8 HOUR), ?, ?)
                ");

                $insertResult = $stmt->execute([
                    $user['staff_id'],
                    $sessionToken,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                ]);

                error_log("Session insert result: " . ($insertResult ? 'SUCCESS' : 'FAILED'));
                error_log("Session token generated: " . $sessionToken);
                error_log("StaffID: " . $user['staff_id']); 

                // check if session was actually inserted
                $checkStmt = $this->pdo->prepare("SELECT * FROM user_sessions WHERE session_token = ?");
                $checkStmt->execute([$sessionToken]);
                $insertedSession = $checkStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Session verification: " . ($insertedSession ? 'FOUND' : 'NOT FOUND'));
                
                if ($insertedSession) {
                    error_log("Session ID: " . $insertedSession['session_id']);
                    error_log("Session Expires: " . $insertedSession['expires_at']);
                    error_log("Session IsActive: " . $insertedSession['is_active']);
                }

                // update last login 
                $stmt = $this->pdo->prepare("UPDATE staffs SET last_login = NOW() WHERE staff_id = ?");
                $stmt->execute([$user['staff_id']]);

                // set session variables 
                $_SESSION['user'] = [
                    'user_id' => $user['staff_id'],
                    'username' => $user['username'],
                    'user_type' => $userType,
                    'full_name' => $user['full_name'],
                    'original_role' => $user['role'],
                    'session_token' => $sessionToken,
                    'login_time' => time()
                ];

                // Convenience top-level session values for templates
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                error_log("Session set in PHP: " . $_SESSION['user']['session_token']);

                return [
                    'success' => true,
                    'user_type' => $userType,
                    'original_role' => $user['role'],
                    'message' => 'Login successful'
                ];
            }

            return ['success' => false, 'message' => 'Invalid username or password'];
           
        } catch (PDOException $e) {
            error_log("Auth Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    } 

    public function validateSession() {
        error_log('=== VALIDATE SESSION DEBUG ===');
        if(!isset($_SESSION['user']['session_token'])) {
            error_log("No session token in session");
            return false;
        }

        try {
            $sessionToken = $_SESSION['user']['session_token'];
            error_log("Validating token: " . substr($sessionToken, 0, 16) . "...");

            $stmt = $this->pdo->prepare("
                SELECT us.*, s.full_name, s.role, s.status, s.username
                FROM user_sessions us
                JOIN staffs s ON us.staff_id = s.staff_id
                WHERE us.session_token = ? AND us.is_active = TRUE AND us.expires_at > NOW()
            ");
            $stmt->execute([$sessionToken]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($session) {
                error_log("Session found in DB, Status: " . $session['status']);
                error_log("Session details - ID: " . $session['session_id'] . ", staff_id: " . $session['staff_id'] . ", is_active: " . $session['is_active'] . ", expires_at: " . $session['expires_at']);
                error_log("Staff Role: " . $session['role']);

                if(strtolower($session['status']) == 'active') {
                    $userType = $this->mapRoleToUserType($session['role']);
                    error_log("Mapped role to user type: {$session['role']} => {$userType}");
                    
                    $newExpires = date('Y-m-d H:i:s', strtotime('+8 hours'));
                    $stmt = $this->pdo->prepare("
                        UPDATE user_sessions SET expires_at = ? WHERE session_token = ?
                    ");
                    $stmt->execute([$newExpires, $sessionToken]);

                    // update session with current role mapping 
                    $_SESSION['user']['user_type'] = $userType;
                    $_SESSION['user']['original_role'] = $session['role'];

                    // Ensure convenience session variables are available for templates
                    if (!empty($session['username'])) {
                        $_SESSION['username'] = $session['username'];
                    } elseif (!empty($session['full_name'])) {
                        // fallback to full_name if username not available
                        $_SESSION['username'] = $session['full_name'];
                    }
                    $_SESSION['full_name'] = $session['full_name'] ?? ($_SESSION['user']['full_name'] ?? '');
                    error_log("Session validated successfully");
                    return $session;
                } else {
                    error_log("Staff account is not active");
                }
            } else {
                error_log("No active session found in database");

                // check if session exists at all (fixed query)
                $checkStmt = $this->pdo->prepare("SELECT * FROM user_sessions WHERE session_token = ?");
                $checkStmt->execute([$sessionToken]);
                $anySession = $checkStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Any session with this token: " . ($anySession ? 'EXISTS' : 'NOT EXISTS'));

                if($anySession) {
                    error_log("But session is is_active: " . $anySession['is_active'] . ", expires_at: " . $anySession['expires_at']);
                }
            }
            return false;
       
        } catch (PDOException $e) {
            error_log("Session Validation Error: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        if(isset($_SESSION['user']['session_token'])) {
            try {
                //mark session as inactive in database
                $stmt = $this->pdo->prepare("
                    UPDATE user_sessions SET is_active = FALSE WHERE session_token = ?
                ");
                $stmt->execute([$_SESSION['user']['session_token']]);
           
            } catch (PDOException $e) {
                error_log("Logout Error: " . $e->getMessage());
            }
        }

        //clear session
        $_SESSION = [];
        session_destroy();
    }

    public function requireAuth($accessLevel = 'any') {
        $session = $this->validateSession();

        if(!$session) {
            header("Location: ../index.php");
            exit();
        }

        $userType = $_SESSION['user']['user_type'];
        $originalRole = $_SESSION['user']['original_role'] ?? '';

        error_log("Access check - Required: {$accessLevel}, User Type: {$userType}, Original Role: {$originalRole}");

        switch($accessLevel) {
            case 'admin':
                if($userType !== 'admin') {
                    header("Location: ../index.php");
                    exit();
                }
                break;

            case 'staff':
                if($userType !== 'staff') {
                    header("Location: ../index.php");
                    exit();
                }
                break;

            case 'both': 
                if(!in_array($userType, ['admin', 'staff'])) {
                    header("Location: ../index.php");
                    exit();
                }
                break;

            // case: 'any':
            default:
                break;
        }
        
        error_log("Access granted for user type: {$userType}");
        return $session;
    }

    public function cleanExpiredSessions() {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE user_sessions SET is_active = FALSE WHERE expires_at < NOW()
            ");
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Session Cleanup Error: " . $e->getMessage());
        }
    }

    public function getSessionTimeLeft() {
        if(!isset($_SESSION['user']['session_token'])) {
            return 0;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) as seconds_left
                FROM user_sessions
                WHERE session_token = ? AND is_active = TRUE
            ");
            $stmt->execute([$_SESSION['user']['session_token']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? max(0, $result['seconds_left']) : 0;  
        } catch (PDOException $e) {
            error_log("Session time left error: " . $e->getMessage());
            return 0;
        }
    }

    public function extendSession() {
        if(!isset($_SESSION['user']['session_token'])) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE user_sessions SET expires_at = DATE_ADD(NOW(), INTERVAL 8 HOUR)
                WHERE session_token = ?
            ");
            return $stmt->execute([$_SESSION['user']['session_token']]);
        } catch (PDOException $e) {
            error_log("Extend session token: " . $e->getMessage());
            return false;
        }
    }

    private function mapRoleToUserType($role) {
        $role = strtolower(trim($role));

        if ($role === 'manager') {
            return 'admin';
        } elseif (in_array($role, ['secretary', 'technician'])) {
            return 'staff';
        }
    }
}
?>