import { router, useHttp } from '@inertiajs/react';
import { useState } from 'react';
import {
    generate,
    save,
} from '@/actions/App/Http/Controllers/AI/QuizGeneratorController';
import AiGeneratedQuestion from '@/components/ai/AiGeneratedQuestion';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { GeneratedQuestion } from '@/types';

type GenerateForm = {
    question_count: number;
    difficulty: string;
    question_type: string;
    language: string;
    additional_instructions: string;
};

type GenerateResponse = {
    generation_id: number;
    questions: GeneratedQuestion[];
};

type GenerateErrorResponse = { message: string };

export default function QuizGeneratorModal({
    lessonId,
    quizId,
    open,
    onOpenChange,
}: {
    lessonId: number;
    quizId: number;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [questions, setQuestions] = useState<GeneratedQuestion[] | null>(
        null,
    );
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [error, setError] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);

    const { data, setData, post, processing } = useHttp<
        GenerateForm,
        GenerateResponse
    >('post', generate(lessonId).url, {
        question_count: 5,
        difficulty: 'medium',
        question_type: 'multiple_choice',
        language: 'Vietnamese',
        additional_instructions: '',
    });

    const reset = () => {
        setQuestions(null);
        setSelected(new Set());
        setError(null);
    };

    const handleGenerate = () => {
        setError(null);

        void post(generate(lessonId).url, {
            onSuccess: (response) => {
                setQuestions(response.questions);
                setSelected(new Set(response.questions.map((_, i) => i)));
            },
            onError: (errors) => {
                const body = errors as unknown as GenerateErrorResponse;
                setError(
                    body?.message ??
                        'Không thể kết nối trợ lý AI lúc này. Vui lòng thử lại.',
                );
            },
        });
    };

    const handleSave = () => {
        if (!questions) return;

        setSaving(true);
        router.post(
            save(quizId).url,
            {
                questions: questions.filter((_, i) => selected.has(i)),
            },
            {
                onFinish: () => setSaving(false),
                onSuccess: () => {
                    onOpenChange(false);
                    reset();
                },
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => {
                onOpenChange(next);
                if (!next) reset();
            }}
        >
            <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Generate Questions with AI</DialogTitle>
                </DialogHeader>

                {!questions ? (
                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label>Number of questions</Label>
                                <Select
                                    value={String(data.question_count)}
                                    onValueChange={(value) =>
                                        setData('question_count', Number(value))
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="5">5</SelectItem>
                                        <SelectItem value="10">10</SelectItem>
                                        <SelectItem value="20">20</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label>Difficulty</Label>
                                <Select
                                    value={data.difficulty}
                                    onValueChange={(value) =>
                                        setData('difficulty', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="easy">
                                            Easy
                                        </SelectItem>
                                        <SelectItem value="medium">
                                            Medium
                                        </SelectItem>
                                        <SelectItem value="hard">
                                            Hard
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label>Question type</Label>
                                <Select
                                    value={data.question_type}
                                    onValueChange={(value) =>
                                        setData('question_type', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="multiple_choice">
                                            Multiple choice
                                        </SelectItem>
                                        <SelectItem value="true_false">
                                            True / False
                                        </SelectItem>
                                        <SelectItem value="short_answer">
                                            Short answer
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label>Language</Label>
                                <Input
                                    value={data.language}
                                    onChange={(e) =>
                                        setData('language', e.target.value)
                                    }
                                />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label>Additional instructions (optional)</Label>
                            <Textarea
                                value={data.additional_instructions}
                                onChange={(e) =>
                                    setData(
                                        'additional_instructions',
                                        e.target.value,
                                    )
                                }
                                rows={2}
                            />
                        </div>

                        {error && (
                            <p className="text-destructive text-sm">{error}</p>
                        )}

                        <Button onClick={handleGenerate} disabled={processing}>
                            {processing ? 'Generating...' : 'Generate'}
                        </Button>
                    </div>
                ) : (
                    <div className="space-y-4">
                        <p className="text-muted-foreground text-sm">
                            Review, edit, and select the questions to add to
                            this quiz.
                        </p>

                        <div className="space-y-3">
                            {questions.map((question, index) => (
                                <AiGeneratedQuestion
                                    key={index}
                                    question={question}
                                    selected={selected.has(index)}
                                    onToggleSelected={(isSelected) => {
                                        setSelected((prev) => {
                                            const next = new Set(prev);
                                            if (isSelected) {
                                                next.add(index);
                                            } else {
                                                next.delete(index);
                                            }
                                            return next;
                                        });
                                    }}
                                    onChange={(updated) => {
                                        setQuestions((prev) =>
                                            (prev ?? []).map((q, i) =>
                                                i === index ? updated : q,
                                            ),
                                        );
                                    }}
                                />
                            ))}
                        </div>

                        <div className="flex gap-2">
                            <Button
                                onClick={handleSave}
                                disabled={saving || selected.size === 0}
                            >
                                Save selected questions ({selected.size})
                            </Button>
                            <Button variant="outline" onClick={reset}>
                                Start over
                            </Button>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
