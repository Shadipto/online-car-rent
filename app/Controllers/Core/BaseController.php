<?php

abstract class BaseController
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = app_path('Views/' . str_replace('.', '/', $view) . '.php');
        $layoutPath = app_path('Views/layouts/' . $layout . '.php');

        if (!is_file($viewPath)) {
            $this->abort(500, 'View not found.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (!is_file($layoutPath)) {
            echo $content;
            return;
        }

        require $layoutPath;
    }

    protected function redirect(string $path): void
    {
        $target = preg_match('#^https?://#i', $path) ? $path : base_url($path);
        header('Location: ' . $target);
        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_THROW_ON_ERROR);
        exit;
    }

    protected function requireAuth(bool $api = false): void
    {
        AuthMiddleware::requireAuth($api);
    }

    protected function requireRole(string $role, bool $api = false): void
    {
        AuthMiddleware::requireRole($role, $api);
    }

    protected function verifyCsrf(bool $api = false): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (Session::validateCsrf(is_string($token) ? $token : null)) {
            return;
        }

        if ($api) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 419);
        }

        Session::flash('error', 'Invalid form token. Please try again.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    protected function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(Session::csrfToken()) . '">';
    }

    protected function abort(int $status = 404, string $message = 'Not found.'): void
    {
        http_response_code($status);
        echo e($message);
        exit;
    }
}
