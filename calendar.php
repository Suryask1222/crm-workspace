<?php
// calendar.php
$pageTitle = 'Follow-up Calendar';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\FollowupController();
$controller->index();

include 'includes/footer.php';
