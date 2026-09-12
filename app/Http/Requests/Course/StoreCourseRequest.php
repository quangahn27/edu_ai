<?php

namespace App\Http\Requests\Course;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Models\Course;
use App\Services\RichContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Course::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'status' => ['required', Rule::enum(CourseStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => app(RichContentSanitizer::class)->sanitize($this->input('description')),
        ]);
    }
}
