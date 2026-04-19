<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base.'/'.ltrim($path, '/') : $base;
    }
}

$configFile = base_path('config/config.php');
if (!file_exists($configFile)) {
    $example = base_path('config/config.example.php');
    if (!file_exists($example)) {
        http_response_code(500);
        exit('Missing config files.');
    }
    copy($example, $configFile);
}

$config = require $configFile;
date_default_timezone_set($config['timezone'] ?? 'UTC');

session_name($config['session_name'] ?? 'chatting_pure_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_save_path(base_path('storage/sessions'));
session_start();

require_once __DIR__.'/services/db.php';
require_once __DIR__.'/services/security.php';
require_once __DIR__.'/services/auth.php';
require_once __DIR__.'/services/chat.php';
require_once __DIR__.'/services/admin.php';
require_once __DIR__.'/services/router.php';

refresh_last_seen();
