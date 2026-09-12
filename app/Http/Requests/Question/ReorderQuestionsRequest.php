<?php

namespace App\Http\Requests\Question;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Quiz $quiz
 */
class ReorderQuestionsRequest extends FormRequest
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
            'question_ids' => ['required', 'array'],
            'question_ids.*' => [
                'integer',
                Rule::exists('questions', 'id')->where('quiz_id', $this->quiz->id),
            ],
        ];
    }
}
