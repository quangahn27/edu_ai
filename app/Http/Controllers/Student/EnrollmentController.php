<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('create', [CourseEnrollment::class, $course]);

        $course->enrollments()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'enrolled_at' => now(),
        ]);

        return redirect()->route('student.courses.show', $course)
            ->with('success', 'Enrolled successfully.');
    }
}
