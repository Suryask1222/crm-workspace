<?php
// src/Services/CustomerService.php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\LeadActivityRepository;
use App\Core\Database;
use Exception;

class CustomerService {
    private CustomerRepository $custRepo;
    private AuditLogRepository $auditRepo;
    private LeadActivityRepository $activityRepo;

    public function __construct() {
        $this->custRepo = new CustomerRepository();
        $this->auditRepo = new AuditLogRepository();
        $this->activityRepo = new LeadActivityRepository();
    }

    public function recordPurchase(int $id, float $dealValue, string $status, int $userId): array {
        if ($id <= 0 || $dealValue <= 0) {
            return ['success' => false, 'message' => 'Please enter a valid deal purchase size.'];
        }

        $customer = $this->custRepo->findById($id);
        if (!$customer) {
            return ['success' => false, 'message' => 'Customer profile not found.'];
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $this->custRepo->recordPurchase($id, $dealValue, $status);

            $this->auditRepo->create($userId, 'record_purchase', 'customers', $id, (array)$customer, ['deal_value' => $dealValue, 'status' => $status]);

            if ($customer->lead_id) {
                $description = "Logged subsequent client purchase contract of: $" . number_format($dealValue, 2);
                $this->activityRepo->create($customer->lead_id, $userId, 'sale_logged', $description);
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
}
