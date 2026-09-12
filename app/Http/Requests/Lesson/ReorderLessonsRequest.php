<?php

namespace App\Http\Requests\Lesson;

use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Section $section
 */
class ReorderLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->section);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lesson_ids' => ['required', 'array'],
            'lesson_ids.*' => [
                'integer',
                Rule::exists('lessons', 'id')->where('section_id', $this->section->id),
            ],
        ];
    }
}
