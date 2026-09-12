<?php

namespace App\Http\Requests\Lesson;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\Section;
use App\Services\RichContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Section $section
 */
class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Lesson::class, $this->section]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
            'status' => ['required', Rule::enum(LessonStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => app(RichContentSanitizer::class)->sanitize($this->input('content')),
            'is_preview' => $this->boolean('is_preview'),
        ]);
    }
}
