<?php

use Illuminate\Support\Facades\Route;
use Plugins\SocialMediaManagement\Controllers\SocialMediaController;
use Plugins\SocialMediaManagement\Controllers\SocialSettingsController;

Route::middleware(['web', 'auth', 'customcan:manage_posts'])->group(function () {
    Route::prefix('social-media-scheduler')->group(function () {
        Route::get('/', [SocialMediaController::class, 'index'])->name('social.index');
        Route::get('/create', [SocialMediaController::class, 'create'])->name('social.create')->middleware('customcan:create_posts');
        Route::get('/edit/{id}', [SocialMediaController::class, 'edit'])->name('social.edit')->middleware('customcan:edit_posts');
        Route::post('/update/{id}', [SocialMediaController::class, 'update'])->name('social.update');
        Route::post('/post', [SocialMediaController::class, 'post'])->name('social.post');
        Route::get('/list', [SocialMediaController::class, 'list'])->name('social.list');
        Route::post('/publish-now/{id}', [SocialMediaController::class, 'publishNow'])->name('social.publish-now');
        Route::delete('/destroy/{id}', [SocialMediaController::class, 'destroy'])->name('social.destroy')->middleware('customcan:delete_posts');
        Route::post('/destroy_multiple', [SocialMediaController::class, 'destroy_multiple'])->name('social.destroy_multiple')->middleware('customcan:delete_posts');
        Route::post('/ai/generate-caption', [SocialMediaController::class, 'generateSocialCaption'])->name('social.generate_caption');
        Route::get('/posts/{id}', [SocialMediaController::class, 'show'])->name('social.show');
        Route::delete('/destroy-media/{id}', [SocialMediaController::class, 'destroyMedia'])->name('social.destroy_media')->middleware('customcan:edit_posts');
        Route::get('/post-detail/{id}', [SocialMediaController::class, 'getPostDetail'])->name('social.post.detail');

        // Calendar routes
        Route::get('/calendar', [SocialMediaController::class, 'calendar'])->name('social.calendar');
        Route::get('/calendar-data', [SocialMediaController::class, 'getCalendarData'])->name('social.calendar.data');
        Route::get('/calendar-stats', [SocialMediaController::class, 'getCalendarStats'])->name('social.calendar.stats');
        Route::get('/posts-by-date', [SocialMediaController::class, 'getPostsByDate'])->name('social.posts.by_date');

        // Analytics routes
        Route::get('/analytics', [SocialMediaController::class, 'analytics'])->name('social.analytics');
        Route::get('/analytics/data', [SocialMediaController::class, 'getAnalyticsData'])->name('social.analytics.data');
        Route::get('/analytics/trends', [SocialMediaController::class, 'getPostingTrends'])->name('social.analytics.trends');

        Route::get('/social-settings', [SocialSettingsController::class, 'index'])->name('social.settings.index');
        Route::post('/social-settings/update', [SocialSettingsController::class, 'update'])->name('social.settings.update');
    });
});
