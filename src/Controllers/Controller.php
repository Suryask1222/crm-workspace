<?php
// src/Controllers/Controller.php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Core\View;

abstract class Controller {
    protected array $currentUser = [];

    protected function requireLogin(): array {
        $this->currentUser = AuthMiddleware::requireLogin();
        return $this->currentUser;
    }

    protected function requireAdmin(): array {
        $this->currentUser = AuthMiddleware::requireAdmin();
        return $this->currentUser;
    }

    protected function getUserId(): int {
        if (empty($this->currentUser)) {
            $this->requireLogin();
        }
        return $this->currentUser['id'];
    }

    protected function isAdmin(): bool {
        if (empty($this->currentUser)) {
            $this->requireLogin();
        }
        return $this->currentUser['role'] === 'Admin';
    }

    protected function json(array $data, int $statusCode = 200): void {
        Response::json($data, $statusCode);
    }

    protected function redirect(string $url): void {
        Response::redirect($url);
    }

    protected function render(string $viewName, array $data = []): void {
        View::render($viewName, $data);
    }
}
