<?php
// leads.php
$pageTitle = 'Lead Database';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\LeadController();
$controller->index();

include 'includes/footer.php';