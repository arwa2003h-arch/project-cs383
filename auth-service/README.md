# auth-service (Norah) — port 8000, database `auth_db`

Register, login and tokens for Fresh Laundry. After login the user gets a **JWT**.
The other services check that token by themselves (same `JWT_SECRET`), so they never
call this service on every request.

## Run it

```
cd C:\laragon\www\project-cs383\auth-service
composer install
copy .env.example .env
php artisan key:generate
```

Open `.env`, check the DB lines (`DB_DATABASE=auth_db`) and **set `JWT_SECRET`**.
Make a secret with:

```
php -r "echo bin2hex(random_bytes(32));"
```

> Give the same `JWT_SECRET` to Arwa, Mariah and Layan. Send it privately, not on GitHub.

Create the database `auth_db` in Laragon (HeidiSQL / phpMyAdmin), then:

```
php artisan migrate
php artisan db:seed
php artisan serve --port=8000
php artisan test
```

Seeded accounts:

| Role | Email | Password |
|---|---|---|
| admin | admin@freshlaundry.test | Admin12345 |
| customer | customer@freshlaundry.test | Customer12345 |

## API

| Method | URL | Who | What |
|---|---|---|---|
| POST | `/api/register` | everyone | create a customer account, returns token |
| POST | `/api/login` | everyone | returns token (max 5 tries / minute) |
| GET | `/api/me` | logged in | current user |
| POST | `/api/logout` | logged in | tells the client to delete the token |

Register body: `name`, `email`, `phone` (optional), `password` (min 8), `password_confirmation`.
Login body: `email`, `password`.

Answer of register / login:

```json
{
  "user": { "id": 1, "name": "Norah", "email": "n@x.com", "phone": null, "role": "customer" },
  "token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 7200
}
```

For protected routes send the header: `Authorization: Bearer <token>`.

Errors: `401` no/bad/expired token or wrong login, `403` wrong role, `422` validation.

## What is inside the token

`sub` (user id), `name`, `email`, `role` (`customer` or `admin`), `iat`, `exp`, `jti`.

## Connect the other services

Follow `jwt-kit/README.md` (5 small steps per service). After that Arwa's TODO becomes:

```php
Route::middleware(['auth:api', 'role:admin'])->group(function () { ... });
```

and Arwa's notification controller can use `$request->user()->id` instead of `?user_id=`.

## Files I made (for the Development Journal)

- `app/Services/JwtService.php` — makes and checks HS256 tokens (no extra package)
- `app/Providers/AppServiceProvider.php` — the `jwt` guard (`auth:api`)
- `app/Http/Middleware/RoleMiddleware.php` — `role:admin`
- `app/Http/Controllers/AuthController.php` — register / login / me / logout
- `app/Http/Resources/UserResource.php`, `app/Models/User.php`
- migration (users + `phone` + `role`), `DatabaseSeeder`, tests in `tests/`
