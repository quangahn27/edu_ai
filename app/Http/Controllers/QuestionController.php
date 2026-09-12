<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\ReorderQuestionsRequest;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($quiz, $data) {
            $question = $quiz->questions()->create([
                ...collect($data)->except('options')->all(),
                'sort_order' => $quiz->questions()->max('sort_order') + 1,
            ]);

            foreach ($data['options'] ?? [] as $index => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'] ?? false,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Question added.');
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($question, $data) {
            $question->update(collect($data)->except('options')->all());

            $keptOptionIds = [];

            foreach ($data['options'] ?? [] as $index => $option) {
                $saved = $question->options()->updateOrCreate(
                    ['id' => $option['id'] ?? null],
                    [
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'] ?? false,
                        'sort_order' => $index,
                    ],
                );

                $keptOptionIds[] = $saved->id;
            }

            $question->options()->whereNotIn('id', $keptOptionIds)->delete();
        });

        return redirect()->route('quizzes.show', $question->quiz_id)
            ->with('success', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        Gate::authorize('delete', $question);

        $quizId = $question->quiz_id;
        $question->delete();

        return redirect()->route('quizzes.show', $quizId)
            ->with('success', 'Question deleted.');
    }

    public function reorder(ReorderQuestionsRequest $request, Quiz $quiz): RedirectResponse
    {
        foreach ($request->validated('question_ids') as $index => $questionId) {
            Question::whereKey($questionId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Questions reordered.');
    }
}
