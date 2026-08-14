<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // API v1 Health & Status endpoint
    Route::get('/', function (): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => 'Steriqore Mobile API v1 is operational',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'endpoints' => [
                'status' => 'GET /api/v1',
                'register' => 'POST /api/v1/register',
                'login' => 'POST /api/v1/login',
                'user' => 'GET /api/v1/user (requires Bearer token)',
                'logout' => 'POST /api/v1/logout (requires Bearer token)',
            ],
        ]);
    });

    // Public auth routes for mobile
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes requiring Bearer token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Legacy / default fallback
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
