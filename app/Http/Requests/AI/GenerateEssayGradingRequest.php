<?php

namespace App\Http\Requests\AI;

use App\Models\EssayGrading;
use App\Models\QuizAnswer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read QuizAnswer $answer
 */
class GenerateEssayGradingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [EssayGrading::class, $this->answer]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
