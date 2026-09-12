<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentPolicy
{
    public function view(User $user, CourseEnrollment $enrollment): bool
    {
        return $user->id === $enrollment->user_id
            || $user->isAdmin()
            || $enrollment->course->instructor_id === $user->id;
    }

    public function create(User $user, Course $course): bool
    {
        return $user->isStudent();
    }
}
