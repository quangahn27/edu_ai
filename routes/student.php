<?php

use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('student/courses', [CourseController::class, 'index'])->name('student.courses.index');
    Route::get('student/courses/{course}', [CourseController::class, 'show'])->name('student.courses.show');
    Route::post('student/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('student.courses.enroll');

    Route::get('learn/{lesson}', [LearnController::class, 'show'])->name('learn.show');
    Route::post('learn/{lesson}/complete', [LearnController::class, 'complete'])->name('learn.complete');

    Route::get('quizzes/{quiz}/take', [QuizAttemptController::class, 'take'])->name('quiz-attempts.take');
    Route::post('attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz-attempts.submit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quiz-attempts.show');
});
