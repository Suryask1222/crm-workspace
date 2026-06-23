<?php
// index.php
require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\AuthController();
$controller->login();
