<?php

namespace App\Http\Requests\Quiz;

use App\Enums\QuizStatus;
use App\Models\Quiz;
use App\Services\RichContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Quiz $quiz
 */
class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->quiz);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'shuffle_questions' => ['boolean'],
            'status' => ['required', Rule::enum(QuizStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => app(RichContentSanitizer::class)->sanitize($this->input('description')),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
        ]);
    }
}
