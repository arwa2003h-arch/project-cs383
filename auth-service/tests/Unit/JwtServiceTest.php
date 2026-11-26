<?php

namespace Tests\Unit;

use App\Services\JwtService;
use Tests\TestCase;

class JwtServiceTest extends TestCase
{
    public function test_a_token_can_be_made_and_read_back(): void
    {
        $token = JwtService::encode(['sub' => 7, 'role' => 'admin']);

        $payload = JwtService::decode($token);

        $this->assertSame(7, $payload['sub']);
        $this->assertSame('admin', $payload['role']);
    }

    public function test_a_changed_token_is_rejected(): void
    {
        $token = JwtService::encode(['sub' => 7, 'role' => 'customer']);
        [$h, $p, $s] = explode('.', $token);

        // attacker changes the role inside the payload
        $fake = rtrim(strtr(base64_encode(json_encode(['sub' => 7, 'role' => 'admin', 'exp' => time() + 999])), '+/', '-_'), '=');

        $this->assertNull(JwtService::decode("$h.$fake.$s"));
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $token = JwtService::encode(['sub' => 1, 'exp' => time() - 10]);

        $this->assertNull(JwtService::decode($token));
    }

    public function test_garbage_is_rejected(): void
    {
        $this->assertNull(JwtService::decode(null));
        $this->assertNull(JwtService::decode('abc'));
        $this->assertNull(JwtService::decode('a.b.c'));
    }
}
