<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:instructor,admin'])->group(function () {
    Route::resource('courses', CourseController::class);

    Route::post('courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::patch('courses/{course}/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
    Route::patch('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    Route::get('sections/{section}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::patch('sections/{section}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
    Route::get('lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::patch('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
});
