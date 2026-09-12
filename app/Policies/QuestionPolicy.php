<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

class QuestionPolicy
{
    public function view(User $user, Question $question): bool
    {
        return $this->manages($user, $question->resolveCourse());
    }

    public function create(User $user, Quiz $quiz): bool
    {
        return $this->manages($user, $quiz->resolveCourse());
    }

    public function update(User $user, Question $question): bool
    {
        return $this->manages($user, $question->resolveCourse());
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->update($user, $question);
    }

    private function manages(User $user, ?Course $course): bool
    {
        return $user->isAdmin() || $course?->instructor_id === $user->id;
    }
}
