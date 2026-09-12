<?php

namespace App\Http\Requests\Section;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Course $course
 */
class ReorderSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->course);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'section_ids' => ['required', 'array'],
            'section_ids.*' => [
                'integer',
                Rule::exists('sections', 'id')->where('course_id', $this->course->id),
            ],
        ];
    }
}
