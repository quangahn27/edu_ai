<?php

namespace App\Http\Requests\AI;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Lesson $lesson
 */
class GenerateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->lesson);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question_count' => ['required', Rule::in([5, 10, 20])],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'question_type' => ['required', Rule::in(['multiple_choice', 'true_false', 'short_answer'])],
            'language' => ['nullable', 'string', 'max:50'],
            'additional_instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
