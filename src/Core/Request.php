<?php
// src/Core/Request.php

namespace App\Core;

class Request {
    private array $get;
    private array $post;
    private array $server;
    private ?array $json = null;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public function getMethod(): string {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function isPost(): bool {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool {
        return $this->getMethod() === 'GET';
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed {
        if ($this->json !== null && isset($this->json[$key])) {
            return $this->json[$key];
        }
        return $this->post[$key] ?? $default;
    }

    public function all(): array {
        $data = array_merge($this->get, $this->post, $this->json ?? []);
        return $this->sanitizeArray($data);
    }

    public function getIp(): string {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function getUserAgent(): string {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function validateCSRF(): bool {
        $token = $this->post('csrf_token') ?? $this->get('csrf_token') ?? '';
        if (empty($token) && isset($this->server['HTTP_X_CSRF_TOKEN'])) {
            $token = $this->server['HTTP_X_CSRF_TOKEN'];
        }
        return verifyCSRFToken($token);
    }

    private function sanitizeArray(array $data): array {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->sanitizeArray($value);
            } else {
                $cleaned[$key] = is_string($value) ? trim($value) : $value;
            }
        }
        return $cleaned;
    }
}
