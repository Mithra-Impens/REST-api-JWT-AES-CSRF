<?php

class AuthMiddleware
{
    public static function handle()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Response::json(false, "Authorization header missing", [], 401);
        }

        $authHeader = $headers['Authorization'];

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::json(false, "Invalid token format", [], 401);
        }

        $accessToken = $matches[1];
        $decoded = JWT::validate($accessToken);
        
        if (!$decoded) {
         Response::json(false, "Invalid or expired token", [], 401);
        }

        if (!isset($decoded['type']) || $decoded['type'] !== 'access') {
            Response::json(false, "Invalid token type", [], 401);
        }

        $userModel = new User();

        $user = $userModel->findById($decoded['user_id']);

        if (!$user) {
         Response::json(false, "User not found", [], 401);
        }

        $_REQUEST['user'] = $decoded;
    }
}