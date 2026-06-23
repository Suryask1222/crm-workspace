<?php
// src/Repositories/LeadNoteRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\LeadNote;
use PDO;

class LeadNoteRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getLeadNotes(int $leadId): array {
        $stmt = $this->db->prepare("
            SELECT n.*, u.name AS user_name 
            FROM lead_notes n
            JOIN users u ON n.user_id = u.id
            WHERE n.lead_id = ?
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$leadId]);
        
        $notes = [];
        while ($row = $stmt->fetch()) {
            $n = new LeadNote();
            $n->id = (int)$row['id'];
            $n->lead_id = (int)$row['lead_id'];
            $n->user_id = (int)$row['user_id'];
            $n->note = $row['note'];
            $n->is_internal = (int)$row['is_internal'];
            $n->created_at = $row['created_at'];
            $n->user_name = $row['user_name'];
            $notes[] = $n;
        }
        return $notes;
    }

    public function create(int $leadId, int $userId, string $note, int $isInternal): int {
        $stmt = $this->db->prepare("INSERT INTO lead_notes (lead_id, user_id, note, is_internal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$leadId, $userId, $note, $isInternal]);
        return (int)$this->db->lastInsertId();
    }
}
