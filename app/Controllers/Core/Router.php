<?php

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        $path = $path === '//' ? '/' : $path;

        foreach ($this->routes as $route) {
            [$routeMethod, $pattern, $handler] = $route;

            if (strtoupper($method) !== strtoupper($routeMethod)) {
                continue;
            }

            $params = $this->match($pattern, $path);

            if ($params === null) {
                continue;
            }

            $this->handleAuth($route);
            $this->invoke($handler, $params, !empty($route['api']));
            return;
        }

        $this->notFound($this->expectsJson($path));
    }

    private function match(string $pattern, string $path): ?array
    {
        $keys = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $matches) use (&$keys) {
            $keys[] = $matches[1];
            return '([^/]+)';
        }, '/' . trim($pattern, '/'));

        $regex = '#^' . ($regex === '/' ? '/' : rtrim($regex, '/')) . '$#';
        $path = $path === '/' ? '/' : rtrim($path, '/');

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        array_shift($matches);

        return array_combine($keys, $matches) ?: [];
    }

    private function handleAuth(array $route): void
    {
        $auth = $route['auth'] ?? null;
        $api = !empty($route['api']);

        if ($auth === true) {
            AuthMiddleware::requireAuth($api);
            return;
        }

        if (is_string($auth)) {
            AuthMiddleware::requireRole($auth, $api);
        }
    }

    private function invoke(string $handler, array $params, bool $api): void
    {
        [$controllerName, $methodName] = explode('@', $handler, 2);

        if (!class_exists($controllerName)) {
            $this->notImplemented($api, 'Controller is not implemented yet.');
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            $this->notImplemented($api, 'Controller method is not implemented yet.');
        }

        $controller->{$methodName}(...array_values($params));
    }

    private function notFound(bool $api): void
    {
        http_response_code(404);

        if ($api) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Route not found.'], JSON_THROW_ON_ERROR);
            return;
        }

        echo '404 - Page not found.';
    }

    private function notImplemented(bool $api, string $message): void
    {
        http_response_code(501);

        if ($api) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message], JSON_THROW_ON_ERROR);
            exit;
        }

        echo e($message);
        exit;
    }

    private function expectsJson(string $path): bool
    {
        return str_starts_with($path, '/api/');
    }
}
