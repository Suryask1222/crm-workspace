<?php
// src/Middleware/AuthMiddleware.php

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class AuthMiddleware {
    public static function requireLogin(): array {
        Session::start();
        $userId = Session::get('user_id');
        if (empty($userId)) {
            Response::redirect('index.php');
        }
        
        $role = Session::get('user_role');
        return [
            'id' => (int)$userId,
            'role' => $role === 'Admin' ? 'Admin' : 'Staff'
        ];
    }

    public static function requireAdmin(): array {
        $user = self::requireLogin();
        if ($user['role'] !== 'Admin') {
            Response::redirect('dashboard.php?error=unauthorized');
        }
        return $user;
    }
}
