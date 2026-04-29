<?php
// includes/bootstrap.php

declare(strict_types=1);

session_start();

const APP_TITLE = 'Welcome Letter Webform';

const DEFAULT_FROM_EMAIL = 'noreply@example.com';
const DEFAULT_FROM_NAME  = 'Example Company';

const BASE_PATH = __DIR__ . '/..';
// const CONFIG_PATH = BASE_PATH . '/config';
const TEMPLATE_PATH = BASE_PATH . '/templates';
// const VENDOR_PATH = BASE_PATH . '/vendor';

const DEBUG_MODE = true;

// Load config
$letters = require 'config.php';

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Load helpers
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mailer.php';
