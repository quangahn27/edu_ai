<?php

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:instructor,admin'])->group(function () {
    Route::post('courses/{course}/quizzes', [QuizController::class, 'storeForCourse'])->name('quizzes.store-for-course');
    Route::post('lessons/{lesson}/quizzes', [QuizController::class, 'storeForLesson'])->name('quizzes.store-for-lesson');
    Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::patch('quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');

    Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::patch('quizzes/{quiz}/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::patch('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
});
