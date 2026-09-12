<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('an instructor sees instructor stats on the dashboard', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('role', 'instructor')
            ->where('stats.total_courses', 1));
});

test('a student sees their enrolled course as continue learning', function () {
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->create();
    $course->enrollments()->create(['user_id' => $student->id]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('role', 'student')
            ->where('stats.enrolled_courses', 1)
            ->where('stats.continue_course.id', $course->id));
});

test('an admin sees ai usage stats on the dashboard', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('role', 'admin'));
});
