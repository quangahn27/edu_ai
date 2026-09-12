<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Http\Requests\EssayGrading\GradeEssayRequest;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Services\QuizGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EssayGradingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $answers = QuizAnswer::query()
            ->whereHas('question', fn ($query) => $query->where('type', QuestionType::Essay))
            ->whereDoesntHave('essayGrading', fn ($query) => $query->whereNotNull('teacher_score'))
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $ownedQuizIds = Quiz::query()
                    ->where(function ($q) use ($user) {
                        $q->whereHas('course', fn ($c) => $c->where('instructor_id', $user->id))
                            ->orWhereHas('lesson.section.course', fn ($c) => $c->where('instructor_id', $user->id));
                    })
                    ->pluck('id');

                $query->whereHas('question', fn ($q) => $q->whereIn('quiz_id', $ownedQuizIds));
            })
            ->with(['question:id,question_text,points', 'attempt:id,user_id,quiz_id', 'attempt.user:id,name', 'attempt.quiz:id,title'])
            ->latest()
            ->paginate(20);

        return Inertia::render('instructor/essays/index', [
            'answers' => $answers,
        ]);
    }

    public function show(QuizAnswer $answer): Response
    {
        Gate::authorize('view', $answer->attempt);

        $answer->load(['question', 'attempt.user:id,name', 'attempt.quiz:id,title', 'essayGrading']);

        return Inertia::render('instructor/essays/show', [
            'answer' => $answer,
        ]);
    }

    public function update(GradeEssayRequest $request, QuizAnswer $answer, QuizGradingService $grading): RedirectResponse
    {
        $essayGrading = $answer->essayGrading()->updateOrCreate([], [
            'teacher_score' => $request->validated('teacher_score'),
            'teacher_feedback' => $request->validated('teacher_feedback'),
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        $answer->update(['score' => $essayGrading->teacher_score]);

        $grading->recalculateAfterManualGrading($answer->attempt);

        return redirect()->route('essays.index')
            ->with('success', 'Grade saved.');
    }
}
