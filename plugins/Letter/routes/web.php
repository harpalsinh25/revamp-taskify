<?php

use Illuminate\Support\Facades\Route;
use Plugins\Letter\Controllers\LetterController;
use Plugins\Letter\Controllers\LetterTemplateController;

Route::middleware(['web', 'auth'])->group(function () {
    // Letter Templates Routes
    Route::prefix('/letter-templates')->name('letter-templates.')->group(function () {
        Route::get('/', [LetterTemplateController::class, 'index'])->name('index');
        Route::get('/list', [LetterTemplateController::class,'list'])->name('list');
        Route::get('/create', [LetterTemplateController::class, 'create'])->name('create');
        Route::post('/', [LetterTemplateController::class, 'store'])->name('store');
        Route::any('/sample-content', [LetterTemplateController::class, 'getSampleContent'])->name('sample_content');
        Route::get('/variables', [LetterTemplateController::class, 'getVariables'])->name('variables');
        Route::post('/preview/{id?}', [LetterTemplateController::class, 'preview'])->name('preview');
        Route::get('/{template}/edit', [LetterTemplateController::class, 'edit'])->name('edit');
        Route::get('/{template}', [LetterTemplateController::class, 'show'])->name('show');
        Route::put('/{template}', [LetterTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [LetterTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/duplicate', [LetterTemplateController::class, 'duplicate'])->name('duplicate');
    });

    // Letters Routes
    Route::prefix('letters')->name('letters.')->group(function () {
        Route::get('/', [LetterController::class, 'index'])->name('index');
        Route::get('/create', [LetterController::class, 'create'])->name('create');
        Route::post('/', [LetterController::class, 'store'])->name('store');
        Route::get('/{letter}', [LetterController::class, 'show'])->name('show');
        Route::get('/{letter}/edit', [LetterController::class, 'edit'])->name('edit');
        Route::put('/{letter}', [LetterController::class, 'update'])->name('update');
        Route::delete('/{letter}', [LetterController::class, 'destroy'])->name('destroy');

        // PDF & Preview
        Route::get('/preview', [LetterController::class, 'preview'])->name('preview');
        Route::get('/{letter}/pdf', [LetterController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{letter}/send-email', [LetterController::class, 'sendEmail'])->name('send-email');

        // Generate from template
        Route::get('/generate/{template}', [LetterController::class, 'generateFromTemplate'])->name('generate');
    });

    // API Routes for AJAX calls
    Route::prefix('api/letters')->name('api.letters.')->group(function () {
        Route::get('/templates', [LetterTemplateController::class, 'getTemplates'])->name('templates');
        Route::get('/variables', [LetterController::class, 'getVariables'])->name('variables');
        Route::post('/preview-content', [LetterController::class, 'previewContent'])->name('preview-content');
    });
});
