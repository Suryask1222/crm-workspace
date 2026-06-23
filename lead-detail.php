<?php
// lead-detail.php
$pageTitle = 'Lead Profile Workspace';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\LeadController();
$controller->detail();

include 'includes/footer.php';
