<?php
// src/Core/Response.php

namespace App\Core;

class Response {
    public static function json(array $data, int $statusCode = 200): void {
        // Ensure headers are sent
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($statusCode);
        }
        echo json_encode($data);
        exit();
    }

    public static function redirect(string $url): void {
        header("Location: " . $url);
        exit();
    }
}
