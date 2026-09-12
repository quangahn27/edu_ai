<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function view(User $user, Quiz $quiz): bool
    {
        $course = $quiz->resolveCourse();

        if (! $course) {
            return $user->isAdmin();
        }

        if ($user->isAdmin() || $course->instructor_id === $user->id) {
            return true;
        }

        return $course->students()->whereKey($user->id)->exists();
    }

    public function create(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->instructor_id === $user->id;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        $course = $quiz->resolveCourse();

        return $user->isAdmin() || $course?->instructor_id === $user->id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }
}
