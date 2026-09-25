<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AnalyticsController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}/skills', [StudentController::class, 'skills']);
    
    Route::post('/assessments', [AssessmentController::class, 'store']);
    Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
    Route::post('/assessments/{id}/reset', [AssessmentController::class, 'reset']);
    
    Route::get('/analytics/heatmap', [AnalyticsController::class, 'heatmap']);
    Route::get('/analytics/gap-analysis', [AnalyticsController::class, 'gapAnalysis']);
});

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});
