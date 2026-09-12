<?php

namespace App\Http\Requests\Section;

use App\Models\Course;
use App\Models\Section;
use App\Services\RichContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Course $course
 */
class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Section::class, $this->course]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => app(RichContentSanitizer::class)->sanitize($this->input('description')),
        ]);
    }
}
