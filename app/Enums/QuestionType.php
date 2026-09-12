<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';

    public function isAutoGradable(): bool
    {
        return match ($this) {
            self::MultipleChoice, self::TrueFalse => true,
            self::ShortAnswer, self::Essay => false,
        };
    }
}
