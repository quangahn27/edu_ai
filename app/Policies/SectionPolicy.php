<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function view(User $user, Section $section): bool
    {
        return $user->isAdmin() || $section->course->instructor_id === $user->id;
    }

    public function create(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->instructor_id === $user->id;
    }

    public function update(User $user, Section $section): bool
    {
        return $user->isAdmin() || $section->course->instructor_id === $user->id;
    }

    public function delete(User $user, Section $section): bool
    {
        return $this->update($user, $section);
    }
}
