<?php
// src/bootstrap.php

// Load the autoloader first
require_once __DIR__ . '/Autoloader.php';
\App\Autoloader::register();

// Load environment variables from .env
\App\Core\Config::load(__DIR__ . '/../.env');

// Load the procedural configurations to maintain backward compatibility
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Initialize a secure session start
\App\Core\Session::start();
