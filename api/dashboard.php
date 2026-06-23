<?php
// api/dashboard.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/bootstrap.php';

$controller = new \App\Controllers\DashboardController();
$controller->apiStats();
