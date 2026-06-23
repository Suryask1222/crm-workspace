<?php
// src/Controllers/FollowupController.php

namespace App\Controllers;

use App\Repositories\FollowupRepository;
use App\Repositories\LeadRepository;
use App\Services\FollowupService;
use App\Core\Request;
use App\Core\Session;

class FollowupController extends Controller {
    private FollowupRepository $followRepo;
    private LeadRepository $leadRepo;
    private FollowupService $followService;

    public function __construct() {
        $this->followRepo = new FollowupRepository();
        $this->leadRepo = new LeadRepository();
        $this->followService = new FollowupService();
    }

    public function index(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $assignedTo = $isAdmin ? null : $userId;

        try {
            $leads = $this->leadRepo->getLeadsCount('', '', '', $assignedTo) > 0 
                ? $this->leadRepo->getLeads('', '', '', $assignedTo, 1000, 0)
                : [];

            $this->render('calendar', [
                'leads' => $leads,
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

        if ($request->isGet()) {
            $action = $request->get('action', '');
            if ($action === 'get_events') {
                $start = $request->get('start', '');
                $end = $request->get('end', '');

                if (empty($start) || empty($end)) {
                    $this->json([]);
                    return;
                }

                $startDate = date('Y-m-d H:i:s', strtotime($start));
                $endDate = date('Y-m-d H:i:s', strtotime($end));
                $assignedTo = $isAdmin ? null : $userId;

                $followups = $this->followRepo->getFollowupsBetween($startDate, $endDate, $assignedTo);
                
                $events = [];
                foreach ($followups as $evt) {
                    $companyLabel = $evt->lead_company ? " ({$evt->lead_company})" : "";
                    $events[] = [
                        'id' => $evt->id,
                        'title' => $evt->title . " - " . $evt->lead_name . $companyLabel,
                        'start' => $evt->scheduled_at,
                        'allDay' => false,
                        'extendedProps' => [
                            'status' => $evt->status,
                            'lead_id' => $evt->lead_id,
                            'description' => $evt->description
                        ]
                    ];
                }

                $this->json($events);
            }
            return;
        }

        if ($request->isPost()) {
            $inputs = $request->all();
            $action = $inputs['action'] ?? '';

            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'CSRF verification failed.']);
                return;
            }

            switch ($action) {
                case 'create':
                    $res = $this->followService->scheduleFollowup($inputs, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'complete':
                    $id = (int)($inputs['id'] ?? 0);
                    $res = $this->followService->completeFollowup($id, $userId, $isAdmin);
                    $this->json($res);
                    break;

                default:
                    $this->json(['success' => false, 'message' => 'Action not recognized.']);
                    break;
            }
        }
    }
}
