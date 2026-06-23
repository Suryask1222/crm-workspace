<?php
// src/Repositories/TaskRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Task;
use PDO;

class TaskRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getTasksByStatus(string $status, ?int $assignedTo = null, ?int $limit = null): array {
        $where = ["t.status = ?"];
        $params = [$status];

        if ($assignedTo !== null) {
            $where[] = "t.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $whereSQL = "WHERE " . implode(" AND ", $where);
        
        $orderBy = "ORDER BY t.due_date ASC";
        if ($status === 'completed') {
            $orderBy = "ORDER BY t.due_date DESC";
        }

        $limitSQL = $limit !== null ? " LIMIT " . (int)$limit : "";

        $sql = "
            SELECT t.*, u.name AS assigned_name 
            FROM tasks t 
            JOIN users u ON t.assigned_to = u.id 
            {$whereSQL} 
            {$orderBy}
            {$limitSQL}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $tasks = [];
        while ($row = $stmt->fetch()) {
            $tasks[] = $this->mapRowToModel($row);
        }
        return $tasks;
    }

    public function findById(int $id): ?Task {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->mapRowToModel($row);
    }

    public function getTaskDetails(int $id): ?array {
        $stmt = $this->db->prepare("SELECT assigned_to, created_by, status, title FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO tasks (title, description, assigned_to, created_by, priority, status, due_date) 
            VALUES (?, ?, ?, ?, ?, 'todo', ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['assigned_to'],
            $data['created_by'],
            $data['priority'] ?? 'medium',
            !empty($data['due_date']) ? $data['due_date'] : null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function mapRowToModel(array $row): Task {
        $task = new Task();
        $task->id = (int)$row['id'];
        $task->title = $row['title'];
        $task->description = $row['description'] ?? null;
        $task->assigned_to = (int)$row['assigned_to'];
        $task->created_by = (int)$row['created_by'];
        $task->priority = $row['priority'];
        $task->status = $row['status'];
        $task->due_date = $row['due_date'] ?? null;
        $task->created_at = $row['created_at'];
        $task->updated_at = $row['updated_at'];
        $task->assigned_name = $row['assigned_name'] ?? null;
        return $task;
    }
}
