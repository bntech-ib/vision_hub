<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/email', [AuthController::class, 'loginWithEmail']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // API Token management
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::post('/tokens', [AuthController::class, 'createToken']);
    Route::delete('/tokens', [AuthController::class, 'revokeToken']);
});