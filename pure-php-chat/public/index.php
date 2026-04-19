<?php

declare(strict_types=1);

require __DIR__.'/../src/bootstrap.php';

$route = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$prefix = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
if ($prefix !== '/' && str_starts_with($route, $prefix)) {
    $route = substr($route, strlen($prefix)) ?: '/';
}

route_dispatch($method, $route);
