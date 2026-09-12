<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::query()
            ->published()
            ->withCount('sections')
            ->with('instructor:id,name')
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $enrolledCourseIds = $request->user()
            ->enrollments()
            ->pluck('course_id');

        return Inertia::render('student/courses/index', [
            'courses' => $courses,
            'enrolledCourseIds' => $enrolledCourseIds,
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        Gate::authorize('view', $course);

        $course->load(['sections.lessons.quizzes', 'quizzes', 'instructor:id,name']);

        $enrollment = $request->user()
            ->enrollments()
            ->where('course_id', $course->id)
            ->first();

        return Inertia::render('student/courses/show', [
            'course' => $course,
            'enrollment' => $enrollment,
        ]);
    }
}
