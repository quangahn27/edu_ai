<?php

namespace App\Services;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

class QuizGradingService
{
    /**
     * Persist the student's submitted answers, auto-grading the
     * multiple-choice and true/false questions, and finalize the attempt.
     *
     * @param  array<int, array{question_id: int, selected_option_id?: int|null, answer_text?: string|null}>  $answers
     */
    public function submit(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        DB::transaction(function () use ($attempt, $answers) {
            $questions = $attempt->quiz->questions()->with('options')->get()->keyBy('id');

            foreach ($answers as $answer) {
                $question = $questions->get($answer['question_id']);

                if (! $question) {
                    continue;
                }

                $isAutoGradable = $question->type->isAutoGradable();
                $selectedOptionId = $answer['selected_option_id'] ?? null;
                $isCorrect = null;
                $score = null;

                if ($isAutoGradable) {
                    $correctOptionId = $question->options
                        ->firstWhere('is_correct', true)?->id;

                    $isCorrect = $selectedOptionId !== null && $selectedOptionId === $correctOptionId;
                    $score = $isCorrect ? $question->points : 0;
                }

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'selected_option_id' => $selectedOptionId,
                        'answer_text' => $answer['answer_text'] ?? null,
                        'is_correct' => $isCorrect,
                        'score' => $score,
                    ],
                );
            }

            $allAutoGradable = $questions->every(
                fn ($question) => $question->type->isAutoGradable(),
            );

            $earnedPoints = $attempt->answers()->whereNotNull('score')->sum('score');
            $totalPoints = $questions->sum('points');

            $attempt->update([
                'submitted_at' => now(),
                'status' => $allAutoGradable ? QuizAttemptStatus::Graded : QuizAttemptStatus::Submitted,
                'score' => $allAutoGradable ? $earnedPoints : null,
                'percentage' => $allAutoGradable && $totalPoints > 0
                    ? round(($earnedPoints / $totalPoints) * 100, 2)
                    : null,
            ]);
        });

        return $attempt->fresh(['answers']);
    }

    /**
     * Recompute an attempt's aggregate score once every answer has been
     * graded (auto-graded at submission time, or manually graded since).
     */
    public function recalculateAfterManualGrading(QuizAttempt $attempt): QuizAttempt
    {
        $totalQuestions = $attempt->quiz->questions()->count();
        $gradedAnswers = $attempt->answers()->whereNotNull('score')->get();

        if ($gradedAnswers->count() < $totalQuestions) {
            return $attempt;
        }

        $totalPoints = $attempt->quiz->questions()->sum('points');
        $earnedPoints = $gradedAnswers->sum('score');

        $attempt->update([
            'status' => QuizAttemptStatus::Graded,
            'score' => $earnedPoints,
            'percentage' => $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : null,
        ]);

        return $attempt->fresh(['answers']);
    }
}
