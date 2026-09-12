<?php

namespace App\Http\Requests\EssayGrading;

use App\Models\EssayGrading;
use App\Models\QuizAnswer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * @property-read QuizAnswer $answer
 */
class GradeEssayRequest extends FormRequest
{
    public function authorize(): bool
    {
        $existing = $this->answer->essayGrading;

        return $existing
            ? Gate::allows('update', $existing)
            : Gate::allows('create', [EssayGrading::class, $this->answer]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'teacher_score' => ['required', 'numeric', 'min:0'],
            'teacher_feedback' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'teacher_feedback' => $this->filled('teacher_feedback')
                ? Str::of((string) $this->input('teacher_feedback'))->stripTags()->toString()
                : null,
        ]);
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $maxScore = $this->answer->question->points;

                if ($this->filled('teacher_score') && (float) $this->input('teacher_score') > $maxScore) {
                    $validator->errors()->add('teacher_score', "The score cannot exceed {$maxScore} points.");
                }
            },
        ];
    }
}
