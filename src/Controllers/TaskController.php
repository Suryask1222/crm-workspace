<?php
// src/Controllers/TaskController.php

namespace App\Controllers;

use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\TaskService;
use App\Core\Request;
use App\Core\Session;

class TaskController extends Controller {
    private TaskRepository $taskRepo;
    private UserRepository $userRepo;
    private TaskService $taskService;

    public function __construct() {
        $this->taskRepo = new TaskRepository();
        $this->userRepo = new UserRepository();
        $this->taskService = new TaskService();
    }

    public function index(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $assignedTo = $isAdmin ? null : $userId;

        try {
            $todoTasks = $this->taskRepo->getTasksByStatus('todo', $assignedTo);
            $progressTasks = $this->taskRepo->getTasksByStatus('in_progress', $assignedTo);
            $completedTasks = $this->taskRepo->getTasksByStatus('completed', $assignedTo, 15);
            $usersList = $this->userRepo->getActiveUsersList();

            $this->render('tasks', [
                'todoTasks' => $todoTasks,
                'progressTasks' => $progressTasks,
                'completedTasks' => $completedTasks,
                'usersList' => $usersList,
                'isAdmin' => $isAdmin,
                'userId' => $userId
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function handleAPI(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();

        if ($request->isPost()) {
            $inputs = $request->all();
            $action = $inputs['action'] ?? '';

            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'CSRF verification failed.']);
                return;
            }

            switch ($action) {
                case 'create':
                    $res = $this->taskService->createTask($inputs, $userId);
                    $this->json($res);
                    break;

                case 'update_status':
                    $id = (int)($inputs['id'] ?? 0);
                    $status = $inputs['status'] ?? '';
                    $res = $this->taskService->updateTaskStatus($id, $status, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'delete':
                    $id = (int)($inputs['id'] ?? 0);
                    $res = $this->taskService->deleteTask($id, $userId, $isAdmin);
                    $this->json($res);
                    break;

                default:
                    $this->json(['success' => false, 'message' => 'Action not recognized.']);
                    break;
            }
        }
    }
}
