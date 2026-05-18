<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

function app_path(string $path = ''): string
{
    return APP_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function root_path(string $path = ''): string
{
    return ROOT_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function app_config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['app_config'] ?? [];

    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

function base_url(string $path = ''): string
{
    $base = rtrim(app_config('base_url', ''), '/');
    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '' : $path);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

spl_autoload_register(function (string $class): void {
    $class = ltrim($class, '\\');
    $paths = [
        app_path('Controllers/' . $class . '.php'),
        app_path('Controllers/Core/' . $class . '.php'),
        app_path('Controllers/Api/' . $class . '.php'),
        app_path('Models/' . $class . '.php'),
        app_path('Models/Core/' . $class . '.php'),
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$GLOBALS['app_config'] = require root_path('config/app.php');
date_default_timezone_set(app_config('timezone', 'Asia/Dhaka'));

Session::start(app_config());

if (!AuthMiddleware::isLoggedIn() && !empty($_COOKIE['remember_token'])) {
    $rememberedUser = (new User())->findByRememberToken((string) $_COOKIE['remember_token']);

    if ($rememberedUser !== null) {
        Session::regenerate();
        Session::set('user_id', (int) $rememberedUser['id']);
        Session::set('name', $rememberedUser['name']);
        Session::set('role', $rememberedUser['role']);
    }
}

$routes = require root_path('config/routes.php');
$router = new Router($routes);
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = '/online-car-rent/public';

if (str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}

$requestUri = $requestUri ?: '/';

$router->dispatch($_SERVER['REQUEST_METHOD'], $requestUri);
