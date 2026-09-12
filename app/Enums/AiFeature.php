<?php

namespace App\Enums;

enum AiFeature: string
{
    case Tutor = 'tutor';
    case QuizGenerator = 'quiz_generator';
    case EssayGrading = 'essay_grading';
}
