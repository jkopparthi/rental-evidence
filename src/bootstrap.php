<?php
// Loaded at the top of every page in /public.
declare(strict_types=1);

$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Missing config/config.php. Copy config/config.example.php to config/config.php and set your database details.');
}
$config = require $configFile;

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/photos.php';
require __DIR__ . '/layout.php';
