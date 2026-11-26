# JWT kit — to connect order-service / notification-service to the login

Do these 5 steps in each service (order-service, notification-service).
Nothing else in the service needs to change.

1. Copy `app/Services/JwtService.php` and `app/Http/Middleware/RoleMiddleware.php`
   into the same folders in the service.

2. Replace `app/Providers/AppServiceProvider.php` with `jwt-kit/AppServiceProvider.php`.

3. In `bootstrap/app.php`, inside `withMiddleware(...)` add:

   ```php
   $middleware->alias([
       'role' => \App\Http\Middleware\RoleMiddleware::class,
   ]);
   ```

4. In `config/auth.php`, inside `'guards' => [ ... ]` add:

   ```php
   'api' => [
       'driver' => 'jwt',
       'provider' => 'users',
   ],
   ```

   In `config/services.php` (before the closing `];`) add:

   ```php
   'jwt' => [
       'secret' => env('JWT_SECRET'),
       'ttl'    => env('JWT_TTL', 120),
   ],
   ```

5. In `.env` add the SAME secret that is in auth-service's `.env`:

   ```
   JWT_SECRET=...same value everywhere...
   ```

Then you can protect routes:

```php
// any logged in user
Route::middleware('auth:api')->group(function () { ... });

// admin only
Route::middleware(['auth:api', 'role:admin'])->group(function () { ... });
```

In a controller, the logged in user is: `$request->user()->id`, `->role`, `->name`, `->email`
(it comes from the token, no database query).
