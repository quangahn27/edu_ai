<?php

use App\Enums\AiLogStatus;
use App\Enums\UserRole;
use App\Models\AiGeneration;
use App\Models\AiLog;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use App\Services\AI\AIProvider;
use App\Services\AI\AIResponse;
use Tests\Support\FakeAIProvider;

function bindFakeQuizGenerator(string $json): FakeAIProvider
{
    $fake = new class($json) extends FakeAIProvider
    {
        public function __construct(private string $json)
        {
            parent::__construct();
        }

        public function generate(string $prompt, array $options = []): AIResponse
        {
            $this->calls[] = ['prompt' => $prompt, 'options' => $options];

            return new AIResponse(content: $this->json, promptTokens: 50, outputTokens: 100, totalTokens: 150);
        }
    };

    app()->instance(AIProvider::class, $fake);

    return $fake;
}

function validGeneratedQuizJson(): string
{
    return json_encode([
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'What is 2 + 2?',
                'options' => [
                    ['text' => '3', 'is_correct' => false],
                    ['text' => '4', 'is_correct' => true],
                ],
                'explanation' => 'Basic addition.',
                'difficulty' => 'easy',
                'points' => 1,
            ],
            [
                'type' => 'true_false',
                'question' => 'The sky is blue.',
                'options' => [
                    ['text' => 'True', 'is_correct' => true],
                    ['text' => 'False', 'is_correct' => false],
                ],
                'explanation' => 'On a clear day.',
                'difficulty' => 'easy',
                'points' => 1,
            ],
        ],
    ]);
}

test('an instructor can generate quiz questions from a lesson', function () {
    $fake = bindFakeQuizGenerator(validGeneratedQuizJson());
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $response = $this->actingAs($instructor)
        ->postJson(route('ai.quiz-generator.generate', $lesson), [
            'question_count' => 5,
            'difficulty' => 'easy',
            'question_type' => 'multiple_choice',
        ])
        ->assertOk();

    $response->assertJsonCount(2, 'questions');
    expect(AiGeneration::count())->toBe(1);
    expect(AiLog::firstOrFail()->status)->toBe(AiLogStatus::Success);
    expect($fake->calls[0]['options']['json'])->toBeTrue();
});

test('a student cannot generate quiz questions', function () {
    bindFakeQuizGenerator(validGeneratedQuizJson());
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->actingAs($student)
        ->postJson(route('ai.quiz-generator.generate', $lesson), [
            'question_count' => 5,
            'difficulty' => 'easy',
            'question_type' => 'multiple_choice',
        ])
        ->assertForbidden();
});

test('an instructor cannot generate quiz questions for another instructor lesson', function () {
    bindFakeQuizGenerator(validGeneratedQuizJson());
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $other = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->actingAs($other)
        ->postJson(route('ai.quiz-generator.generate', $lesson), [
            'question_count' => 5,
            'difficulty' => 'easy',
            'question_type' => 'multiple_choice',
        ])
        ->assertForbidden();
});

test('malformed AI output is rejected and logged as a failure', function () {
    bindFakeQuizGenerator('not valid json {{{');
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->actingAs($instructor)
        ->postJson(route('ai.quiz-generator.generate', $lesson), [
            'question_count' => 5,
            'difficulty' => 'easy',
            'question_type' => 'multiple_choice',
        ])
        ->assertServiceUnavailable();

    expect(AiGeneration::count())->toBe(0);
    expect(AiLog::firstOrFail()->status)->toBe(AiLogStatus::Failed);
});

test('a question with more than one correct option is dropped during validation', function () {
    $badJson = json_encode([
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Bad question',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'true_false',
                'question' => 'Good question',
                'options' => [
                    ['text' => 'True', 'is_correct' => true],
                    ['text' => 'False', 'is_correct' => false],
                ],
            ],
        ],
    ]);
    bindFakeQuizGenerator($badJson);
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $response = $this->actingAs($instructor)
        ->postJson(route('ai.quiz-generator.generate', $lesson), [
            'question_count' => 5,
            'difficulty' => 'easy',
            'question_type' => 'true_false',
        ])
        ->assertOk();

    $response->assertJsonCount(1, 'questions');
    $response->assertJsonPath('questions.0.question_text', 'Good question');
});

test('an instructor can save selected generated questions to a quiz', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('ai.quiz-generator.save', $quiz), [
            'questions' => [
                [
                    'type' => 'multiple_choice',
                    'question_text' => 'What is 2 + 2?',
                    'explanation' => 'Basic addition.',
                    'points' => 1,
                    'options' => [
                        ['option_text' => '3', 'is_correct' => false],
                        ['option_text' => '4', 'is_correct' => true],
                    ],
                ],
            ],
        ])
        ->assertRedirect(route('quizzes.show', $quiz));

    expect($quiz->questions()->count())->toBe(1);
    $question = $quiz->questions()->firstOrFail();
    expect($question->options()->count())->toBe(2);
});

test('saving generated questions rejects invalid option counts', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('ai.quiz-generator.save', $quiz), [
            'questions' => [
                [
                    'type' => 'true_false',
                    'question_text' => 'Bad question',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'True', 'is_correct' => true],
                    ],
                ],
            ],
        ])
        ->assertSessionHasErrors('questions.0.options');

    expect($quiz->questions()->count())->toBe(0);
});
