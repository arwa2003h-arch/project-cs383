<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
//run before controller, check if user has the right role
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {//no user or not allowed role
            return response()->json(['message' => 'Forbidden'], 403);//stop
        }

        return $next($request);
    }
}
