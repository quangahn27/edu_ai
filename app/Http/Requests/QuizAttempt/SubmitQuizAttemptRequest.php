<?php

namespace App\Http\Requests\QuizAttempt;

use App\Models\QuizAttempt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read QuizAttempt $attempt
 */
class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->attempt);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.question_id' => [
                'required',
                'integer',
                Rule::exists('questions', 'id')->where('quiz_id', $this->attempt->quiz_id),
            ],
            'answers.*.selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'answers.*.answer_text' => ['nullable', 'string'],
        ];
    }
}
