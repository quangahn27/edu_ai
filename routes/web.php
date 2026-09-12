<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/courses.php';
require __DIR__.'/quizzes.php';
require __DIR__.'/student.php';
require __DIR__.'/ai.php';
require __DIR__.'/essays.php';
