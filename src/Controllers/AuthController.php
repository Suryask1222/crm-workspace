<?php
// src/Controllers/AuthController.php

namespace App\Controllers;

use App\Services\AuthService;
use App\Core\Request;
use App\Core\Session;

class AuthController extends Controller {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login(): void {
        Session::start();
        if (Session::has('user_id')) {
            $this->redirect('dashboard.php');
        }

        $error = '';
        $email = '';
        $request = new Request();

        if ($request->isPost()) {
            $email = trim($request->post('email') ?? '');
            $password = $request->post('password') ?? '';
            
            if (!$request->validateCSRF()) {
                $error = 'Invalid security request (CSRF check failed).';
            } elseif (empty($email) || empty($password)) {
                $error = 'Please fill in all details.';
            } else {
                $res = $this->authService->login($email, $password, $request->getIp(), $request->getUserAgent());
                if ($res['success']) {
                    $this->redirect('dashboard.php');
                } else {
                    $error = $res['message'];
                }
            }
        }

        // Fetch company name setting directly
        $companyName = 'Nexentora Technologies';
        try {
            $db = \App\Core\Database::getConnection();
            $stmtSettings = $db->query("SELECT value_value FROM settings WHERE key_name = 'company_name'");
            $dbCompany = $stmtSettings->fetchColumn();
            if ($dbCompany) {
                $companyName = $dbCompany;
            }
        } catch (\PDOException $e) {}

        $this->render('login', [
            'error' => $error,
            'email' => $email,
            'companyName' => $companyName,
            'baseURL' => getBaseURL()
        ]);
    }

    public function logout(): void {
        $this->authService->logout();
        $this->redirect('index.php');
    }
}
