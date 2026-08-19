<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LabelController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\UsageController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // API v1 Health & Status endpoints
    $healthHandler = function (): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => 'Steriqore Mobile API v1 is operational',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'endpoints' => [
                'status' => 'GET /api/v1',
                'health' => 'GET /api/v1/health',
                'register' => 'POST /api/v1/register (or /api/v1/auth/register)',
                'login' => 'POST /api/v1/login (or /api/v1/auth/login)',
                'user' => 'GET /api/v1/user (or /api/v1/auth/me) [requires Bearer token]',
                'logout' => 'POST /api/v1/logout (or /api/v1/auth/logout) [requires Bearer token]',
                'alerts' => 'GET /api/v1/alerts [requires Bearer token]',
                'stock_levels' => 'GET /api/v1/stock-levels [requires Bearer token]',
                'patients' => 'GET /api/v1/patients [requires Bearer token]',
                'labels' => 'GET /api/v1/labels [requires Bearer token]',
                'label_detail' => 'GET /api/v1/labels/{code} [requires Bearer token]',
                'record_usage' => 'POST /api/v1/labels/{id}/usage [requires Bearer token]',
                'practitioner_history' => 'GET /api/v1/practitioner/usages [requires Bearer token]',
            ],
        ]);
    };

    Route::get('/', $healthHandler);
    Route::get('/health', $healthHandler);

    // Public auth routes for mobile (both /auth/* and direct /* aliases)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Protected routes requiring Bearer token
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile & Logout (both /auth/* and direct /* aliases)
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/auth/me', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard & Cycle inspection
        Route::get('/alerts', [DashboardController::class, 'alerts']);
        Route::get('/stock-levels', [DashboardController::class, 'stockLevels']);
        Route::get('/cycles/{id}', [DashboardController::class, 'cycleDetail']);
        Route::get('/cycles/{id}/items', [DashboardController::class, 'cycleItems']);
        Route::get('/cycles/{id}/attachments', [DashboardController::class, 'cycleAttachments']);

        // Patient directory routes (read-only for practitioners)
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);

        // Scanner & Label traceability routes
        Route::get('/labels', [LabelController::class, 'index']);
        Route::get('/labels/{code}', [LabelController::class, 'show']);

        // Usage recording & history routes
        Route::post('/labels/{labelId}/usage', [LabelController::class, 'recordUsage']);
        Route::get('/practitioner/usages', [UsageController::class, 'practitionerHistory']);
        Route::get('/usages', [UsageController::class, 'practitionerHistory']);
    });
});

// Legacy / default fallback
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
