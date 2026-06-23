<?php
// src/Repositories/NotificationRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Notification;
use PDO;

class NotificationRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNotifications(int $userId, int $limit = 10): array {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit);
        $stmt->execute([$userId]);
        
        $notifications = [];
        while ($row = $stmt->fetch()) {
            $n = new Notification();
            $n->id = (int)$row['id'];
            $n->user_id = (int)$row['user_id'];
            $n->title = $row['title'];
            $n->message = $row['message'];
            $n->type = $row['type'];
            $n->is_read = (int)$row['is_read'];
            $n->created_at = $row['created_at'];
            $notifications[] = $n;
        }
        return $notifications;
    }

    public function markRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function markAllRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function create(int $userId, string $title, string $message, string $type = 'info'): int {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type]);
        return (int)$this->db->lastInsertId();
    }
}
