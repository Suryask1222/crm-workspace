<?php
// api/leads.php
require_once __DIR__ . '/../src/bootstrap.php';

$controller = new \App\Controllers\LeadController();
$controller->handleAPI();
