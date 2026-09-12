<?php

use App\Enums\AiLogStatus;
use App\Enums\UserRole;
use App\Models\AiConversation;
use App\Models\AiLog;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use App\Services\AI\AIProvider;
use App\Services\AI\Exceptions\AIRequestException;
use Tests\Support\FakeAIProvider;

function bindFakeAiProvider(): FakeAIProvider
{
    $fake = new FakeAIProvider('Đây là câu trả lời từ AI Tutor.');
    app()->instance(AIProvider::class, $fake);

    return $fake;
}

test('guests cannot ask the AI tutor', function () {
    $course = Course::factory()->published()->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->postJson(route('ai.tutor.store', $lesson), ['question' => 'Hi?'])
        ->assertUnauthorized();
});

test('a student without lesson access cannot ask the AI tutor', function () {
    bindFakeAiProvider();
    $course = Course::factory()->published()->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create(['is_preview' => false]);
    $student = User::factory()->create(['role' => UserRole::Student]);

    $this->actingAs($student)
        ->postJson(route('ai.tutor.store', $lesson), ['question' => 'Explain this?'])
        ->assertForbidden();
});

test('an enrolled student can ask the AI tutor and receives a reply', function () {
    $fake = bindFakeAiProvider();
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();
    $course->enrollments()->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)
        ->postJson(route('ai.tutor.store', $lesson), ['question' => 'What is a variable?'])
        ->assertCreated();

    $response->assertJsonPath('message.content', 'Đây là câu trả lời từ AI Tutor.');

    expect(AiConversation::where('user_id', $student->id)->where('lesson_id', $lesson->id)->count())->toBe(1);
    $conversation = AiConversation::firstOrFail();
    expect($conversation->messages()->count())->toBe(2);

    $log = AiLog::firstOrFail();
    expect($log->status)->toBe(AiLogStatus::Success);
    expect($log->total_tokens)->toBe(15);

    expect($fake->calls)->toHaveCount(1);
    expect($fake->calls[0]['options']['system'])->toContain('AI Tutor');
});

test('a second question continues the same conversation', function () {
    bindFakeAiProvider();
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();
    $course->enrollments()->create(['user_id' => $student->id]);

    $first = $this->actingAs($student)
        ->postJson(route('ai.tutor.store', $lesson), ['question' => 'First question?'])
        ->json();

    $this->actingAs($student)
        ->postJson(route('ai.tutor.store', $lesson), [
            'question' => 'Follow up question?',
            'conversation_id' => $first['conversation_id'],
        ])
        ->assertCreated();

    expect(AiConversation::count())->toBe(1);
    expect(AiConversation::firstOrFail()->messages()->count())->toBe(4);
});

test('a failed AI request returns a generic error and logs the failure', function () {
    $fake = bindFakeAiProvider();
    $fake->failNextRequestWith(new AIRequestException('Gemini quota exceeded.'));

    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();
    $course->enrollments()->create(['user_id' => $student->id]);

    $this->actingAs($student)
        ->postJson(route('ai.tutor.store', $lesson), ['question' => 'Will this fail?'])
        ->assertServiceUnavailable()
        ->assertJsonMissing(['message' => 'Gemini quota exceeded.']);

    $log = AiLog::firstOrFail();
    expect($log->status)->toBe(AiLogStatus::Failed);
    expect($log->error_message)->toBe('Gemini quota exceeded.');
});
