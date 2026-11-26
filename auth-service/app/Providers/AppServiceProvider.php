<?php

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
        //keep empty
    }

    public function boot(): void
    {
        // The "jwt" guard (used by auth:api).
        // It reads the token from "Authorization: Bearer <token>", checks it,
        // and builds the user from the token itself (no database needed).
        // That is why the SAME code works in every service.
        Auth::viaRequest('jwt', function (Request $request) { //create JWT
            $payload = JwtService::decode($request->bearerToken());//read header

            if (!$payload || !isset($payload['sub'])) {//invaled
                return null;
            }

            return new GenericUser([//user obj from token
                'id' => $payload['sub'],
                'name' => $payload['name'] ?? null,
                'email' => $payload['email'] ?? null,
                'role' => $payload['role'] ?? 'customer',//no role = customer
            ]);
        });
    }
}
