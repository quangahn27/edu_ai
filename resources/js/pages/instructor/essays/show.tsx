import { Head, useForm } from '@inertiajs/react';
import { useState, type FormEventHandler } from 'react';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/EssayGradingController';
import EssayAiGrading from '@/components/ai/EssayAiGrading';
import SafeHtml from '@/components/editor/SafeHtml';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { EssayGrading, QuizAnswer } from '@/types';

export default function EssayShow({ answer }: { answer: QuizAnswer }) {
    const [grading, setGrading] = useState<EssayGrading | null>(
        answer.essayGrading ?? null,
    );
    const question = answer.question!;

    const { data, setData, patch, processing, errors } = useForm({
        teacher_score: grading?.teacher_score
            ? String(grading.teacher_score)
            : '',
        teacher_feedback: grading?.teacher_feedback ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(update(answer.id).url);
    };

    return (
        <>
            <Head title="Grade essay" />

            <div className="mx-auto max-w-3xl space-y-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Question</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <SafeHtml html={question.question_text} />

                        {question.reference_answer && (
                            <div>
                                <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">
                                    Reference answer
                                </p>
                                <SafeHtml html={question.reference_answer} />
                            </div>
                        )}

                        <div>
                            <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">
                                Student answer
                            </p>
                            <p className="bg-muted mt-1 rounded p-3 text-sm whitespace-pre-wrap">
                                {answer.answer_text}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <EssayAiGrading
                    answer={answer}
                    grading={grading}
                    onGraded={setGrading}
                    onAccept={(score, feedback) => {
                        setData({
                            teacher_score: String(score),
                            teacher_feedback: feedback,
                        });
                    }}
                />

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="teacher_score">
                            Teacher score (max {question.points})
                        </Label>
                        <Input
                            id="teacher_score"
                            type="number"
                            min={0}
                            max={question.points}
                            step="0.5"
                            value={data.teacher_score}
                            onChange={(e) =>
                                setData('teacher_score', e.target.value)
                            }
                            required
                        />
                        {errors.teacher_score && (
                            <p className="text-destructive text-xs">
                                {errors.teacher_score}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="teacher_feedback">
                            Feedback to student
                        </Label>
                        <Textarea
                            id="teacher_feedback"
                            value={data.teacher_feedback}
                            onChange={(e) =>
                                setData('teacher_feedback', e.target.value)
                            }
                            rows={4}
                        />
                    </div>

                    <Button type="submit" disabled={processing}>
                        Save grade
                    </Button>
                </form>
            </div>
        </>
    );
}

EssayShow.layout = {
    breadcrumbs: [{ title: 'Essay grading', href: index() }],
};
