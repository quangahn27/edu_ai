<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;

class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->section->course;

        if ($user->isAdmin() || $course->instructor_id === $user->id) {
            return true;
        }

        if ($lesson->is_preview) {
            return true;
        }

        return $course->students()->whereKey($user->id)->exists();
    }

    public function create(User $user, Section $section): bool
    {
        return $user->isAdmin() || $section->course->instructor_id === $user->id;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $lesson->section->course->instructor_id === $user->id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}
