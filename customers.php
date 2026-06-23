<?php
// customers.php
$pageTitle = 'Customer Directory';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\CustomerController();
$controller->index();

include 'includes/footer.php';
