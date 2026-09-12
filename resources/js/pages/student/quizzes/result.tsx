import { Head } from '@inertiajs/react';
import SafeHtml from '@/components/editor/SafeHtml';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { QuizAttempt } from '@/types';

export default function QuizResult({ attempt }: { attempt: QuizAttempt }) {
    const quiz = attempt.quiz!;
    const isGraded = attempt.status === 'graded';

    return (
        <>
            <Head title={`${quiz.title} - Results`} />

            <div className="mx-auto max-w-2xl space-y-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{quiz.title}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {isGraded ? (
                            <p className="text-lg">
                                Score: <strong>{attempt.score}</strong> ·{' '}
                                {attempt.percentage}%
                            </p>
                        ) : (
                            <Badge variant="secondary">
                                Some answers are pending manual grading
                            </Badge>
                        )}
                    </CardContent>
                </Card>

                {(quiz.questions ?? []).map((question, index) => {
                    const answer = (attempt.answers ?? []).find(
                        (a) => a.question_id === question.id,
                    );

                    return (
                        <div
                            key={question.id}
                            className="space-y-3 rounded-lg border p-4"
                        >
                            <p className="text-muted-foreground text-xs">
                                Question {index + 1} · {question.points} pt
                                {question.points === 1 ? '' : 's'}
                                {answer?.is_correct === true && (
                                    <span className="ml-2 text-green-600">
                                        Correct
                                    </span>
                                )}
                                {answer?.is_correct === false && (
                                    <span className="text-destructive ml-2">
                                        Incorrect
                                    </span>
                                )}
                            </p>
                            <SafeHtml html={question.question_text} />

                            {question.options && question.options.length > 0 ? (
                                <ul className="space-y-1 text-sm">
                                    {question.options.map((option) => (
                                        <li
                                            key={option.id}
                                            className={cn(
                                                'rounded px-2 py-1',
                                                option.is_correct &&
                                                    'bg-green-500/10 font-medium text-green-700 dark:text-green-400',
                                                !option.is_correct &&
                                                    answer?.selected_option_id ===
                                                        option.id &&
                                                    'bg-destructive/10 text-destructive',
                                            )}
                                        >
                                            {option.option_text}
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="bg-muted rounded p-2 text-sm">
                                    {answer?.answer_text || (
                                        <span className="text-muted-foreground">
                                            No answer submitted.
                                        </span>
                                    )}
                                </p>
                            )}

                            {question.explanation && (
                                <div className="text-muted-foreground text-sm">
                                    <strong>Explanation: </strong>
                                    <SafeHtml html={question.explanation} />
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </>
    );
}
