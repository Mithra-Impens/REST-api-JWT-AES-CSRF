<?php

class CsrfMiddleware
{
    public static function generateToken()
    {
        session_start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function handle()
    {
        // Make sure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $headers = getallheaders();

        $csrfToken = $headers['X-CSRF-Token'] ?? '';

        if (empty($csrfToken)) {
            Response::json(false, "CSRF token missing", [], 403);
        }

        if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            Response::json(false, "Invalid CSRF token", [], 403);
        }
    }
}