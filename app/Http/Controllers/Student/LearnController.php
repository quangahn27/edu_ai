<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    public function show(Request $request, Lesson $lesson): Response
    {
        Gate::authorize('view', $lesson);

        $lesson->load('section.course');
        $course = $lesson->section->course;

        $course->load(['sections.lessons' => function ($query) use ($request) {
            $query->with(['progress' => function ($progress) use ($request) {
                $progress->where('user_id', $request->user()->id);
            }]);
        }]);

        $orderedLessons = $course->sections
            ->flatMap(fn ($section) => $section->lessons);

        $currentIndex = $orderedLessons->search(
            fn (Lesson $item) => $item->id === $lesson->id,
        );

        $progress = $lesson->progress()
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('student/learn/show', [
            'course' => $course,
            'lesson' => $lesson,
            'progress' => $progress,
            'previousLesson' => $currentIndex > 0
                ? $orderedLessons->get($currentIndex - 1)
                : null,
            'nextLesson' => $currentIndex < $orderedLessons->count() - 1
                ? $orderedLessons->get($currentIndex + 1)
                : null,
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('view', $lesson);

        $lesson->progress()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['completed_at' => now(), 'progress_percent' => 100],
        );

        return back()->with('success', 'Lesson marked as completed.');
    }
}
