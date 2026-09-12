<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\StoreQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function storeForCourse(StoreQuizRequest $request, Course $course): RedirectResponse
    {
        $quiz = $course->quizzes()->create($request->validated());

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created.');
    }

    public function storeForLesson(StoreQuizRequest $request, Lesson $lesson): RedirectResponse
    {
        $quiz = $lesson->quizzes()->create($request->validated());

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created.');
    }

    public function show(Quiz $quiz): Response
    {
        Gate::authorize('view', $quiz);

        $quiz->load('questions.options');

        return Inertia::render('instructor/quizzes/show', [
            'quiz' => $quiz,
            'course' => $quiz->resolveCourse(),
        ]);
    }

    public function edit(Quiz $quiz): Response
    {
        Gate::authorize('update', $quiz);

        return Inertia::render('instructor/quizzes/edit', [
            'quiz' => $quiz,
        ]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $quiz->update($request->validated());

        return redirect()->route('quizzes.edit', $quiz)
            ->with('success', 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        Gate::authorize('delete', $quiz);

        $course = $quiz->resolveCourse();
        $quiz->delete();

        return redirect()->route('courses.show', $course)
            ->with('success', 'Quiz deleted.');
    }
}
