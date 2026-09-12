import { useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { type FormEventHandler, useEffect } from 'react';
import RichTextEditor from '@/components/editor/RichTextEditor';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { Question, QuestionType } from '@/types';

type OptionInput = { id?: number; option_text: string; is_correct: boolean };

type QuestionFormData = {
    type: QuestionType;
    question_text: string;
    explanation: string;
    reference_answer: string;
    points: string;
    options: OptionInput[];
};

interface QuestionFormProps {
    question?: Question;
    action: { url: string; method: 'post' | 'patch' };
    submitLabel: string;
    onSuccess?: () => void;
}

const emptyOptions: OptionInput[] = [
    { option_text: '', is_correct: false },
    { option_text: '', is_correct: false },
];

const trueFalseOptions: OptionInput[] = [
    { option_text: 'True', is_correct: false },
    { option_text: 'False', is_correct: false },
];

export default function QuestionForm({
    question,
    action,
    submitLabel,
    onSuccess,
}: QuestionFormProps) {
    const { data, setData, post, patch, processing, errors } =
        useForm<QuestionFormData>({
            type: question?.type ?? 'multiple_choice',
            question_text: question?.question_text ?? '',
            explanation: question?.explanation ?? '',
            reference_answer: question?.reference_answer ?? '',
            points: question ? String(question.points) : '1',
            options:
                question?.options?.map((option) => ({
                    id: option.id,
                    option_text: option.option_text,
                    is_correct: option.is_correct,
                })) ?? emptyOptions,
        });

    useEffect(() => {
        if (data.type === 'true_false' && data.options.length !== 2) {
            setData('options', trueFalseOptions);
        }

        if (
            (data.type === 'short_answer' || data.type === 'essay') &&
            data.options.length > 0
        ) {
            setData('options', []);
        }

        if (data.type === 'multiple_choice' && data.options.length === 0) {
            setData('options', emptyOptions);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.type]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        const options = {
            onSuccess: () => onSuccess?.(),
        };

        if (action.method === 'patch') {
            patch(action.url, options);
        } else {
            post(action.url, options);
        }
    };

    const showOptions =
        data.type === 'multiple_choice' || data.type === 'true_false';
    const isTrueFalse = data.type === 'true_false';

    const setCorrect = (index: number) => {
        setData(
            'options',
            data.options.map((option, i) => ({
                ...option,
                is_correct: i === index,
            })),
        );
    };

    const updateOptionText = (index: number, text: string) => {
        setData(
            'options',
            data.options.map((option, i) =>
                i === index ? { ...option, option_text: text } : option,
            ),
        );
    };

    const addOption = () => {
        setData('options', [
            ...data.options,
            { option_text: '', is_correct: false },
        ]);
    };

    const removeOption = (index: number) => {
        setData(
            'options',
            data.options.filter((_, i) => i !== index),
        );
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label>Type</Label>
                    <Select
                        value={data.type}
                        onValueChange={(value) =>
                            setData('type', value as QuestionType)
                        }
                    >
                        <SelectTrigger className="w-full">
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
                            <SelectItem value="essay">Essay</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.type} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="points">Points</Label>
                    <Input
                        id="points"
                        type="number"
                        min={1}
                        value={data.points}
                        onChange={(e) => setData('points', e.target.value)}
                        required
                    />
                    <InputError message={errors.points} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label>Question</Label>
                <RichTextEditor
                    value={data.question_text}
                    onChange={(value) => setData('question_text', value)}
                    placeholder="Enter the question..."
                />
                <InputError message={errors.question_text} />
            </div>

            {showOptions && (
                <div className="grid gap-2">
                    <Label>Options (select the correct one)</Label>
                    <div className="space-y-2">
                        {data.options.map((option, index) => (
                            <div
                                key={index}
                                className="flex items-center gap-2"
                            >
                                <input
                                    type="radio"
                                    name="correct-option"
                                    checked={option.is_correct}
                                    onChange={() => setCorrect(index)}
                                    className="size-4"
                                    aria-label={`Mark option ${index + 1} as correct`}
                                />
                                <Input
                                    value={option.option_text}
                                    onChange={(e) =>
                                        updateOptionText(index, e.target.value)
                                    }
                                    disabled={isTrueFalse}
                                    placeholder={`Option ${index + 1}`}
                                    className={cn('flex-1')}
                                />
                                {!isTrueFalse && (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => removeOption(index)}
                                        disabled={data.options.length <= 2}
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>
                    {!isTrueFalse && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={addOption}
                        >
                            Add option
                        </Button>
                    )}
                    <InputError message={errors.options} />
                </div>
            )}

            {data.type === 'essay' && (
                <div className="grid gap-2">
                    <Label>Reference answer (for AI grading)</Label>
                    <RichTextEditor
                        value={data.reference_answer}
                        onChange={(value) => setData('reference_answer', value)}
                        placeholder="Model answer used to guide grading..."
                    />
                    <InputError message={errors.reference_answer} />
                </div>
            )}

            <div className="grid gap-2">
                <Label>Explanation (optional)</Label>
                <RichTextEditor
                    value={data.explanation}
                    onChange={(value) => setData('explanation', value)}
                    placeholder="Shown to students after grading..."
                />
                <InputError message={errors.explanation} />
            </div>

            <Button type="submit" disabled={processing}>
                {submitLabel}
            </Button>
        </form>
    );
}
