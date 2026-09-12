<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;

test('instructors and admins can create courses but students cannot', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $student = User::factory()->create(['role' => UserRole::Student]);

    expect($instructor->can('create', Course::class))->toBeTrue();
    expect($admin->can('create', Course::class))->toBeTrue();
    expect($student->can('create', Course::class))->toBeFalse();
});

test('an instructor can update their own course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    expect($instructor->can('update', $course))->toBeTrue();
});

test('an instructor cannot update another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $otherInstructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    expect($otherInstructor->can('update', $course))->toBeFalse();
    expect($otherInstructor->can('delete', $course))->toBeFalse();
});

test('an admin can update any course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    expect($admin->can('update', $course))->toBeTrue();
});

test('a draft course is only visible to its instructor and admins', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $otherInstructor = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    expect($owner->can('view', $course))->toBeTrue();
    expect($admin->can('view', $course))->toBeTrue();
    expect($otherInstructor->can('view', $course))->toBeFalse();
    expect($student->can('view', $course))->toBeFalse();
});

test('a published course is visible to everyone', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($owner, 'instructor')->create();

    expect($student->can('view', $course))->toBeTrue();
});
