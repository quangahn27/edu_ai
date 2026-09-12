<?php

namespace App\Support;

use App\Enums\QuestionType;

/**
 * Shared validation for a question's options, used both by the manual
 * question-builder requests and the AI-generated question review/save flow.
 */
class QuestionOptionValidator
{
    /**
     * @param  array<int, array{option_text?: string, is_correct?: bool}>  $options
     * @return string|null An error message, or null when the options are valid.
     */
    public static function validate(QuestionType $type, array $options): ?string
    {
        if (! $type->isAutoGradable()) {
            return null;
        }

        if ($type === QuestionType::TrueFalse && count($options) !== 2) {
            return 'A true/false question needs exactly 2 options.';
        }

        if ($type === QuestionType::MultipleChoice && count($options) < 2) {
            return 'A multiple choice question needs at least 2 options.';
        }

        $correctCount = collect($options)->where('is_correct', true)->count();

        if ($correctCount !== 1) {
            return 'Select exactly one correct option.';
        }

        return null;
    }
}
