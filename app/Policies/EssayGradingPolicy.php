<?php

namespace App\Policies;

use App\Models\EssayGrading;
use App\Models\QuizAnswer;
use App\Models\User;

class EssayGradingPolicy
{
    public function view(User $user, EssayGrading $grading): bool
    {
        $attempt = $grading->quizAnswer->attempt;

        if ($user->id === $attempt->user_id || $user->isAdmin()) {
            return true;
        }

        return $attempt->quiz->resolveCourse()?->instructor_id === $user->id;
    }

    public function create(User $user, QuizAnswer $quizAnswer): bool
    {
        return $this->manages($user, $quizAnswer);
    }

    public function update(User $user, EssayGrading $grading): bool
    {
        return $this->manages($user, $grading->quizAnswer);
    }

    private function manages(User $user, QuizAnswer $quizAnswer): bool
    {
        $course = $quizAnswer->attempt->quiz->resolveCourse();

        return $user->isAdmin() || $course?->instructor_id === $user->id;
    }
}
