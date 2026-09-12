<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return $course->status === CourseStatus::Published
            || $user->isAdmin()
            || $course->instructor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isInstructor();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isInstructor() && $course->instructor_id === $user->id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
