<?php
// src/Core/View.php

namespace App\Core;

class View {
    public static function render(string $viewName, array $data = []): void {
        $viewPath = __DIR__ . '/../../views/' . $viewName . '.view.php';
        if (!file_exists($viewPath)) {
            die("CRM View Template [{$viewName}] was not found.");
        }

        // Expose data keys as variables in the template scope
        extract($data);

        require $viewPath;
    }
}
