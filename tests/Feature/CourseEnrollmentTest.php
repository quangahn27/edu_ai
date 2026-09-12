<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\QueryException;

test('a student cannot enroll in the same course twice', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $course->enrollments()->create(['user_id' => $student->id]);

    expect(fn () => $course->enrollments()->create(['user_id' => $student->id]))
        ->toThrow(QueryException::class);
});

test('only students can create enrollments', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    expect($student->can('create', [CourseEnrollment::class, $course]))->toBeTrue();
    expect($instructor->can('create', [CourseEnrollment::class, $course]))->toBeFalse();
    expect($admin->can('create', [CourseEnrollment::class, $course]))->toBeFalse();
});

test('an enrolled student appears in the course students relationship', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $course->enrollments()->create(['user_id' => $student->id]);

    expect($course->students()->whereKey($student->id)->exists())->toBeTrue();
    expect($student->enrolledCourses()->whereKey($course->id)->exists())->toBeTrue();
});
