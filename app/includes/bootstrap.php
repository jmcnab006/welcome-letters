<?php

declare(strict_types=1);

session_start();

// const APP_TITLE = 'Welcome Letter Webform';

const BASE_PATH = __DIR__ . '/..';
const CONFIG_PATH = BASE_PATH . '/config';
const TEMPLATE_PATH = BASE_PATH . '/templates';
const VENDOR_PATH = BASE_PATH . '/vendor';

require_once CONFIG_PATH . '/default-config.php';

$letters = require CONFIG_PATH . '/letter-config.php';

if (is_file(CONFIG_PATH . '/letter-functions.php')) {
    require_once CONFIG_PATH . '/letter-functions.php';
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mailer.php';
