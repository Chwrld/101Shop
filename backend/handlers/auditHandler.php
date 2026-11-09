<?php
class AuditHandler {
    private $conn;
    private $table_name = "audit_log";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function logActivity($action, $table_name, $record_id, $old_values = null, $new_values = null, $user_id = null, $username = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, username, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;
        
        $stmt->bind_param("isssissss", 
            $user_id, $username, $action, $table_name, $record_id, 
            $old_json, $new_json, $ip_address, $user_agent
        );
        
        return $stmt->execute();
    }

    public function getActivityLog($page = 1, $itemsPerPage = 10, $filter = 'all', $search = '') {
        $offset = ($page - 1) * $itemsPerPage;
        
        $where_conditions = [];
        $params = [];
        $types = "";
        
        // Add filter condition
        if ($filter !== 'all') {
            $where_conditions[] = "table_name = ?";
            $params[] = $filter;
            $types .= "s";
        }
        
        // Add search condition
        if (!empty($search)) {
            $where_conditions[] = "(action LIKE ? OR table_name LIKE ? OR username LIKE ?)";
            $search_param = "%{$search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "sss";
        }
        
        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM " . $this->table_name . " " . $where_clause;
        $count_stmt = $this->conn->prepare($count_query);
        
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        
        $count_stmt->execute();
        $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
        
        // Get paginated results
        $query = "SELECT * FROM " . $this->table_name . " " . $where_clause . " 
                  ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $all_params = array_merge($params, [$itemsPerPage, $offset]);
            $stmt->bind_param($types . "ii", ...$all_params);
        } else {
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'action' => $row['action'],
                'table_name' => $row['table_name'],
                'record_id' => $row['record_id'],
                'old_values' => $row['old_values'] ? json_decode($row['old_values'], true) : null,
                'new_values' => $row['new_values'] ? json_decode($row['new_values'], true) : null,
                'ip_address' => $row['ip_address'],
                'user_agent' => $row['user_agent'],
                'created_at' => $row['created_at']
            ];
        }
        
        $total_pages = ceil($total_items / $itemsPerPage);
        
        return [
            'activities' => $activities,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'items_per_page' => $itemsPerPage
        ];
    }
}
?>
