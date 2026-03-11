<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sync/meals', [SyncController::class, 'syncMeals']);
    Route::get('/sync/students', [SyncController::class, 'getStudents']);
});

Route::middleware('web', 'auth')->prefix('sync')->group(function () {
    Route::post('/meals', [SyncController::class, 'syncMeals']);
    Route::get('/students', [SyncController::class, 'getStudents']);
    Route::get('/logs', [SyncController::class, 'syncLogs']);
});
