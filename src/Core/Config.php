<?php
// src/Core/Config.php

namespace App\Core;

class Config {
    private static array $data = [];

    public static function load(string $path): void {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and invalid lines
            if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Strip enclosing quotes if present
            if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                $value = $matches[1];
            }

            self::$data[$name] = $value;
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }
        
        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        return $_ENV[$key] ?? $default;
    }
}
