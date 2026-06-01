<?php

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $email, $password)
    {
        $sql = "INSERT INTO users(name, email, password)
                VALUES(:name, :email, :password)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password
        ]);
    }

public function findById($id)
{
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':id' => $id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function saveRefreshToken($userId, $token, $expiresAt)
    {
        // One refresh token per user — delete old one first
        $this->conn
             ->prepare("DELETE FROM refresh_tokens WHERE user_id = :user_id")
             ->execute([':user_id' => $userId]);

        $sql  = "INSERT INTO refresh_tokens(user_id, token, expires_at)
                 VALUES(:user_id, :token, :expires_at)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id'    => $userId,
            ':token'      => $token,
            ':expires_at' => $expiresAt
        ]);
    }

    public function findRefreshToken($token)
    {
        $sql  = "SELECT * FROM refresh_tokens WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteRefreshToken($token)
    {
        $sql  = "DELETE FROM refresh_tokens WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }


}