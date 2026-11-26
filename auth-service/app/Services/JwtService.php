<?php

namespace App\Services;

//JWT==header.payload.signature
//signature made with secret all services share (JWT_SECRET)
//every service check the token w/o auth-service.
class JwtService
{
    
    public static function encode(array $payload): string //create token, static = w/o new
    {
        $now = time();//current

        $payload = array_merge([//default
            'iat' => $now,//issued at 
            'exp' => $now + self::ttl() * 60, //expires
            'jti' => bin2hex(random_bytes(8)),//random+unique id
        ], $payload);//data is passed,not defaulr

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];//JWT header

        $unsigned = self::base64UrlEncode(json_encode($header))
            . '.' . self::base64UrlEncode(json_encode($payload));//Json 

        return $unsigned . '.' . self::sign($unsigned);//signature
    }


    public static function decode(?string $token): ?array//token, payload or null
    {
        if (!$token) {
            return null;
        }

        $parts = explode('.', $token);//cut

        if (count($parts) !== 3) { // 3 parts(header, payload, signature
            return null;
        }

        [$header64, $payload64, $signature] = $parts;//assign name

        $header = json_decode(self::base64UrlDecode($header64), true);//header as array

        //only accept HS256
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        // compare signature and calculate from header+payload
        if (!hash_equals(self::sign($header64 . '.' . $payload64), $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);//payload as array

        if (!is_array($payload) || !isset($payload['exp']) || $payload['exp'] < time()) {//unreadable - no exp-expired
            return null;//
        }

        return $payload;// valid
    }

    // token lifetime in minutes
    public static function ttl(): int
    {
        return (int) config('services.jwt.ttl', 120);
    }

    private static function sign(string $data): string
    {
        $secret = config('services.jwt.secret'); //JWT_SECRET in .env

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
    }//changge +,= to - _ and decode back
}
