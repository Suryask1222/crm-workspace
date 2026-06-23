<?php
// src/Services/SettingService.php

namespace App\Services;

use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use App\Repositories\AuditLogRepository;
use App\Core\Database;
use Exception;

class SettingService {
    private SettingRepository $settingRepo;
    private UserRepository $userRepo;
    private AuditLogRepository $auditRepo;

    public function __construct() {
        $this->settingRepo = new SettingRepository();
        $this->userRepo = new UserRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function saveSettings(array $settingsData, int $userId): array {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $oldSettings = $this->settingRepo->getSettings();
            $keysToSave = ['company_name', 'currency', 'currency_symbol', 'timezone', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'whatsapp_api_url', 'sms_api_key'];
            
            $filteredData = [];
            foreach ($keysToSave as $key) {
                if (isset($settingsData[$key])) {
                    $filteredData[$key] = $settingsData[$key];
                }
            }

            $this->settingRepo->saveSettings($filteredData);
            $this->auditRepo->create($userId, 'update_settings', 'settings', null, $oldSettings, $filteredData);

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Failed to save settings: ' . $e->getMessage()];
        }
    }

    public function createUser(array $data, int $userId, bool $isAdmin): array {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Restricted operation.'];
        }

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $roleId = (int)($data['role_id'] ?? 0);

        if (empty($name) || empty($email) || empty($password) || $roleId <= 0) {
            return ['success' => false, 'message' => 'All inputs are required.'];
        }

        try {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $newUId = $this->userRepo->create($name, $email, $hashedPass, $roleId);

            $this->auditRepo->create($userId, 'create_user', 'users', $newUId, null, ['name' => $name, 'email' => $email, 'role_id' => $roleId]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database registration failure (email may already exist): ' . $e->getMessage()];
        }
    }

    public function toggleUserStatus(int $id, string $status, int $userId, bool $isAdmin): array {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if ($id === $userId) {
            return ['success' => false, 'message' => 'You cannot deactivate your own account.'];
        }

        $user = $this->userRepo->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        try {
            $this->userRepo->updateStatus($id, $status);
            $this->auditRepo->create($userId, 'toggle_user_status', 'users', $id, ['status' => $user->status], ['status' => $status]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
