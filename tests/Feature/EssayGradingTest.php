<?php

use App\Enums\AiLogStatus;
use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Enums\UserRole;
use App\Models\AiLog;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AI\AIProvider;
use App\Services\AI\AIResponse;
use Tests\Support\FakeAIProvider;

function bindFakeEssayGrader(string $json): FakeAIProvider
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

            return new AIResponse(content: $this->json, promptTokens: 20, outputTokens: 30, totalTokens: 50);
        }
    };

    app()->instance(AIProvider::class, $fake);

    return $fake;
}

function essayAnswerFixture(?User $instructor = null): array
{
    $instructor ??= User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $quiz = Quiz::factory()->for($course)->create();
    $question = Question::factory()->for($quiz)->create([
        'type' => QuestionType::Essay,
        'points' => 10,
        'reference_answer' => 'A model answer.',
    ]);
    $attempt = $quiz->attempts()->create(['user_id' => $student->id, 'started_at' => now()]);
    $answer = $attempt->answers()->create([
        'question_id' => $question->id,
        'answer_text' => 'My essay answer.',
    ]);

    return compact('instructor', 'student', 'course', 'quiz', 'question', 'attempt', 'answer');
}

test('an instructor can request an AI grading suggestion for an essay answer', function () {
    $fake = bindFakeEssayGrader(json_encode([
        'suggested_score' => 8,
        'max_score' => 10,
        'criteria' => [['name' => 'Clarity', 'score' => 4, 'max_score' => 5, 'comment' => 'Clear.']],
        'strengths' => ['Good structure'],
        'improvements' => ['Add more detail'],
        'feedback' => 'Solid answer overall.',
    ]));

    ['instructor' => $instructor, 'answer' => $answer] = essayAnswerFixture();

    $response = $this->actingAs($instructor)
        ->postJson(route('ai.essay-grading.generate', $answer))
        ->assertOk();

    $response->assertJsonPath('grading.ai_suggested_score', '8.00');
    expect(AiLog::firstOrFail()->status)->toBe(AiLogStatus::Success);
    expect($fake->calls[0]['options']['json'])->toBeTrue();
});

test('the AI suggested score is clamped to the max score', function () {
    bindFakeEssayGrader(json_encode(['suggested_score' => 999]));
    ['instructor' => $instructor, 'answer' => $answer] = essayAnswerFixture();

    $this->actingAs($instructor)->postJson(route('ai.essay-grading.generate', $answer));

    $grading = $answer->essayGrading()->firstOrFail();
    expect((float) $grading->ai_suggested_score)->toBe(10.0);
});

test('a student cannot request an AI grading suggestion', function () {
    bindFakeEssayGrader(json_encode(['suggested_score' => 8]));
    ['student' => $student, 'answer' => $answer] = essayAnswerFixture();

    $this->actingAs($student)
        ->postJson(route('ai.essay-grading.generate', $answer))
        ->assertForbidden();
});

test('an instructor can save a final grade and it overrides the AI suggestion', function () {
    ['instructor' => $instructor, 'answer' => $answer, 'attempt' => $attempt] = essayAnswerFixture();
    $answer->essayGrading()->create(['ai_suggested_score' => 8, 'ai_feedback' => 'AI feedback']);

    $this->actingAs($instructor)
        ->patch(route('essays.update', $answer), [
            'teacher_score' => 9,
            'teacher_feedback' => 'Great work, minor improvements needed.',
        ])
        ->assertRedirect(route('essays.index'));

    $grading = $answer->essayGrading()->firstOrFail();
    expect((float) $grading->teacher_score)->toBe(9.0);
    expect($grading->graded_by)->toBe($instructor->id);

    expect((float) $answer->fresh()->score)->toBe(9.0);
});

test('the attempt is fully graded once the essay answer is scored', function () {
    ['instructor' => $instructor, 'answer' => $answer, 'attempt' => $attempt] = essayAnswerFixture();

    expect($attempt->status)->toBe(QuizAttemptStatus::InProgress);

    $this->actingAs($instructor)->patch(route('essays.update', $answer), [
        'teacher_score' => 7,
    ]);

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Graded);
    expect((float) $attempt->fresh()->score)->toBe(7.0);
});

test('a teacher score above the question max is rejected', function () {
    ['instructor' => $instructor, 'answer' => $answer] = essayAnswerFixture();

    $this->actingAs($instructor)
        ->patch(route('essays.update', $answer), ['teacher_score' => 999])
        ->assertSessionHasErrors('teacher_score');
});

test('an instructor cannot grade an essay for another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $other = User::factory()->create(['role' => UserRole::Instructor]);
    ['answer' => $answer] = essayAnswerFixture($owner);

    $this->actingAs($other)
        ->patch(route('essays.update', $answer), ['teacher_score' => 5])
        ->assertForbidden();
});
