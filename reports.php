<?php
// reports.php
$pageTitle = 'Enterprise Reporting & BI';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\ReportController();
$controller->index();

include 'includes/footer.php';
