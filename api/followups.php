<?php
// api/followups.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/bootstrap.php';

$controller = new \App\Controllers\FollowupController();
$controller->handleAPI();
