<?php

namespace App\Http\Requests\AI;

use App\Enums\QuestionType;
use App\Models\Quiz;
use App\Services\RichContentSanitizer;
use App\Support\QuestionOptionValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Quiz $quiz
 */
class SaveGeneratedQuestionsRequest extends FormRequest
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
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.points' => ['required', 'integer', 'min:1'],
            'questions.*.options' => ['array'],
            'questions.*.options.*.option_text' => ['required_with:questions.*.options', 'string', 'max:255'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(RichContentSanitizer::class);

        $questions = collect((array) $this->input('questions', []))
            ->map(fn ($question) => [
                ...$question,
                'question_text' => $sanitizer->sanitize($question['question_text'] ?? null),
                'explanation' => $sanitizer->sanitize($question['explanation'] ?? null),
            ])
            ->all();

        $this->merge(['questions' => $questions]);
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ($this->input('questions', []) as $index => $question) {
                    $type = QuestionType::tryFrom($question['type'] ?? '');

                    if ($type === null) {
                        continue;
                    }

                    $error = QuestionOptionValidator::validate($type, $question['options'] ?? []);

                    if ($error !== null) {
                        $validator->errors()->add("questions.{$index}.options", $error);
                    }
                }
            },
        ];
    }
}
