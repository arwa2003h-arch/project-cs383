<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//public
Route::post('/register', [AuthController::class, 'register']);
// max 5 login tries for minute
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
//needs valid token
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
