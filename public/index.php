<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class) && is_file(__DIR__.'/../.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    $appUrlPath = parse_url((string) ($_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? ''), PHP_URL_PATH);
    if (is_string($appUrlPath) && $appUrlPath !== '' && $appUrlPath !== '/') {
        $_SERVER['SCRIPT_NAME'] = rtrim($appUrlPath, '/').'/index.php';
        $_SERVER['PHP_SELF'] = rtrim($appUrlPath, '/').'/index.php';
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
