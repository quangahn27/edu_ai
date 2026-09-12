<?php

use App\Http\Controllers\AI\EssayGradingController as AiEssayGradingController;
use App\Http\Controllers\EssayGradingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:instructor,admin'])->group(function () {
    Route::get('essays', [EssayGradingController::class, 'index'])->name('essays.index');
    Route::get('answers/{answer}', [EssayGradingController::class, 'show'])->name('essays.show');
    Route::patch('answers/{answer}/grade', [EssayGradingController::class, 'update'])->name('essays.update');

    Route::post('answers/{answer}/ai/essay-grading', [AiEssayGradingController::class, 'generate'])
        ->middleware('throttle:ai-essay-grading')
        ->name('ai.essay-grading.generate');
});
