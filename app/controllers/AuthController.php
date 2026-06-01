<?php

class AuthController
{
    public function register()
    {
        $data = $_REQUEST['body'];

        if (
            empty($data['name']) ||
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::json(false, "All fields are required", [], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::json(false, "Invalid email format", [], 400);
        }

        if (strlen($data['password']) < 6) {
            Response::json(false, "Password must be at least 6 characters", [], 400);
        }

        $userModel = new User();

        $existingUser = $userModel->findByEmail($data['email']);

        if ($existingUser) {
            Response::json(false, "Email already exists", [], 409);
        }

        $hashedPassword = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $userModel->create(
            $data['name'],
            $data['email'],
            $hashedPassword
        );

        Response::json(true, "User registered successfully");
    }

    public function login()
    {
        $data = $_REQUEST['body'];

        if (
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::json(false, "Email and password required", [], 400);
        }

        $userModel = new User();

        $user = $userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::json(false, "Invalid credentials", [], 401);
        }

    
        $accessToken = JWT::generate([
        'user_id' => $user['id'],
        'email'   => $user['email']
    ]);

        $refreshToken = JWT::generateRefreshToken();

        $expiresAt = date('Y-m-d H:i:s', time() + (int)$_ENV['JWT_REFRESH_EXPIRY']);
        $userModel->saveRefreshToken($user['id'], $refreshToken, $expiresAt);
        
        setcookie('refresh_token', $refreshToken, 
        time() + (int)$_ENV['JWT_REFRESH_EXPIRY'], '/', '', false, true);
        
        $csrfToken = CsrfMiddleware::generateToken();

        Response::json(true, "Login successful", [
            "access_token"         => $accessToken,
            "access_token_expiry"  => "15 seconds",
            "csrf_token"          => $csrfToken
        ]);
    }

    public function refresh()
{
    // Read from cookie, 
    if (empty($_COOKIE['refresh_token'])) {
        Response::json(false, "Refresh token missing", [], 401);
    }

    $refreshToken = $_COOKIE['refresh_token'];

    $userModel   = new User();
    $storedToken = $userModel->findRefreshToken($refreshToken);

    // Not found in DB
    if (!$storedToken) {
        Response::json(false, "Invalid refresh token. Please login again.", [], 401);
    }

    // DB level expiry check
    if (strtotime($storedToken['expires_at']) < time()) {
        $userModel->deleteRefreshToken($refreshToken);
        setcookie('refresh_token', '', time() - 3600, '/'); // clear cookie
        Response::json(false, "Session expired. Please login again.", [], 401);
    }

    // Load user
    $user = $userModel->findById($storedToken['user_id']);
    if (!$user) {
        Response::json(false, "User not found", [], 401);
    }

    // Generate new access token
    $newAccessToken = JWT::generate([
        'user_id' => $user['id'],
        'email'   => $user['email']
    ]);

    // Rotate refresh token — old one deleted, new one issued
    $newRefreshToken = JWT::generateRefreshToken();
    $expiresAt = date('Y-m-d H:i:s', time() + (int)$_ENV['JWT_REFRESH_EXPIRY']);

    $userModel->deleteRefreshToken($refreshToken);
    $userModel->saveRefreshToken($user['id'], $newRefreshToken, $expiresAt);

    setcookie('refresh_token', $newRefreshToken, 
    time() + (int)$_ENV['JWT_REFRESH_EXPIRY'], '/', '', false, true);

    Response::json(true, "Token refreshed successfully", [
        "access_token"        => $newAccessToken,
        "access_token_expiry" => "15 seconds"
    ]);
}

public function logout()
    {
        
        if (!empty($_COOKIE['refresh_token'])) {
            $userModel = new User();
            $userModel->deleteRefreshToken($_COOKIE['refresh_token']);
        }
 
        setcookie('refresh_token', '', time() - 3600, '/');
 
        // Destroy CSRF session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();            
        session_destroy();          
        session_regenerate_id(true); 
 
        Response::json(true, "Logged out successfully");
    }

}