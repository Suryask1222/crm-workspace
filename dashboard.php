<?php
// dashboard.php
$pageTitle = 'Dashboard Analytics';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\DashboardController();
$controller->index();

include 'includes/footer.php';
