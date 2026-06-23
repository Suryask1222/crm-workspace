<?php
// tasks.php
$pageTitle = 'Task Kanban Workspace';
include 'includes/header.php';

require_once __DIR__ . '/src/bootstrap.php';

$controller = new \App\Controllers\TaskController();
$controller->index();

include 'includes/footer.php';
