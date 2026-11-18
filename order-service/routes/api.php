<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// ===== Laundry services (Arwa) =====

// Everyone can read the services
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);

// Admin only
// TODO: when Norah finishes the login, put these inside:
//       Route::middleware(['auth:api', 'role:admin'])->group(function () { ... });
Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
Route::patch('/services/{id}/activate', [ServiceController::class, 'activate']);

// ===== Orders (Mariah) =====
// Mariah adds her routes here
