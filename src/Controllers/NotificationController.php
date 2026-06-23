<?php
// src/Controllers/NotificationController.php

namespace App\Controllers;

use App\Services\NotificationService;
use App\Core\Request;

class NotificationController extends Controller {
    private NotificationService $notifService;

    public function __construct() {
        $this->notifService = new NotificationService();
    }

    public function handleAPI(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];

        $request = new Request();

        if ($request->isGet()) {
            $action = $request->get('action', '');

            if ($action === 'get_all') {
                $res = $this->notifService->getNotifications($userId);
                // Map object models back to raw arrays for JSON API backward compatibility
                if ($res['success']) {
                    $rawNotifs = [];
                    foreach ($res['notifications'] as $notif) {
                        $rawNotifs[] = [
                            'id' => $notif->id,
                            'user_id' => $notif->user_id,
                            'title' => $notif->title,
                            'message' => $notif->message,
                            'type' => $notif->type,
                            'is_read' => $notif->is_read,
                            'created_at' => $notif->created_at
                        ];
                    }
                    $this->json(['success' => true, 'notifications' => $rawNotifs]);
                } else {
                    $this->json($res);
                }
                return;
            }

            if ($action === 'check_new') {
                $res = $this->notifService->checkNewCount($userId);
                $this->json($res);
                return;
            }
        }

        if ($request->isPost()) {
            $inputs = $request->all();
            $action = $inputs['action'] ?? '';

            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'CSRF verification failed.']);
                return;
            }

            if ($action === 'mark_read') {
                $id = (int)($inputs['id'] ?? 0);
                $res = $this->notifService->markRead($id, $userId);
                $this->json($res);
                return;
            }

            if ($action === 'mark_all_read') {
                $res = $this->notifService->markAllRead($userId);
                $this->json($res);
                return;
            }
        }

        $this->json(['success' => false, 'message' => 'Action not recognized.']);
    }
}
