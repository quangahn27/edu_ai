<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;

test('a student can enroll in a published course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    $this->actingAs($student)
        ->post(route('student.courses.enroll', $course))
        ->assertRedirect(route('student.courses.show', $course));

    expect($course->enrollments()->where('user_id', $student->id)->exists())->toBeTrue();
});

test('enrolling twice does not create duplicate enrollments', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    $this->actingAs($student)->post(route('student.courses.enroll', $course));
    $this->actingAs($student)->post(route('student.courses.enroll', $course));

    expect($course->enrollments()->where('user_id', $student->id)->count())->toBe(1);
});

test('instructors cannot enroll in courses', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->published()->create();

    $this->actingAs($instructor)
        ->post(route('student.courses.enroll', $course))
        ->assertForbidden();
});

test('an enrolled student can view a non-preview lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);
    $course->enrollments()->create(['user_id' => $student->id]);

    $this->actingAs($student)
        ->get(route('learn.show', $lesson))
        ->assertOk();
});

test('a non-enrolled student cannot view a non-preview lesson', function () {
    $course = Course::factory()->published()->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);
    $student = User::factory()->create(['role' => UserRole::Student]);

    $this->actingAs($student)
        ->get(route('learn.show', $lesson))
        ->assertForbidden();
});

test('a student can mark a lesson as completed', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();
    $course->enrollments()->create(['user_id' => $student->id]);

    $this->actingAs($student)
        ->post(route('learn.complete', $lesson))
        ->assertRedirect();

    $progress = $lesson->progress()->where('user_id', $student->id)->firstOrFail();
    expect($progress->progress_percent)->toBe(100);
    expect($progress->completed_at)->not->toBeNull();
});

test('instructors and admins cannot access the student learning routes', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($instructor)->get(route('student.courses.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('student.courses.index'))->assertForbidden();
});
