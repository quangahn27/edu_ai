import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';
import RichTextEditor from '@/components/editor/RichTextEditor';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Quiz } from '@/types';

type QuizFormData = {
    title: string;
    description: string;
    time_limit: string;
    passing_score: string;
    max_attempts: string;
    shuffle_questions: boolean;
    status: string;
};

interface QuizFormProps {
    quiz?: Quiz;
    action: { url: string; method: 'post' | 'patch' };
    submitLabel: string;
}

export default function QuizForm({ quiz, action, submitLabel }: QuizFormProps) {
    const { data, setData, post, patch, processing, errors } =
        useForm<QuizFormData>({
            title: quiz?.title ?? '',
            description: quiz?.description ?? '',
            time_limit: quiz?.time_limit ? String(quiz.time_limit) : '',
            passing_score: quiz ? String(quiz.passing_score) : '60',
            max_attempts: quiz?.max_attempts ? String(quiz.max_attempts) : '',
            shuffle_questions: quiz?.shuffle_questions ?? false,
            status: quiz?.status ?? 'draft',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (action.method === 'patch') {
            patch(action.url);
        } else {
            post(action.url);
        }
    };

    return (
        <form onSubmit={submit} className="max-w-2xl space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="title">Title</Label>
                <Input
                    id="title"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    required
                />
                <InputError message={errors.title} />
            </div>

            <div className="grid gap-2">
                <Label>Description</Label>
                <RichTextEditor
                    value={data.description}
                    onChange={(value) => setData('description', value)}
                    placeholder="Quiz description..."
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <div className="grid gap-2">
                    <Label htmlFor="time_limit">Time limit (minutes)</Label>
                    <Input
                        id="time_limit"
                        type="number"
                        min={1}
                        value={data.time_limit}
                        onChange={(e) => setData('time_limit', e.target.value)}
                        placeholder="No limit"
                    />
                    <InputError message={errors.time_limit} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="passing_score">Passing score (%)</Label>
                    <Input
                        id="passing_score"
                        type="number"
                        min={0}
                        max={100}
                        value={data.passing_score}
                        onChange={(e) =>
                            setData('passing_score', e.target.value)
                        }
                        required
                    />
                    <InputError message={errors.passing_score} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="max_attempts">Max attempts</Label>
                    <Input
                        id="max_attempts"
                        type="number"
                        min={1}
                        value={data.max_attempts}
                        onChange={(e) =>
                            setData('max_attempts', e.target.value)
                        }
                        placeholder="Unlimited"
                    />
                    <InputError message={errors.max_attempts} />
                </div>
            </div>

            <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="shuffle_questions"
                        checked={data.shuffle_questions}
                        onCheckedChange={(checked) =>
                            setData('shuffle_questions', checked === true)
                        }
                    />
                    <Label htmlFor="shuffle_questions" className="font-normal">
                        Shuffle questions
                    </Label>
                </div>

                <div className="grid gap-2">
                    <Select
                        value={data.status}
                        onValueChange={(value) => setData('status', value)}
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="published">Published</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.status} />
                </div>
            </div>

            <Button type="submit" disabled={processing}>
                {submitLabel}
            </Button>
        </form>
    );
}
