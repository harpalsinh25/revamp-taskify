<?php

use Illuminate\Support\Facades\Route;
use Plugins\TimeTracker\Controllers\DashboardController;
use Plugins\TimeTracker\Controllers\ManualTimeController;
use Plugins\TimeTracker\Controllers\ScreenShotController;
use Plugins\TimeTracker\Controllers\TimeAndAttendanceController;
use Plugins\TimeTracker\Controllers\TimeTrackerController;

Route::middleware(['web', 'auth'])->prefix('timetracker')->group(function () {
    // Main Time Tracker Routes
    Route::get('/', [TimeTrackerController::class, 'index'])->name('timetracker.index');
    Route::get('/dashboard-data', [DashboardController::class, 'index'])->name('timetracker.dashboard.data');
    Route::post('/track', [TimeTrackerController::class, 'storeTime']);
    Route::post('/screenshot', [TimeTrackerController::class, 'storeScreenshot']);
    Route::get('/configuration', [TimeTrackerController::class, 'configuration'])->name('timetracker.configuration');
    Route::put('/configuration/store', [TimeTrackerController::class, 'storeConfig'])->name('timetracker.configuration.store');

    // Time and Attendance Routes
    Route::prefix('time-and-attendance')->group(function () {
        Route::get('/', [TimeAndAttendanceController::class, 'index'])->name('time_and_attendance.index');
        Route::get('/data', [TimeAndAttendanceController::class, 'timeAndAttendanceData'])->name('time_and_attendance.data');
        Route::get('/timeline', [TimeAndAttendanceController::class, 'timeline'])->name('attendance.timeline');
    });

    // Screenshot Management Routes
    Route::prefix('screen-shots')->group(function () {
        Route::get('/', [ScreenShotController::class, 'index'])->name('timetracker.screenshots');
        Route::get('/data', [ScreenShotController::class, 'data'])->name('timetracker.screenshots.data');
        Route::get('/{id}', [ScreenShotController::class, 'show'])->name('timetracker.screenshots.show');
        Route::delete('/{id}', [ScreenShotController::class, 'destroy'])->name('timetracker.screenshots.destroy');
        Route::post('/bulk-delete', [ScreenShotController::class, 'bulkDelete'])->name('timetracker.screenshots.bulk-delete');
    });

    // Manual Time Entry Routes
    Route::prefix('manual-time')->group(function () {
        Route::get('/', [ManualTimeController::class, 'index'])->name('timetracker.manual_time.index');
        Route::get('/data', [ManualTimeController::class, 'data'])->name('timetracker.manual_time.data');
        Route::post('/store', [ManualTimeController::class, 'store'])->name('timetracker.manual_time.store');
        Route::get('/fetch', [ManualTimeController::class, 'fetch'])->name('timetracker.manual_time.fetch');
        Route::post('/approve', [ManualTimeController::class, 'approve'])->name('timetracker.manual_time.approve');
    });

    Route::get('/clear-timetracker-data', function () {
        \Plugins\TimeTracker\Models\Screenshot::truncate();
        \Plugins\TimeTracker\Models\TimeTrackerActivityLog::truncate();

        return response()->json(['status' => 'success', 'message' => 'TimeTracker data cleared.']);
    });
});
