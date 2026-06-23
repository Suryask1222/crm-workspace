<?php
// src/Middleware/CSRFMiddleware.php

namespace App\Middleware;

use App\Core\Session;

class CSRFMiddleware {
    public static function generateToken(): string {
        Session::start();
        $token = Session::get('csrf_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function verifyToken(?string $token): bool {
        Session::start();
        $savedToken = Session::get('csrf_token');
        if (empty($savedToken) || empty($token)) {
            return false;
        }
        return hash_equals($savedToken, $token);
    }
}
