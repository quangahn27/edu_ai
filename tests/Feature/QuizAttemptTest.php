<?php

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Enums\QuizStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function enrolledStudent(Course $course): User
{
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course->enrollments()->create(['user_id' => $student->id]);

    return $student;
}

test('an enrolled student can start an attempt on a published quiz', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('quiz-attempts.take', $quiz))
        ->assertOk();

    expect($quiz->attempts()->where('user_id', $student->id)->exists())->toBeTrue();
});

test('a non-enrolled student cannot start an attempt', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $student = User::factory()->create(['role' => UserRole::Student]);

    $this->actingAs($student)
        ->get(route('quiz-attempts.take', $quiz))
        ->assertForbidden();
});

test('a student cannot start an attempt on a draft quiz', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Draft]);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('quiz-attempts.take', $quiz))
        ->assertForbidden();
});

test('a student cannot exceed the max attempts for a quiz', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create([
        'status' => QuizStatus::Published,
        'max_attempts' => 1,
    ]);
    $student = enrolledStudent($course);

    $quiz->attempts()->create([
        'user_id' => $student->id,
        'started_at' => now(),
        'submitted_at' => now(),
        'status' => QuizAttemptStatus::Graded,
    ]);

    $this->actingAs($student)
        ->get(route('quiz-attempts.take', $quiz))
        ->assertForbidden();
});

test('submitting a fully auto-gradable quiz grades it immediately', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $student = enrolledStudent($course);

    $question = Question::factory()->for($quiz)->create([
        'type' => QuestionType::MultipleChoice,
        'points' => 4,
    ]);
    $correct = $question->options()->create(['option_text' => 'Correct', 'is_correct' => true]);
    $question->options()->create(['option_text' => 'Wrong', 'is_correct' => false]);

    $attempt = $quiz->attempts()->create([
        'user_id' => $student->id,
        'started_at' => now(),
    ]);

    $this->actingAs($student)
        ->post(route('quiz-attempts.submit', $attempt), [
            'answers' => [
                ['question_id' => $question->id, 'selected_option_id' => $correct->id],
            ],
        ])
        ->assertRedirect(route('quiz-attempts.show', $attempt));

    $attempt->refresh();
    expect($attempt->status)->toBe(QuizAttemptStatus::Graded);
    expect((float) $attempt->score)->toBe(4.0);
    expect((float) $attempt->percentage)->toBe(100.0);
});

test('a quiz with an essay question stays pending after submission', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $student = enrolledStudent($course);

    $mcq = Question::factory()->for($quiz)->create(['type' => QuestionType::MultipleChoice, 'points' => 1]);
    $correct = $mcq->options()->create(['option_text' => 'A', 'is_correct' => true]);
    $mcq->options()->create(['option_text' => 'B', 'is_correct' => false]);

    $essay = Question::factory()->for($quiz)->create(['type' => QuestionType::Essay, 'points' => 5]);

    $attempt = $quiz->attempts()->create([
        'user_id' => $student->id,
        'started_at' => now(),
    ]);

    $this->actingAs($student)->post(route('quiz-attempts.submit', $attempt), [
        'answers' => [
            ['question_id' => $mcq->id, 'selected_option_id' => $correct->id],
            ['question_id' => $essay->id, 'answer_text' => 'My long answer.'],
        ],
    ]);

    $attempt->refresh();
    expect($attempt->status)->toBe(QuizAttemptStatus::Submitted);
    expect($attempt->score)->toBeNull();

    $essayAnswer = $attempt->answers()->where('question_id', $essay->id)->firstOrFail();
    expect($essayAnswer->is_correct)->toBeNull();
    expect($essayAnswer->answer_text)->toBe('My long answer.');
});

test('a student cannot view another students attempt', function () {
    $course = Course::factory()->published()->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $owner = enrolledStudent($course);
    $intruder = enrolledStudent($course);

    $attempt = $quiz->attempts()->create([
        'user_id' => $owner->id,
        'started_at' => now(),
    ]);

    $this->actingAs($intruder)
        ->get(route('quiz-attempts.show', $attempt))
        ->assertForbidden();
});

test('the owning instructor can view a student attempt', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create(['status' => QuizStatus::Published]);
    $student = enrolledStudent($course);

    $attempt = $quiz->attempts()->create([
        'user_id' => $student->id,
        'started_at' => now(),
    ]);

    $this->actingAs($instructor)
        ->get(route('quiz-attempts.show', $attempt))
        ->assertOk();
});
