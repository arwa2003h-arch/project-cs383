<?php

namespace App\Services;

// Very small JWT (HS256) helper. No extra package needed.
// A JWT is 3 parts joined by dots:  header.payload.signature
// The signature is made with a secret that ALL services share (JWT_SECRET),
// so every service can check the token by itself, without calling auth-service.
class JwtService
{
    // Make a token from the data (payload). Adds iat / exp / jti automatically.
    public static function encode(array $payload): string
    {
        $now = time();

        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + self::ttl() * 60,
            'jti' => bin2hex(random_bytes(8)),
        ], $payload);

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];

        $unsigned = self::base64UrlEncode(json_encode($header))
            . '.' . self::base64UrlEncode(json_encode($payload));

        return $unsigned . '.' . self::sign($unsigned);
    }

    // Check a token. Returns the payload (array) if it is valid, or null if not
    // (wrong format, wrong signature, or expired).
    public static function decode(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header64, $payload64, $signature] = $parts;

        $header = json_decode(self::base64UrlDecode($header64), true);

        // only accept HS256 (blocks the "alg: none" trick)
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        // hash_equals compares in constant time (safer than ===)
        if (!hash_equals(self::sign($header64 . '.' . $payload64), $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);

        if (!is_array($payload) || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    // token lifetime in minutes
    public static function ttl(): int
    {
        return (int) config('services.jwt.ttl', 120);
    }

    private static function sign(string $data): string
    {
        $secret = config('services.jwt.secret');

        if (!$secret) {
            throw new \RuntimeException('JWT_SECRET is not set in .env');
        }

        return self::base64UrlEncode(hash_hmac('sha256', $data, $secret, true));
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}
