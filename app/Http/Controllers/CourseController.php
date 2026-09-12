<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $courses = Course::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('instructor_id', $user->id))
            ->withCount(['sections', 'enrollments'])
            ->with('instructor:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('instructor/courses/index', [
            'courses' => $courses,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Course::class);

        return Inertia::render('instructor/courses/create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $course = Course::create([
            ...$data,
            'instructor_id' => $request->user()->id,
            'slug' => Course::generateUniqueSlug($data['title']),
            'thumbnail' => $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('course-thumbnails', 'public')
                : null,
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Course created.');
    }

    public function show(Course $course): Response
    {
        Gate::authorize('view', $course);

        $course->load(['sections.lessons.quizzes', 'quizzes', 'instructor:id,name']);

        return Inertia::render('instructor/courses/show', [
            'course' => $course,
        ]);
    }

    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        return Inertia::render('instructor/courses/edit', [
            'course' => $course,
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();

        $thumbnail = $course->thumbnail;

        if ($request->hasFile('thumbnail')) {
            if ($thumbnail) {
                Storage::disk('public')->delete($thumbnail);
            }

            $thumbnail = $request->file('thumbnail')->store('course-thumbnails', 'public');
        }

        $course->update([
            ...$data,
            'slug' => $course->title === $data['title']
                ? $course->slug
                : Course::generateUniqueSlug($data['title'], $course->id),
            'thumbnail' => $thumbnail,
            'published_at' => $data['status'] === 'published' && ! $course->published_at
                ? now()
                : $course->published_at,
        ]);

        return redirect()->route('courses.edit', $course)
            ->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('delete', $course);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Course deleted.');
    }
}
