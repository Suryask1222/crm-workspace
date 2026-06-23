<?php
// src/Repositories/LoginLogRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\LoginLog;
use PDO;

class LoginLogRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getRecentLogs(int $limit = 15): array {
        $stmt = $this->db->query("
            SELECT l.*, u.name AS user_name, u.email AS user_email 
            FROM login_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.login_time DESC LIMIT " . (int)$limit);
        
        $logs = [];
        while ($row = $stmt->fetch()) {
            $l = new LoginLog();
            $l->id = (int)$row['id'];
            $l->user_id = $row['user_id'] !== null ? (int)$row['user_id'] : null;
            $l->ip_address = $row['ip_address'];
            $l->user_agent = $row['user_agent'];
            $l->login_time = $row['login_time'];
            $l->status = $row['status'];
            $l->user_name = $row['user_name'] ?? 'Unknown';
            $l->user_email = $row['user_email'] ?? '-';
            $logs[] = $l;
        }
        return $logs;
    }

    public function create(?int $userId, string $ipAddress, string $userAgent, string $status): int {
        $stmt = $this->db->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $ipAddress, $userAgent, $status]);
        return (int)$this->db->lastInsertId();
    }
}
