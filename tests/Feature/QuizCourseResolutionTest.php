<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;

test('a quiz attached directly to a course resolves that course', function () {
    $course = Course::factory()->create();
    $quiz = Quiz::factory()->for($course)->create();

    expect($quiz->resolveCourse()->is($course))->toBeTrue();
});

test('a quiz attached to a lesson resolves the course through the section', function () {
    $course = Course::factory()->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();
    $quiz = Quiz::factory()->create(['course_id' => null, 'lesson_id' => $lesson->id]);

    expect($quiz->resolveCourse()->is($course))->toBeTrue();
});

test('an instructor can only manage quizzes in their own course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $otherInstructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    expect($owner->can('update', $quiz))->toBeTrue();
    expect($otherInstructor->can('update', $quiz))->toBeFalse();
});
