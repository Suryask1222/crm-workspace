<?php
// settings.php
$pageTitle = 'System Settings & Controls';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\SettingController();
$controller->index();

include 'includes/footer.php';
