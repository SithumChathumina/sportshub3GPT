<?php

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('SPORTSHUB_SESSION');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Folder name beneath htdocs. Use '' if uploading to a hosting account's root.
// Example: http://localhost/sportshub3/ uses '/sportshub3'.
define('BASE_PATH', '/sportshub3');
date_default_timezone_set('Asia/Colombo');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Log technical details on the server and show a short message to the user.
function handle_application_error($exception)
{
    error_log($exception->getMessage());
    http_response_code(500);
    echo '<h1>SportsHub could not complete this request</h1>';
    echo '<p>Please check the server error log or ask your team leader for help.</p>';
}
set_exception_handler('handle_application_error');
