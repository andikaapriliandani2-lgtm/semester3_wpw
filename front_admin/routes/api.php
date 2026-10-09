<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware(['abilities:products:read', 'role:admin,kasir'])->group(function (): void {
            Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        });

        Route::middleware(['abilities:products:write', 'role:admin'])->group(function (): void {
            Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        });
    });
});
