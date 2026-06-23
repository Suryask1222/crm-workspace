<?php
// src/Services/LeadService.php

namespace App\Services;

use App\Repositories\LeadRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\LeadActivityRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\NotificationRepository;
use App\Core\Database;
use Exception;

class LeadService {
    private LeadRepository $leadRepo;
    private CustomerRepository $custRepo;
    private LeadActivityRepository $activityRepo;
    private AuditLogRepository $auditRepo;
    private NotificationRepository $notifRepo;

    public function __construct() {
        $this->leadRepo = new LeadRepository();
        $this->custRepo = new CustomerRepository();
        $this->activityRepo = new LeadActivityRepository();
        $this->auditRepo = new AuditLogRepository();
        $this->notifRepo = new NotificationRepository();
    }

    public function createLead(array $data, int $userId): array {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'message' => 'Lead contact name is required.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $newLeadId = $this->leadRepo->create($data);

            $this->auditRepo->create($userId, 'create_lead', 'leads', $newLeadId, null, $data);
            $this->activityRepo->create($newLeadId, $userId, 'lead_created', "Lead profile created by executive.");

            $assignedTo = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null;
            if ($assignedTo) {
                $this->activityRepo->create($newLeadId, $userId, 'lead_assigned', "Assigned lead workspace to user ID: {$assignedTo}");
                $this->notifRepo->create($assignedTo, "New Lead Assigned", "You have been assigned to lead: {$name} (" . ($data['company'] ?? 'Personal') . ")", "info");
            }

            $db->commit();
            return ['success' => true, 'id' => $newLeadId];
        } catch (Exception $e) {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateLead(int $id, array $data, int $userId, bool $isAdmin): array {
        $lead = $this->leadRepo->findById($id);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found.'];
        }

        if (!$isAdmin && $lead->assigned_to !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized profile access.'];
        }

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'message' => 'Required parameters missing.'];
        }

        try {
            $this->leadRepo->update($id, $data);
            $this->auditRepo->create($userId, 'update_lead', 'leads', $id, (array)$lead, $data);
            $this->activityRepo->create($id, $userId, 'lead_updated', "Lead profile values updated.");
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteLead(int $id, int $userId, bool $isAdmin): array {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Restricted operation. Admin authority needed.'];
        }

        $lead = $this->leadRepo->findById($id);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead already deleted.'];
        }

        try {
            $this->leadRepo->delete($id);
            $this->auditRepo->create($userId, 'delete_lead', 'leads', $id, (array)$lead, null);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateStatus(int $id, string $status, int $userId, bool $isAdmin): array {
        $lead = $this->leadRepo->findById($id);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found.'];
        }

        if (!$isAdmin && $lead->assigned_to !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized access.'];
        }

        try {
            $this->leadRepo->updateStatus($id, $status);
            $this->auditRepo->create($userId, 'update_status', 'leads', $id, ['status' => $lead->status], ['status' => $status]);
            $this->activityRepo->create($id, $userId, 'status_changed', "Status updated to: " . str_replace('_', ' ', $status));
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function transferLead(int $id, ?int $assignedTo, int $userId, bool $isAdmin): array {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Action restricted to Super Admins.'];
        }

        $lead = $this->leadRepo->findById($id);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $this->leadRepo->transfer($id, $assignedTo);

            // Log history
            $stmt = $db->prepare("INSERT INTO lead_assignments (lead_id, assigned_by, assigned_to) VALUES (?, ?, ?)");
            $stmt->execute([$id, $userId, $assignedTo]);

            $this->auditRepo->create($userId, 'transfer_lead', 'leads', $id, ['assigned_to' => $lead->assigned_to], ['assigned_to' => $assignedTo]);
            $this->activityRepo->create($id, $userId, 'lead_assigned', "Lead ownership transferred to user ID: {$assignedTo}");

            if ($assignedTo) {
                $this->notifRepo->create($assignedTo, "Lead Transferred", "Lead: {$lead->name} has been transferred to your list.", "info");
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

    public function convertToCustomer(int $leadId, float $totalPurchases, int $userId, bool $isAdmin): array {
        $lead = $this->leadRepo->findById($leadId);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found.'];
        }

        if (!$isAdmin && $lead->assigned_to !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized workspace privilege.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $this->leadRepo->updateStatus($leadId, 'converted');
            $newCustId = $this->custRepo->createFromLead($leadId, $lead->name, $lead->phone, $lead->email, $lead->company, $totalPurchases);

            $this->auditRepo->create($userId, 'convert_customer', 'customers', $newCustId, null, ['lead_id' => $leadId, 'total_purchases' => $totalPurchases]);
            $this->activityRepo->create($leadId, $userId, 'status_changed', "Status updated to Converted. Deal value completed: $" . number_format($totalPurchases, 2));

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

    public function importCSV(string $fileTmpPath, int $userId, bool $isAdmin): array {
        try {
            $file = fopen($fileTmpPath, 'r');
            if (!$file) {
                return ['success' => false, 'message' => 'Could not read uploaded file.'];
            }

            $headers = fgetcsv($file);
            if (!$headers) {
                fclose($file);
                return ['success' => false, 'message' => 'Uploaded file is empty.'];
            }

            $insertedCount = 0;
            $db = Database::getConnection();
            $db->beginTransaction();

            $assignedTo = $isAdmin ? null : $userId;

            while (($row = fgetcsv($file)) !== false) {
                $row = array_map('trim', $row);
                $name = $row[0] ?? '';
                $phone = $row[1] ?? '';
                $email = $row[2] ?? '';
                $company = $row[3] ?? '';
                $industry = $row[4] ?? '';
                $source = $row[5] ?? '';
                $priority = strtolower($row[6] ?? 'medium');
                if (!in_array($priority, ['low', 'medium', 'high'])) {
                    $priority = 'medium';
                }
                $expectedValue = (float)($row[7] ?? 0.00);

                if (empty($name)) {
                    continue;
                }

                $leadData = [
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'company' => $company,
                    'industry' => $industry,
                    'source' => $source,
                    'priority' => $priority,
                    'status' => 'new',
                    'assigned_to' => $assignedTo,
                    'expected_value' => $expectedValue
                ];

                $newLeadId = $this->leadRepo->create($leadData);
                $this->activityRepo->create($newLeadId, $userId, 'lead_created', "Lead bulk imported via CSV sheet.");
                $insertedCount++;
            }

            fclose($file);

            if ($insertedCount > 0) {
                $this->auditRepo->create($userId, 'import_leads_csv', 'leads', null, null, ['count' => $insertedCount]);
                $db->commit();
                return ['success' => true, 'inserted' => $insertedCount];
            } else {
                $db->rollBack();
                return ['success' => false, 'message' => 'No valid leads found in CSV. Make sure Name column is filled.'];
            }
        } catch (Exception $e) {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Import processing failed: ' . $e->getMessage()];
        }
    }
}
