<?php
// src/Controllers/SettingController.php

namespace App\Controllers;

use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use App\Repositories\LoginLogRepository;
use App\Repositories\AuditLogRepository;
use App\Services\SettingService;
use App\Core\Request;
use App\Core\Session;

class SettingController extends Controller {
    private SettingRepository $settingRepo;
    private UserRepository $userRepo;
    private LoginLogRepository $loginLogRepo;
    private AuditLogRepository $auditRepo;
    private SettingService $settingService;

    public function __construct() {
        $this->settingRepo = new SettingRepository();
        $this->userRepo = new UserRepository();
        $this->loginLogRepo = new LoginLogRepository();
        $this->auditRepo = new AuditLogRepository();
        $this->settingService = new SettingService();
    }

    public function index(): void {
        $user = $this->requireAdmin();
        $userId = $user['id'];

        $request = new Request();
        $successMsg = '';
        $errorMsg = '';

        if ($request->isPost()) {
            $action = $request->post('action') ?? '';
            if ($action === 'save_settings') {
                $inputs = $request->all();
                $res = $this->settingService->saveSettings($inputs, $userId);
                if ($res['success']) {
                    $successMsg = 'CRM parameters updated successfully!';
                } else {
                    $errorMsg = $res['message'];
                }
            }
        }

        try {
            $settings = $this->settingRepo->getSettings();
            $roles = $this->userRepo->getRoles();
            $usersList = $this->userRepo->getAllUsers();
            $loginLogs = $this->loginLogRepo->getRecentLogs(15);
            $auditLogs = $this->auditRepo->getRecentLogs(15);

            $this->render('settings', [
                'settings' => $settings,
                'roles' => $roles,
                'usersList' => $usersList,
                'loginLogs' => $loginLogs,
                'auditLogs' => $auditLogs,
                'successMsg' => $successMsg,
                'errorMsg' => $errorMsg
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
