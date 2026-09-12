<?php

namespace App\Http\Requests\AI;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Lesson $lesson
 */
class AskTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->lesson);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:2000'],
            'conversation_id' => [
                'nullable',
                'integer',
                Rule::exists('ai_conversations', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('lesson_id', $this->lesson->id),
            ],
        ];
    }
}
