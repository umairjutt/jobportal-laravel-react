<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ResumeController;
use Illuminate\Support\Facades\Route;

// Observability
Route::get('metrics', [MetricsController::class, 'index']);

// Public
Route::get('jobs', [JobController::class, 'index']);
Route::get('jobs/{slug}', [JobController::class, 'show']);

// Auth
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Candidate
    Route::middleware('role:candidate')->group(function () {
        Route::post('resumes', [ResumeController::class, 'upload']);
        Route::post('jobs/{job}/apply', [ApplicationController::class, 'apply']);
        Route::get('applications/mine', [ApplicationController::class, 'mine']);
    });

    // Recruiter
    Route::middleware('role:recruiter')->group(function () {
        Route::post('jobs', [JobController::class, 'store']);
        Route::get('jobs/{job}/applications', [ApplicationController::class, 'forRecruiter']);
        Route::post('applications/{application}/transition', [ApplicationController::class, 'transition']);
        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard']);
    });

    // Chat (any authenticated)
    Route::get('chat/conversations', [ChatController::class, 'conversations']);
    Route::post('chat/conversations/with/{user}', [ChatController::class, 'startWith']);
    Route::get('chat/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('chat/conversations/{conversation}/messages', [ChatController::class, 'send']);
    Route::post('chat/conversations/{conversation}/typing', [ChatController::class, 'typing']);
    Route::post('chat/conversations/{conversation}/read', [ChatController::class, 'markRead']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
});
