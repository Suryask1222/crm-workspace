<?php
// src/Services/TaskService.php

namespace App\Services;

use App\Repositories\TaskRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Core\Database;
use Exception;

class TaskService {
    private TaskRepository $taskRepo;
    private AuditLogRepository $auditRepo;
    private NotificationRepository $notifRepo;
    private UserRepository $userRepo;

    public function __construct() {
        $this->taskRepo = new TaskRepository();
        $this->auditRepo = new AuditLogRepository();
        $this->notifRepo = new NotificationRepository();
        $this->userRepo = new UserRepository();
    }

    public function createTask(array $data, int $userId): array {
        $title = trim($data['title'] ?? '');
        $assignedTo = (int)($data['assigned_to'] ?? 0);

        if (empty($title) || $assignedTo <= 0) {
            return ['success' => false, 'message' => 'Task Title and Assigned Member are required.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $data['created_by'] = $userId;
            $taskId = $this->taskRepo->create($data);

            $this->auditRepo->create($userId, 'create_task', 'tasks', $taskId, null, $data);

            if ($assignedTo !== $userId) {
                $creator = $this->userRepo->findById($userId);
                $creatorName = $creator ? $creator->name : 'System';
                $this->notifRepo->create($assignedTo, "New Task Assigned", "Task: {$title} has been assigned to you by {$creatorName}.", "info");
            }

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateTaskStatus(int $id, string $status, int $userId, bool $isAdmin): array {
        $task = $this->taskRepo->findById($id);
        if (!$task) {
            return ['success' => false, 'message' => 'Task not found.'];
        }

        if (!$isAdmin && $task->assigned_to !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized task modification.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $this->taskRepo->updateStatus($id, $status);
            $this->auditRepo->create($userId, 'update_task_status', 'tasks', $id, ['status' => $task->status], ['status' => $status]);

            if ($status === 'completed' && $task->created_by !== $userId) {
                $this->notifRepo->create($task->created_by, "Task Completed", "Task: {$task->title} has been marked as completed by assignee.", "success");
            }

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteTask(int $id, int $userId, bool $isAdmin): array {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Action restricted to Admin.'];
        }

        $task = $this->taskRepo->findById($id);
        if (!$task) {
            return ['success' => false, 'message' => 'Task not found.'];
        }

        try {
            $this->taskRepo->delete($id);
            $this->auditRepo->create($userId, 'delete_task', 'tasks', $id, (array)$task, null);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
