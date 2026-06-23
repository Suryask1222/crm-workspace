<?php
// src/Services/FollowupService.php

namespace App\Services;

use App\Repositories\FollowupRepository;
use App\Repositories\LeadRepository;
use App\Repositories\LeadActivityRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\NotificationRepository;
use App\Core\Database;
use Exception;

class FollowupService {
    private FollowupRepository $followRepo;
    private LeadRepository $leadRepo;
    private LeadActivityRepository $activityRepo;
    private AuditLogRepository $auditRepo;
    private NotificationRepository $notifRepo;

    public function __construct() {
        $this->followRepo = new FollowupRepository();
        $this->leadRepo = new LeadRepository();
        $this->activityRepo = new LeadActivityRepository();
        $this->auditRepo = new AuditLogRepository();
        $this->notifRepo = new NotificationRepository();
    }

    public function scheduleFollowup(array $data, int $userId, bool $isAdmin): array {
        $leadId = (int)($data['lead_id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $scheduledAt = $data['scheduled_at'] ?? '';

        if ($leadId <= 0 || empty($title) || empty($scheduledAt)) {
            return ['success' => false, 'message' => 'Lead, Title, and Scheduled Time are required.'];
        }

        $lead = $this->leadRepo->findById($leadId);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found.'];
        }

        if (!$isAdmin && $lead->assigned_to !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized scheduling access.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $targetUser = $lead->assigned_to ?: $userId;
            $formattedScheduled = date('Y-m-d H:i:s', strtotime($scheduledAt));

            $newFollowId = $this->followRepo->create($leadId, $targetUser, $title, $data['description'] ?? null, $formattedScheduled);

            $this->auditRepo->create($userId, 'schedule_followup', 'followups', $newFollowId, null, $data);
            
            $actDescription = "Scheduled followup: '{$title}' for " . date('M j, Y, g:i A', strtotime($scheduledAt));
            $this->activityRepo->create($leadId, $userId, 'followup_scheduled', $actDescription);

            if ($targetUser !== $userId) {
                $this->notifRepo->create($targetUser, "Follow-Up Scheduled", "Agenda: {$title} has been scheduled for lead: {$lead->name}.", "info");
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

    public function completeFollowup(int $id, int $userId, bool $isAdmin): array {
        $followup = $this->followRepo->findById($id);
        if (!$followup) {
            return ['success' => false, 'message' => 'Agenda not found.'];
        }

        if (!$isAdmin && $followup->user_id !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized modification.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $this->followRepo->complete($id);
            
            $this->auditRepo->create($userId, 'complete_followup', 'followups', $id, ['status' => 'pending'], ['status' => 'completed']);
            $this->activityRepo->create($followup->lead_id, $userId, 'followup_completed', "Completed followup: '{$followup->title}'");

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
}
