import { useHttp } from '@inertiajs/react';
import { generate } from '@/actions/App/Http/Controllers/AI/EssayGradingController';
import { Button } from '@/components/ui/button';
import type { EssayGrading, QuizAnswer } from '@/types';

type GenerateResponse = { grading: EssayGrading };
type GenerateErrorResponse = { message: string };

export default function EssayAiGrading({
    answer,
    grading,
    onGraded,
    onAccept,
}: {
    answer: QuizAnswer;
    grading: EssayGrading | null;
    onGraded: (grading: EssayGrading) => void;
    onAccept: (score: number, feedback: string) => void;
}) {
    const { post, processing, errors } = useHttp<
        Record<string, never>,
        GenerateResponse
    >('post', generate(answer.id).url, {});

    const requestSuggestion = () => {
        void post(generate(answer.id).url, {
            onSuccess: (response) => onGraded(response.grading),
        });
    };

    const error = (errors as unknown as GenerateErrorResponse | undefined)
        ?.message;

    return (
        <div className="space-y-3 rounded-lg border p-4">
            <div className="flex items-center justify-between">
                <h3 className="text-sm font-semibold">AI Grading Assistant</h3>
                <Button
                    size="sm"
                    variant="outline"
                    onClick={requestSuggestion}
                    disabled={processing}
                >
                    {processing
                        ? 'Grading...'
                        : grading
                          ? 'Regenerate suggestion'
                          : 'Get AI suggestion'}
                </Button>
            </div>

            {error && <p className="text-destructive text-sm">{error}</p>}

            {grading?.ai_suggested_score !== null &&
                grading?.ai_suggested_score !== undefined && (
                    <div className="space-y-2 text-sm">
                        <p>
                            AI Suggested Score:{' '}
                            <strong>{grading.ai_suggested_score}</strong> /{' '}
                            {answer.question?.points}
                        </p>

                        {grading.grading_details?.criteria && (
                            <ul className="space-y-1">
                                {grading.grading_details.criteria.map(
                                    (criterion, index) => (
                                        <li
                                            key={index}
                                            className="text-muted-foreground"
                                        >
                                            {criterion.name}: {criterion.score}/
                                            {criterion.max_score} —{' '}
                                            {criterion.comment}
                                        </li>
                                    ),
                                )}
                            </ul>
                        )}

                        {grading.ai_feedback && (
                            <p className="text-muted-foreground">
                                {grading.ai_feedback}
                            </p>
                        )}

                        <Button
                            size="sm"
                            onClick={() =>
                                onAccept(
                                    grading.ai_suggested_score ?? 0,
                                    grading.ai_feedback ?? '',
                                )
                            }
                        >
                            Accept AI Score
                        </Button>
                    </div>
                )}
        </div>
    );
}
