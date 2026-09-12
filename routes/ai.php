<?php

use App\Http\Controllers\AI\QuizGeneratorController;
use App\Http\Controllers\AI\TutorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('lessons/{lesson}/ai')->group(function () {
    Route::get('messages', [TutorController::class, 'index'])->name('ai.tutor.index');
    Route::post('messages', [TutorController::class, 'store'])
        ->middleware('throttle:ai-tutor')
        ->name('ai.tutor.store');

    Route::post('generate-quiz', [QuizGeneratorController::class, 'generate'])
        ->middleware(['role:instructor,admin', 'throttle:ai-quiz-generator'])
        ->name('ai.quiz-generator.generate');
});

Route::middleware(['auth', 'verified', 'role:instructor,admin'])->group(function () {
    Route::post('quizzes/{quiz}/ai/questions', [QuizGeneratorController::class, 'save'])
        ->name('ai.quiz-generator.save');
});
