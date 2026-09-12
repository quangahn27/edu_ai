<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;

test('the owning instructor can view a lesson in their course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);

    expect($instructor->can('view', $lesson))->toBeTrue();
});

test('an enrolled student can view a non-preview lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);

    $course->enrollments()->create(['user_id' => $student->id]);

    expect($student->can('view', $lesson))->toBeTrue();
});

test('a non-enrolled student cannot view a non-preview lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);

    expect($student->can('view', $lesson))->toBeFalse();
});

test('a non-enrolled student can view a preview lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => true]);

    expect($student->can('view', $lesson))->toBeTrue();
});

test('an instructor cannot manage a lesson in another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $otherInstructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    expect($otherInstructor->can('update', $lesson))->toBeFalse();
    expect($otherInstructor->can('delete', $lesson))->toBeFalse();
});
