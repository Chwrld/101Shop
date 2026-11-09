<?php
require_once __DIR__ . '/bootstrapArchive.php';

class ArchiveHandler {
    private $conn;
    private $table_name = "archive_records";
    private $tableExists = null;

    public function __construct($db) {
        $this->conn = $db;
        // Try to ensure table exists on initialization
        ensureArchiveTableExists($db);
        $this->checkTableExists();
    }

    private function checkTableExists() {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }
        
        $result = $this->conn->query("SHOW TABLES LIKE '{$this->table_name}'");
        $this->tableExists = ($result && $result->num_rows > 0);
        
        if (!$this->tableExists) {
            error_log('WARNING: archive_records table does not exist. Archiving will be skipped.');
        }
        
        return $this->tableExists;
    }

    public function archiveRecord($table_name, $record_id, $deleted_data, $deleted_by = null, $reason = null) {
        // Check if archive table exists, if not, log and return success (graceful degradation)
        if (!$this->checkTableExists()) {
            error_log('ArchiveHandler->archiveRecord skipped: archive_records table does not exist. Table: ' . $table_name . ' Record: ' . $record_id);
            // Return true to not break the deletion process
            return true;
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (table_name, record_id, deleted_data, deleted_by, reason) 
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log('ArchiveHandler->archiveRecord prepare failed: ' . $this->conn->error);
            // Return true to not break deletion even if archiving fails
            return true;
        }

        // Encode deleted_data into JSON, using partial output fallback if needed.
        $deleted_json = json_encode($deleted_data);
        if ($deleted_json === false) {
            $fallback = json_encode($deleted_data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($fallback === false) {
                // As a final fallback, serialize or export the value to a string
                $deleted_json = var_export($deleted_data, true);
                error_log('ArchiveHandler->archiveRecord json_encode failed and used var_export fallback');
            } else {
                $deleted_json = $fallback;
                error_log('ArchiveHandler->archiveRecord used JSON_PARTIAL_OUTPUT_ON_ERROR fallback');
            }
        }

        // Normalize deleted_by and reason
        $deleted_by_val = is_numeric($deleted_by) ? intval($deleted_by) : 0; // 0 denotes system or unknown
        $reason_val = $reason !== null ? $reason : '';

        // Bind params (use sisis as in previous design: string, int, string, int, string)
        $bindResult = $stmt->bind_param("sisis", $table_name, $record_id, $deleted_json, $deleted_by_val, $reason_val);
        if ($bindResult === false) {
            error_log('ArchiveHandler->archiveRecord bind_param failed: ' . $stmt->error);
            // Return true to not break deletion
            return true;
        }

        $execResult = $stmt->execute();
        if ($execResult === false) {
            error_log('ArchiveHandler->archiveRecord execute failed: ' . $stmt->error . ' | SQL: ' . $query . ' | data: ' . substr($deleted_json, 0, 2000));
            // Return true to not break deletion even if archiving fails
            return true;
        }
        error_log('ArchiveHandler->archiveRecord success: table=' . $table_name . ' record=' . $record_id . ' by=' . var_export($deleted_by_val, true));
        return true;
    }

    public function getArchivedRecords($page = 1, $itemsPerPage = 10, $search = '') {
        // Check if table exists
        if (!$this->checkTableExists()) {
            return [
                'archives' => [],
                'current_page' => 1,
                'total_pages' => 0,
                'total_items' => 0,
                'items_per_page' => $itemsPerPage
            ];
        }
        
        $offset = ($page - 1) * $itemsPerPage;
        
        $where_conditions = [];
        $params = [];
        $types = "";
        
        // Add search condition
        if (!empty($search)) {
            $where_conditions[] = "(table_name LIKE ? OR record_id LIKE ? OR reason LIKE ?)";
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
                  ORDER BY id ASC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $all_params = array_merge($params, [$itemsPerPage, $offset]);
            $stmt->bind_param($types . "ii", ...$all_params);
        } else {
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $archives = [];
        while ($row = $result->fetch_assoc()) {
            $archives[] = [
                'id' => $row['id'],
                'table_name' => $row['table_name'],
                'record_id' => $row['record_id'],
                'deleted_data' => json_decode($row['deleted_data'], true),
                'deleted_by' => $row['deleted_by'],
                'deleted_at' => $row['deleted_at'],
                'reason' => $row['reason']
            ];
        }

        error_log('ArchiveHandler->getArchivedRecords result: ' . count($archives) . ' rows (page ' . $page . ')');
        
        $total_pages = ceil($total_items / $itemsPerPage);
        
        return [
            'archives' => $archives,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'items_per_page' => $itemsPerPage
        ];
    }

    public function restoreRecord($archive_id) {
        // Get the archived record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $archive_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $table_name = $row['table_name'];
            $deleted_data = json_decode($row['deleted_data'], true);
            
            // Restore the record to the original table
            $columns = array_keys($deleted_data);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            
            $restore_query = "INSERT INTO {$table_name} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
            $restore_stmt = $this->conn->prepare($restore_query);
            $restore_stmt->bind_param(str_repeat('s', count($columns)), ...array_values($deleted_data));
            
            if ($restore_stmt->execute()) {
                // Delete from archive
                $delete_query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
                $delete_stmt = $this->conn->prepare($delete_query);
                $delete_stmt->bind_param("i", $archive_id);
                return $delete_stmt->execute();
            }
        }
        
        return false;
    }
}
?>
