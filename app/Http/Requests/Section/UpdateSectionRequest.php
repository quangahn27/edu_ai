<?php

namespace App\Http\Requests\Section;

use App\Models\Section;
use App\Services\RichContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Section $section
 */
class UpdateSectionRequest extends FormRequest
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
