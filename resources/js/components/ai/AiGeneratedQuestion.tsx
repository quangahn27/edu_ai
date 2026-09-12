import { useId } from 'react';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import type { GeneratedQuestion } from '@/types';

interface AiGeneratedQuestionProps {
    question: GeneratedQuestion;
    selected: boolean;
    onToggleSelected: (selected: boolean) => void;
    onChange: (question: GeneratedQuestion) => void;
}

const typeLabel: Record<GeneratedQuestion['type'], string> = {
    multiple_choice: 'Multiple choice',
    true_false: 'True / False',
    short_answer: 'Short answer',
    essay: 'Essay',
};

export default function AiGeneratedQuestion({
    question,
    selected,
    onToggleSelected,
    onChange,
}: AiGeneratedQuestionProps) {
    const groupId = useId();

    const setCorrect = (index: number) => {
        onChange({
            ...question,
            options: question.options.map((option, i) => ({
                ...option,
                is_correct: i === index,
            })),
        });
    };

    const updateOptionText = (index: number, text: string) => {
        onChange({
            ...question,
            options: question.options.map((option, i) =>
                i === index ? { ...option, option_text: text } : option,
            ),
        });
    };

    return (
        <div className="flex gap-3 rounded-lg border p-4">
            <Checkbox
                checked={selected}
                onCheckedChange={(checked) =>
                    onToggleSelected(checked === true)
                }
                className="mt-1"
                aria-label="Include this question"
            />
            <div className="flex-1 space-y-3">
                <div className="flex items-center justify-between gap-2">
                    <span className="text-muted-foreground text-xs font-medium">
                        {typeLabel[question.type]}
                        {question.difficulty && ` · ${question.difficulty}`}
                    </span>
                    <div className="flex items-center gap-1 text-xs">
                        Points:
                        <Input
                            type="number"
                            min={1}
                            value={question.points}
                            onChange={(e) =>
                                onChange({
                                    ...question,
                                    points: Number(e.target.value) || 1,
                                })
                            }
                            className="h-7 w-16"
                        />
                    </div>
                </div>

                <Textarea
                    value={question.question_text}
                    onChange={(e) =>
                        onChange({ ...question, question_text: e.target.value })
                    }
                    rows={2}
                />

                {question.options.length > 0 && (
                    <div className="space-y-1.5">
                        {question.options.map((option, index) => (
                            <div
                                key={index}
                                className="flex items-center gap-2"
                            >
                                <input
                                    type="radio"
                                    name={groupId}
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
                                    className="h-8 flex-1"
                                />
                            </div>
                        ))}
                    </div>
                )}

                {question.explanation && (
                    <Textarea
                        value={question.explanation}
                        onChange={(e) =>
                            onChange({
                                ...question,
                                explanation: e.target.value,
                            })
                        }
                        placeholder="Explanation"
                        rows={2}
                        className="text-muted-foreground text-sm"
                    />
                )}
            </div>
        </div>
    );
}
