<?php
// src/Repositories/LeadActivityRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\LeadActivity;
use PDO;

class LeadActivityRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getLeadActivities(int $leadId): array {
        $stmt = $this->db->prepare("
            SELECT la.*, u.name AS user_name 
            FROM lead_activities la
            JOIN users u ON la.user_id = u.id
            WHERE la.lead_id = ?
            ORDER BY la.created_at DESC
        ");
        $stmt->execute([$leadId]);
        
        $activities = [];
        while ($row = $stmt->fetch()) {
            $la = new LeadActivity();
            $la->id = (int)$row['id'];
            $la->lead_id = (int)$row['lead_id'];
            $la->user_id = (int)$row['user_id'];
            $la->activity_type = $row['activity_type'];
            $la->description = $row['description'];
            $la->created_at = $row['created_at'];
            $la->user_name = $row['user_name'];
            $activities[] = $la;
        }
        return $activities;
    }

    public function create(int $leadId, int $userId, string $type, string $description): int {
        $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$leadId, $userId, $type, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function getRecentActivities(?int $userId = null, int $limit = 5): array {
        $params = [];
        $userFilter = "";

        if ($userId !== null) {
            $userFilter = "WHERE l.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "
            SELECT la.*, u.name AS user_name, l.name AS lead_name 
            FROM lead_activities la
            JOIN users u ON la.user_id = u.id
            JOIN leads l ON la.lead_id = l.id
            {$userFilter}
            ORDER BY la.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $activities = [];
        while ($row = $stmt->fetch()) {
            $la = new LeadActivity();
            $la->id = (int)$row['id'];
            $la->lead_id = (int)$row['lead_id'];
            $la->user_id = (int)$row['user_id'];
            $la->activity_type = $row['activity_type'];
            $la->description = $row['description'];
            $la->created_at = $row['created_at'];
            $la->user_name = $row['user_name'];
            $la->lead_name = $row['lead_name'];
            $activities[] = $la;
        }
        return $activities;
    }
}
