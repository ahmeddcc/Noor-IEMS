<?php
// app/Models/AuditLog.php

namespace App\Models;

use App\Core\Database;

class AuditLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get logs with pagination and filters
     */
    public function getLogs($limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT al.*, u.username as user_name 
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND al.timestamp >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND al.timestamp <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY al.timestamp DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get total count for pagination
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND timestamp >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Add a new log entry
     */
    public static function log($userId, $action, $target, $details = null) {
        $db = Database::getInstance();
        return $db->query(
            "INSERT INTO audit_logs (user_id, action, target, details) VALUES (?, ?, ?, ?)",
            [$userId, $action, $target, $details]
        );
    }
    /**
     * Delete a log entry
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM audit_logs WHERE id = ?", [$id]);
    }

    /**
     * Clear all log entries
     */
    public function clearAll() {
        return $this->db->query("DELETE FROM audit_logs");
    }
}
