<?php

// Replace app/Providers/AppServiceProvider.php with this file
// (same file as in auth-service).

namespace App\Providers;

use App\Services\JwtService;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::viaRequest('jwt', function (Request $request) {
            $payload = JwtService::decode($request->bearerToken());

            if (!$payload || !isset($payload['sub'])) {
                return null;
            }

            return new GenericUser([
                'id'    => $payload['sub'],
                'name'  => $payload['name'] ?? null,
                'email' => $payload['email'] ?? null,
                'role'  => $payload['role'] ?? 'customer',
            ]);
        });
    }
}
