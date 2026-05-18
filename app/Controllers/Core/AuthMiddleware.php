<?php

class AuthMiddleware
{
    public static function isLoggedIn(): bool
    {
        return !empty(Session::get('user_id'));
    }

    public static function userRole(): ?string
    {
        $role = Session::get('role');

        return is_string($role) ? $role : null;
    }

    public static function isAdmin(): bool
    {
        return self::userRole() === 'admin';
    }

    public static function isMember(): bool
    {
        return self::userRole() === 'member';
    }

    public static function requireAuth(bool $api = false): void
    {
        if (self::isLoggedIn()) {
            return;
        }

        self::deny($api, 'Authentication required.', 401);
    }

    public static function requireRole(string $role, bool $api = false): void
    {
        self::requireAuth($api);

        if (self::userRole() === $role) {
            return;
        }

        self::deny($api, 'You do not have permission to access this page.', 403);
    }

    private static function deny(bool $api, string $message, int $status): void
    {
        http_response_code($status);

        if ($api) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message], JSON_THROW_ON_ERROR);
            exit;
        }

        Session::flash('error', $message);
        header('Location: ' . base_url('/login'));
        exit;
    }
}
