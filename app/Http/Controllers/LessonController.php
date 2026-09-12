<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\ReorderLessonsRequest;
use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function create(Section $section): Response
    {
        Gate::authorize('create', [Lesson::class, $section]);

        $section->load('course:id,title');

        return Inertia::render('instructor/lessons/create', [
            'section' => $section,
        ]);
    }

    public function store(StoreLessonRequest $request, Section $section): RedirectResponse
    {
        $data = $request->validated();

        $lesson = $section->lessons()->create([
            ...$data,
            'slug' => Lesson::generateUniqueSlug($data['title']),
            'sort_order' => $section->lessons()->max('sort_order') + 1,
        ]);

        return redirect()->route('lessons.edit', $lesson)
            ->with('success', 'Lesson created.');
    }

    public function edit(Lesson $lesson): Response
    {
        Gate::authorize('update', $lesson);

        $lesson->load('section.course:id,title');

        return Inertia::render('instructor/lessons/edit', [
            'lesson' => $lesson,
        ]);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validated();

        $lesson->update([
            ...$data,
            'slug' => $lesson->title === $data['title']
                ? $lesson->slug
                : Lesson::generateUniqueSlug($data['title'], $lesson->id),
        ]);

        return redirect()->route('lessons.edit', $lesson)
            ->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        Gate::authorize('delete', $lesson);

        $courseId = $lesson->section->course_id;
        $lesson->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Lesson deleted.');
    }

    public function reorder(ReorderLessonsRequest $request, Section $section): RedirectResponse
    {
        foreach ($request->validated('lesson_ids') as $index => $lessonId) {
            Lesson::whereKey($lessonId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Lessons reordered.');
    }
}
