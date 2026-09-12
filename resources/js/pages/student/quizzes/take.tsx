import { Head, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';
import { submit } from '@/actions/App/Http/Controllers/Student/QuizAttemptController';
import SafeHtml from '@/components/editor/SafeHtml';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import type { Quiz, QuizAttempt, TakingQuestion } from '@/types';

type AnswerInput = {
    question_id: number;
    selected_option_id: number | null;
    answer_text: string;
};

export default function TakeQuiz({
    quiz,
    attempt,
    questions,
}: {
    quiz: Quiz;
    attempt: QuizAttempt;
    questions: TakingQuestion[];
}) {
    const { data, setData, post, processing } = useForm<{
        answers: AnswerInput[];
    }>({
        answers: questions.map((question) => ({
            question_id: question.id,
            selected_option_id: null,
            answer_text: '',
        })),
    });

    const setOption = (questionId: number, optionId: number) => {
        setData(
            'answers',
            data.answers.map((answer) =>
                answer.question_id === questionId
                    ? { ...answer, selected_option_id: optionId }
                    : answer,
            ),
        );
    };

    const setText = (questionId: number, text: string) => {
        setData(
            'answers',
            data.answers.map((answer) =>
                answer.question_id === questionId
                    ? { ...answer, answer_text: text }
                    : answer,
            ),
        );
    };

    const onSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        if (
            !confirm(
                'Submit the quiz? You cannot change your answers after submitting.',
            )
        ) {
            return;
        }

        post(submit(attempt.id).url);
    };

    return (
        <>
            <Head title={quiz.title} />

            <form
                onSubmit={onSubmit}
                className="mx-auto max-w-2xl space-y-6 p-4"
            >
                <div>
                    <h1 className="text-2xl font-semibold">{quiz.title}</h1>
                    {quiz.time_limit && (
                        <p className="text-muted-foreground text-sm">
                            Time limit: {quiz.time_limit} minutes
                        </p>
                    )}
                </div>

                {questions.map((question, index) => {
                    const answer = data.answers.find(
                        (a) => a.question_id === question.id,
                    );

                    return (
                        <div
                            key={question.id}
                            className="space-y-3 rounded-lg border p-4"
                        >
                            <p className="text-muted-foreground text-xs">
                                Question {index + 1} of {questions.length} ·{' '}
                                {question.points} pt
                                {question.points === 1 ? '' : 's'}
                            </p>
                            <SafeHtml html={question.question_text} />

                            {question.type === 'multiple_choice' ||
                            question.type === 'true_false' ? (
                                <div className="space-y-2">
                                    {question.options.map((option) => (
                                        <label
                                            key={option.id}
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <input
                                                type="radio"
                                                name={`question-${question.id}`}
                                                checked={
                                                    answer?.selected_option_id ===
                                                    option.id
                                                }
                                                onChange={() =>
                                                    setOption(
                                                        question.id,
                                                        option.id,
                                                    )
                                                }
                                            />
                                            {option.option_text}
                                        </label>
                                    ))}
                                </div>
                            ) : (
                                <Textarea
                                    value={answer?.answer_text ?? ''}
                                    onChange={(e) =>
                                        setText(question.id, e.target.value)
                                    }
                                    rows={4}
                                    placeholder="Your answer..."
                                />
                            )}
                        </div>
                    );
                })}

                <Button type="submit" disabled={processing}>
                    Submit quiz
                </Button>
            </form>
        </>
    );
}
