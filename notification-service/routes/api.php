<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// TODO: when Norah's login is ready, put these routes inside:
//       Route::middleware('auth:api')->group(function () { ... });

// Called by the Order service
Route::post('/notifications', [NotificationController::class, 'store']);

// Used by the customer (through the website)
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
