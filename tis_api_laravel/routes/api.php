<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FailureController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api.key')->group(function () {
    // Failures
    Route::post('/failures', [FailureController::class, 'store']);
    Route::get('/failures', [FailureController::class, 'index']);
    Route::get('/failures/{sessionId}', [FailureController::class, 'show']);

    // Files
    Route::post('/files', [FileController::class, 'store']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/analytics/trend', [DashboardController::class, 'trend']);
    Route::get('/analytics/pareto', [DashboardController::class, 'pareto']);

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => config('app.version', '1.0.0')
        ]);
    });
});

