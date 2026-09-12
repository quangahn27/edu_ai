<?php

namespace App\Http\Controllers\Student;

use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuizAttempt\SubmitQuizAttemptRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    public function take(Request $request, Quiz $quiz): Response
    {
        $user = $request->user();

        $attempt = $quiz->attempts()
            ->where('user_id', $user->id)
            ->where('status', QuizAttemptStatus::InProgress)
            ->first();

        if (! $attempt) {
            Gate::authorize('create', [QuizAttempt::class, $quiz]);

            $attemptCount = $quiz->attempts()->where('user_id', $user->id)->count();

            abort_if(
                $quiz->max_attempts !== null && $attemptCount >= $quiz->max_attempts,
                403,
                'You have used all of your attempts for this quiz.',
            );

            $attempt = $quiz->attempts()->create([
                'user_id' => $user->id,
                'started_at' => now(),
            ]);
        }

        $quiz->load('questions.options');

        return Inertia::render('student/quizzes/take', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $quiz->questions->map(fn (Question $question) => [
                'id' => $question->id,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'points' => $question->points,
                'options' => $question->options->map(fn (QuestionOption $option) => [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                ]),
            ]),
        ]);
    }

    public function submit(SubmitQuizAttemptRequest $request, QuizAttempt $attempt, QuizGradingService $grading): RedirectResponse
    {
        $grading->submit($attempt, $request->validated('answers'));

        return redirect()->route('quiz-attempts.show', $attempt)
            ->with('success', 'Quiz submitted.');
    }

    public function show(QuizAttempt $attempt): Response
    {
        Gate::authorize('view', $attempt);

        $attempt->load(['quiz.questions.options', 'answers']);

        return Inertia::render('student/quizzes/result', [
            'attempt' => $attempt,
        ]);
    }
}
