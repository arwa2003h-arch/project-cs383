<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $extra = []): array
    {
        return array_merge([
            'name'                  => 'Norah',
            'email'                 => 'norah@example.com',
            'phone'                 => '0500000000',
            'password'              => 'secret1234',
            'password_confirmation' => 'secret1234',
        ], $extra);
    }

    public function test_register_creates_a_customer_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', $this->validData());

        $response->assertStatus(201)
            ->assertJsonPath('user.email', 'norah@example.com')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'norah@example.com']);
        $this->assertNotNull(JwtService::decode($response->json('token')));
    }

    public function test_register_cannot_make_yourself_admin(): void
    {
        $response = $this->postJson('/api/register', $this->validData(['role' => 'admin']));

        $response->assertStatus(201)->assertJsonPath('user.role', 'customer');
    }

    public function test_register_checks_the_data(): void
    {
        User::factory()->create(['email' => 'norah@example.com']);

        $this->postJson('/api/register', $this->validData())->assertStatus(422);        // email taken
        $this->postJson('/api/register', $this->validData([
            'email' => 'new@example.com',
            'password_confirmation' => 'different',
        ]))->assertStatus(422);                                                        // passwords differ
        $this->postJson('/api/register', [])->assertStatus(422);                        // nothing sent
    }

    public function test_login_works_with_the_right_password(): void
    {
        User::factory()->create(['email' => 'norah@example.com', 'password' => 'secret1234']);

        $response = $this->postJson('/api/login', [
            'email' => 'norah@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['user', 'token', 'token_type', 'expires_in']);
    }

    public function test_login_fails_with_a_wrong_password_or_unknown_email(): void
    {
        User::factory()->create(['email' => 'norah@example.com', 'password' => 'secret1234']);

        $this->postJson('/api/login', ['email' => 'norah@example.com', 'password' => 'wrong'])
            ->assertStatus(401)->assertJsonPath('message', 'Invalid email or password');

        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'secret1234'])
            ->assertStatus(401)->assertJsonPath('message', 'Invalid email or password');
    }

    // Laravel remembers the logged in user between requests inside ONE test.
    // In real life every request is new, so here we reset the guards before each call.
    private function callApi(string $uri, ?string $token = null)
    {
        $this->app['auth']->forgetGuards();

        return $this->getJson($uri, $token ? ['Authorization' => "Bearer $token"] : []);
    }

    public function test_me_needs_a_valid_token(): void
    {
        $token = $this->postJson('/api/register', $this->validData())->json('token');

        $this->callApi('/api/me', $token)
            ->assertStatus(200)->assertJsonPath('data.email', 'norah@example.com');

        $this->callApi('/api/me')->assertStatus(401);
        $this->callApi('/api/me', 'not-a-token')->assertStatus(401);
        $this->callApi('/api/me', JwtService::encode(['sub' => 1, 'exp' => time() - 5]))
            ->assertStatus(401);
    }

    public function test_role_admin_middleware(): void
    {
        // a small route only for this test
        Route::middleware(['auth:api', 'role:admin'])->get('/api/_admin-only', fn () => ['ok' => true]);

        $customer = JwtService::encode(['sub' => 1, 'role' => 'customer']);
        $admin    = JwtService::encode(['sub' => 2, 'role' => 'admin']);

        $this->callApi('/api/_admin-only')->assertStatus(401);
        $this->callApi('/api/_admin-only', $customer)->assertStatus(403);
        $this->callApi('/api/_admin-only', $admin)->assertStatus(200);
    }
}