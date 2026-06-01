<?php

class Encryption
{
    private static $cipher = "AES-256-CBC";

    // This ensures key is always exactly 32 bytes no matter what is in .env
    private static function getKey()
    {
        return hash('sha256', $_ENV['ENCRYPTION_KEY'], true);
    }

    public static function encrypt($data)
    {
        $key = self::getKey();

        $iv = random_bytes(16); // Always exactly 16 bytes

        $encrypted = openssl_encrypt(
            $data,
            self::$cipher,
            $key,
            0,
            $iv
        );

        // Combine IV + encrypted data, then Base64 encode
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encryptedData)
    {
        $key = self::getKey();

        $data = base64_decode($encryptedData);

        // Extract IV — always first 16 bytes
        $iv = substr($data, 0, 16);

        // Extract cipher text — everything after 16 bytes
        $encrypted = substr($data, 16);

        return openssl_decrypt(
            $encrypted,
            self::$cipher,
            $key,
            0,
            $iv
        );
    }
}