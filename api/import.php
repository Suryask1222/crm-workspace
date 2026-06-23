<?php
// api/import.php
require_once __DIR__ . '/../src/bootstrap.php';

$controller = new \App\Controllers\LeadController();
$controller->import();
