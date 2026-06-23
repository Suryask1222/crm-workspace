<?php
// config/security.php

require_once __DIR__ . '/../src/Autoloader.php';
\App\Autoloader::register();

// Session hardening (Must be called before session_start)
function secureSessionStart(): void {
    \App\Core\Session::start();
}

// Generate CSRF token
function generateCSRFToken(): string {
    return \App\Middleware\CSRFMiddleware::generateToken();
}

// Verify CSRF token
function verifyCSRFToken(?string $token): bool {
    return \App\Middleware\CSRFMiddleware::verifyToken($token);
}

// XSS Sanitizer Helper
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Output sanitized string directly
function e(string $data): void {
    echo sanitize($data);
}

// Clean request inputs
function cleanInput(array $data): array {
    $cleaned = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $cleaned[$key] = cleanInput($value);
        } else {
            $cleaned[$key] = trim($value);
        }
    }
    return $cleaned;
}

// Generate Dynamic Application Base URL
function getBaseURL(): string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = rtrim($dir, '/');
    
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || ($_SERVER['SERVER_PORT'] ?? 0) == 443;
    $protocol = $isSecure ? 'https://' : 'http://';
    
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    return $protocol . $host . $dir . '/';
}

