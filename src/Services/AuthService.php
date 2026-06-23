<?php
// src/Services/AuthService.php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\LoginLogRepository;
use App\Core\Session;

class AuthService {
    private UserRepository $userRepo;
    private LoginLogRepository $loginLogRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->loginLogRepo = new LoginLogRepository();
    }

    public function login(string $email, string $password, string $ip, string $agent): array {
        $user = $this->userRepo->findByEmail($email);
        
        if (!$user) {
            $this->loginLogRepo->create(null, $ip, $agent, 'failed');
            return ['success' => false, 'message' => 'Incorrect email or password.'];
        }

        if (!password_verify($password, $user->password)) {
            $this->loginLogRepo->create($user->id, $ip, $agent, 'failed');
            return ['success' => false, 'message' => 'Incorrect email or password.'];
        }

        if ($user->status !== 'active') {
            $this->loginLogRepo->create($user->id, $ip, $agent, 'failed');
            return ['success' => false, 'message' => 'Your account is deactivated. Contact Administrator.'];
        }

        // Success: Setup secure session parameters
        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::set('user_role', $user->role_name);
        Session::set('user_name', $user->name);
        Session::set('user_email', $user->email);

        $this->loginLogRepo->create($user->id, $ip, $agent, 'success');
        return ['success' => true, 'user' => $user];
    }

    public function logout(): void {
        Session::destroy();
    }
}
