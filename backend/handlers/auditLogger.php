<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/auditHandler.php';

class AuditLogger {
    private static $auditHandler = null;
    
    public static function getInstance() {
        if (self::$auditHandler === null) {
            $database = new Database();
            $db = $database->getConnection();
            self::$auditHandler = new AuditHandler($db);
        }
        return self::$auditHandler;
    }
    
    public static function logActivity($action, $table_name, $record_id, $old_values = null, $new_values = null) {
        $auditHandler = self::getInstance();
        
        // Get current user info from session
        $user_id = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'System';
        
        return $auditHandler->logActivity($action, $table_name, $record_id, $old_values, $new_values, $user_id, $username);
    }
    
    public static function logCreate($table_name, $record_id, $new_values) {
        return self::logActivity('CREATE', $table_name, $record_id, null, $new_values);
    }
    
    public static function logUpdate($table_name, $record_id, $old_values, $new_values) {
        return self::logActivity('UPDATE', $table_name, $record_id, $old_values, $new_values);
    }
    
    public static function logDelete($table_name, $record_id, $old_values) {
        return self::logActivity('DELETE', $table_name, $record_id, $old_values, null);
    }
}
?>
