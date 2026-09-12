<?php

namespace App\Http\Requests\Question;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\RichContentSanitizer;
use App\Support\QuestionOptionValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Quiz $quiz
 */
class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Question::class, $this->quiz]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'reference_answer' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            'options' => ['array'],
            'options.*.option_text' => ['required_with:options', 'string', 'max:255'],
            'options.*.is_correct' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'question_text' => app(RichContentSanitizer::class)->sanitize($this->input('question_text')),
            'explanation' => app(RichContentSanitizer::class)->sanitize($this->input('explanation')),
            'reference_answer' => app(RichContentSanitizer::class)->sanitize($this->input('reference_answer')),
        ]);
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $type = $this->enum('type', QuestionType::class);

                if ($type === null) {
                    return;
                }

                $error = QuestionOptionValidator::validate($type, (array) $this->input('options', []));

                if ($error !== null) {
                    $validator->errors()->add('options', $error);
                }
            },
        ];
    }
}
