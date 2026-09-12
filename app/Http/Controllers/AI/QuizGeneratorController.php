<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateQuizRequest;
use App\Http\Requests\AI\SaveGeneratedQuestionsRequest;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\QuizGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class QuizGeneratorController extends Controller
{
    public function generate(GenerateQuizRequest $request, Lesson $lesson, QuizGeneratorService $service): JsonResponse
    {
        try {
            $result = $service->generate($request->user(), $lesson, [
                'question_count' => $request->integer('question_count'),
                'difficulty' => $request->string('difficulty')->toString(),
                'question_type' => $request->string('question_type')->toString(),
                'language' => $request->input('language'),
                'additional_instructions' => $request->input('additional_instructions'),
            ]);
        } catch (AIRequestException $e) {
            report($e);

            return response()->json([
                'message' => 'Không thể kết nối trợ lý AI lúc này. Vui lòng thử lại.',
            ], 503);
        }

        return response()->json($result);
    }

    public function save(SaveGeneratedQuestionsRequest $request, Quiz $quiz): RedirectResponse
    {
        DB::transaction(function () use ($request, $quiz) {
            $sortOrder = $quiz->questions()->max('sort_order') + 1;

            foreach ($request->validated('questions') as $questionData) {
                $question = $quiz->questions()->create([
                    'type' => $questionData['type'],
                    'question_text' => $questionData['question_text'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'points' => $questionData['points'],
                    'sort_order' => $sortOrder++,
                ]);

                foreach ($questionData['options'] ?? [] as $index => $option) {
                    $question->options()->create([
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'] ?? false,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Questions added from AI generator.');
    }
}
