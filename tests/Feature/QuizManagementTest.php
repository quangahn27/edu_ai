<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;

test('an instructor can create a quiz on their own course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->post(route('quizzes.store-for-course', $course), [
            'title' => 'Module 1 Quiz',
            'passing_score' => 60,
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($course->quizzes()->where('title', 'Module 1 Quiz')->exists())->toBeTrue();
});

test('an instructor can create a quiz on a lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->actingAs($instructor)
        ->post(route('quizzes.store-for-lesson', $lesson), [
            'title' => 'Lesson Check',
            'passing_score' => 50,
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($lesson->quizzes()->where('title', 'Lesson Check')->exists())->toBeTrue();
});

test('an instructor cannot create a quiz on another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $other = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    $this->actingAs($other)
        ->post(route('quizzes.store-for-course', $course), [
            'title' => 'Sneaky Quiz',
            'passing_score' => 60,
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('creating a multiple choice question requires exactly one correct option', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('questions.store', $quiz), [
            'type' => 'multiple_choice',
            'question_text' => 'What is 2+2?',
            'points' => 1,
            'options' => [
                ['option_text' => '3', 'is_correct' => false],
                ['option_text' => '4', 'is_correct' => false],
            ],
        ])
        ->assertSessionHasErrors('options');

    expect($quiz->questions()->count())->toBe(0);
});

test('an instructor can create a valid multiple choice question with options', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('questions.store', $quiz), [
            'type' => 'multiple_choice',
            'question_text' => 'What is 2+2?',
            'points' => 2,
            'options' => [
                ['option_text' => '3', 'is_correct' => false],
                ['option_text' => '4', 'is_correct' => true],
            ],
        ])
        ->assertRedirect(route('quizzes.show', $quiz));

    $question = $quiz->questions()->firstOrFail();
    expect($question->options()->count())->toBe(2);
    expect($question->options()->where('is_correct', true)->count())->toBe(1);
});

test('a true/false question must have exactly two options', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('questions.store', $quiz), [
            'type' => 'true_false',
            'question_text' => 'The sky is blue.',
            'points' => 1,
            'options' => [
                ['option_text' => 'True', 'is_correct' => true],
            ],
        ])
        ->assertSessionHasErrors('options');
});

test('questions can be reordered within a quiz', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();
    $first = Question::factory()->for($quiz)->create(['sort_order' => 0]);
    $second = Question::factory()->for($quiz)->create(['sort_order' => 1]);

    $this->actingAs($instructor)
        ->patch(route('questions.reorder', $quiz), [
            'question_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(0);
    expect($first->fresh()->sort_order)->toBe(1);
});
