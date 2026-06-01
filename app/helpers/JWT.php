<?php

class JWT
{
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function generate($payload)
    {
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);

        $payload['iat'] = time();
        $payload['exp'] = time() + (int)$_ENV['JWT_EXPIRY'];
        $payload['type'] = 'access';

        $headerEncoded = self::base64UrlEncode($header);
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
// Hash-based Message Authentication Code
        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $_ENV['JWT_SECRET'],
            true
        );

        return "$headerEncoded.$payloadEncoded." . self::base64UrlEncode($signature);
    }

    public static function generateRefreshToken()
{
    return bin2hex(random_bytes(40)); // random string,
}

    public static function validate($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $_ENV['JWT_SECRET'],
            true
        );

        $validSignature = self::base64UrlEncode($signature);

        if (!hash_equals($validSignature, $signatureEncoded)) {
            return false;
        }

        $payload = json_decode(
            self::base64UrlDecode($payloadEncoded),
            true
        );

        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}