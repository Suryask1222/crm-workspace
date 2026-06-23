<?php
// src/Repositories/AuditLogRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\AuditLog;
use PDO;

class AuditLogRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getRecentLogs(int $limit = 15): array {
        $stmt = $this->db->query("
            SELECT a.*, u.name AS user_name 
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC LIMIT " . (int)$limit);
        
        $logs = [];
        while ($row = $stmt->fetch()) {
            $l = new AuditLog();
            $l->id = (int)$row['id'];
            $l->user_id = $row['user_id'] !== null ? (int)$row['user_id'] : null;
            $l->action = $row['action'];
            $l->table_name = $row['table_name'];
            $l->record_id = $row['record_id'] !== null ? (int)$row['record_id'] : null;
            $l->old_values = $row['old_values'];
            $l->new_values = $row['new_values'];
            $l->created_at = $row['created_at'];
            $l->user_name = $row['user_name'] ?? 'System';
            $logs[] = $l;
        }
        return $logs;
    }

    public function create(?int $userId, string $action, string $tableName, ?int $recordId, ?array $oldValues, ?array $newValues): int {
        $oldJson = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
        $newJson = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $tableName, $recordId, $oldJson, $newJson]);
        return (int)$this->db->lastInsertId();
    }
}
