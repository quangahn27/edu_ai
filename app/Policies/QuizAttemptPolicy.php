<?php

namespace App\Policies;

use App\Enums\QuizAttemptStatus;
use App\Enums\QuizStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy
{
    public function view(User $user, QuizAttempt $attempt): bool
    {
        if ($user->id === $attempt->user_id || $user->isAdmin()) {
            return true;
        }

        return $attempt->quiz->resolveCourse()?->instructor_id === $user->id;
    }

    public function create(User $user, Quiz $quiz): bool
    {
        if (! $user->isStudent() || $quiz->status !== QuizStatus::Published) {
            return false;
        }

        $course = $quiz->resolveCourse();

        return $course !== null && $course->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id && $attempt->status === QuizAttemptStatus::InProgress;
    }
}
