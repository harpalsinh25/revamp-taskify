<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Plugins\TimeTracker\Controllers\TimeTrackerController;

// Time Tracker API Routes
Route::prefix('api/plugin/timetracker')->middleware(['api', 'isDevice:electron', 'isApi'])->group(function () {

    // Authentication routes (no middleware needed for login)
    Route::post('/login', [UserController::class, 'authenticate']);
    Route::middleware('auth:sanctum')->get('/validate-token', [UserController::class, 'validateToken']);

    // Configuration route (public access)
    Route::get('/load-config', [TimeTrackerController::class, 'loadConfig']);

    // Protected routes (require authentication)
    Route::middleware(['multiguard', 'custom-verified'])->group(function () {
        // Core time tracking endpoints matching Express.js
        Route::post('/log-update', [TimeTrackerController::class, 'logUpdate']);
        Route::post('/upload-screenshot', [TimeTrackerController::class, 'uploadScreenshot']);

        // Optional: Additional endpoints for better functionality
        Route::get('/time-entries', [TimeTrackerController::class, 'getTimeEntries']); // Keep if needed
        Route::get('/activity-logs', [TimeTrackerController::class, 'getActivityLogs']); // New endpoint
        Route::get('/screenshots/{userId?}', [TimeTrackerController::class, 'getScreenshots']); // New endpoint
    });
});
