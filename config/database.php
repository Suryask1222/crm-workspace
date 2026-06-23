<?php
// config/database.php

require_once __DIR__ . '/../src/Autoloader.php';
\App\Autoloader::register();

\App\Core\Config::load(__DIR__ . '/../.env');

define('DB_CREDENTIALS_FILE', __DIR__ . '/db_credentials.php');

function getDBConnection(): PDO
{
    $host = \App\Core\Config::get('DB_HOST', 'localhost');
    $dbname = \App\Core\Config::get('DB_NAME', 'crm');
    $username = \App\Core\Config::get('DB_USER', 'use yours');
    $password = \App\Core\Config::get('DB_PASS', 'your password');

    if (file_exists(DB_CREDENTIALS_FILE)) {
        $creds = include DB_CREDENTIALS_FILE;
        if (is_array($creds)) {
            $host = $creds['host'] ?? $host;
            $dbname = $creds['dbname'] ?? $dbname;
            $username = $creds['username'] ?? $username;
            $password = $creds['password'] ?? $password;
        }
    }

    try {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        // Output clean human error or log it securely
        die("CRM Database Connection Failed: " . htmlspecialchars($e->getMessage()));
    }
}
