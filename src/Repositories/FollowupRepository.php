<?php
// src/Repositories/FollowupRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Followup;
use PDO;

class FollowupRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getFollowupsBetween(string $start, string $end, ?int $userId = null): array {
        $params = [$start, $end];
        $userFilter = "";
        
        if ($userId !== null) {
            $userFilter = "AND f.user_id = ?";
            $params[] = $userId;
        }

        $sql = "
            SELECT f.*, l.name AS lead_name, l.company AS lead_company 
            FROM followups f
            JOIN leads l ON f.lead_id = l.id
            WHERE f.scheduled_at BETWEEN ? AND ? {$userFilter}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $followups = [];
        while ($row = $stmt->fetch()) {
            $f = new Followup();
            $f->id = (int)$row['id'];
            $f->lead_id = (int)$row['lead_id'];
            $f->user_id = (int)$row['user_id'];
            $f->title = $row['title'];
            $f->description = $row['description'] ?? null;
            $f->scheduled_at = $row['scheduled_at'];
            $f->status = $row['status'];
            $f->created_at = $row['created_at'];
            $f->lead_name = $row['lead_name'];
            $f->lead_company = $row['lead_company'] ?? null;
            $followups[] = $f;
        }
        return $followups;
    }

    public function getUpcomingFollowups(?int $userId = null, int $limit = 5): array {
        $params = [];
        $userFilter = "";

        if ($userId !== null) {
            $userFilter = "AND f.user_id = ?";
            $params[] = $userId;
        }

        $sql = "
            SELECT f.*, l.name AS lead_name, u.name AS staff_name
            FROM followups f
            JOIN leads l ON f.lead_id = l.id
            JOIN users u ON f.user_id = u.id
            WHERE f.status = 'pending' AND f.scheduled_at >= NOW() {$userFilter}
            ORDER BY f.scheduled_at ASC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $followups = [];
        while ($row = $stmt->fetch()) {
            $f = new Followup();
            $f->id = (int)$row['id'];
            $f->lead_id = (int)$row['lead_id'];
            $f->user_id = (int)$row['user_id'];
            $f->title = $row['title'];
            $f->description = $row['description'] ?? null;
            $f->scheduled_at = $row['scheduled_at'];
            $f->status = $row['status'];
            $f->created_at = $row['created_at'];
            $f->lead_name = $row['lead_name'];
            $f->staff_name = $row['staff_name'];
            $followups[] = $f;
        }
        return $followups;
    }

    public function getFollowupsCountToday(?int $userId = null): int {
        $params = [];
        $userFilter = "";

        if ($userId !== null) {
            $userFilter = "AND user_id = ?";
            $params[] = $userId;
        }

        $sql = "SELECT COUNT(*) FROM followups WHERE DATE(scheduled_at) = CURRENT_DATE AND status = 'pending' {$userFilter}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?Followup {
        $stmt = $this->db->prepare("SELECT * FROM followups WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $f = new Followup();
        $f->id = (int)$row['id'];
        $f->lead_id = (int)$row['lead_id'];
        $f->user_id = (int)$row['user_id'];
        $f->title = $row['title'];
        $f->description = $row['description'] ?? null;
        $f->scheduled_at = $row['scheduled_at'];
        $f->status = $row['status'];
        $f->created_at = $row['created_at'];
        return $f;
    }

    public function create(int $leadId, int $userId, string $title, ?string $description, string $scheduledAt): int {
        $stmt = $this->db->prepare("
            INSERT INTO followups (lead_id, user_id, title, description, scheduled_at, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$leadId, $userId, $title, $description, $scheduledAt]);
        return (int)$this->db->lastInsertId();
    }

    public function complete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE followups SET status = 'completed' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getLeadFollowups(int $leadId): array {
        $stmt = $this->db->prepare("
            SELECT f.*, u.name AS user_name 
            FROM followups f
            JOIN users u ON f.user_id = u.id
            WHERE f.lead_id = ?
            ORDER BY f.scheduled_at ASC
        ");
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
}
