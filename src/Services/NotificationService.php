<?php
// src/Services/NotificationService.php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Exception;

class NotificationService {
    private NotificationRepository $notifRepo;

    public function __construct() {
        $this->notifRepo = new NotificationRepository();
    }

    public function getNotifications(int $userId): array {
        try {
            $notifications = $this->notifRepo->getNotifications($userId);
            return ['success' => true, 'notifications' => $notifications];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    public function checkNewCount(int $userId): array {
        try {
            $count = $this->notifRepo->getUnreadCount($userId);
            return ['success' => true, 'new_count' => $count];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    public function markRead(int $id, int $userId): array {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Missing ID.'];
        }

        try {
            $this->notifRepo->markRead($id, $userId);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    public function markAllRead(int $userId): array {
        try {
            $this->notifRepo->markAllRead($userId);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error'];
        }
    }
}
