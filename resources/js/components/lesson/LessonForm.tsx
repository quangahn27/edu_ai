import { useForm } from '@inertiajs/react';
import { type FormEventHandler, useState } from 'react';
import RichTextEditor from '@/components/editor/RichTextEditor';
import SafeHtml from '@/components/editor/SafeHtml';
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
import { Textarea } from '@/components/ui/textarea';
import type { Lesson } from '@/types';

type LessonFormData = {
    title: string;
    summary: string;
    content: string;
    video_url: string;
    duration: string;
    is_preview: boolean;
    status: string;
};

interface LessonFormProps {
    lesson?: Lesson;
    action: { url: string; method: 'post' | 'patch' };
    submitLabel: string;
    showPreviewTab?: boolean;
}

export default function LessonForm({
    lesson,
    action,
    submitLabel,
    showPreviewTab = false,
}: LessonFormProps) {
    const [tab, setTab] = useState<'edit' | 'preview'>('edit');
    const { data, setData, post, patch, processing, errors } =
        useForm<LessonFormData>({
            title: lesson?.title ?? '',
            summary: lesson?.summary ?? '',
            content: lesson?.content ?? '',
            video_url: lesson?.video_url ?? '',
            duration: lesson?.duration ? String(lesson.duration) : '',
            is_preview: lesson?.is_preview ?? false,
            status: lesson?.status ?? 'draft',
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
        <form onSubmit={submit} className="max-w-3xl space-y-6">
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
                <Label htmlFor="summary">Summary</Label>
                <Textarea
                    id="summary"
                    value={data.summary}
                    onChange={(e) => setData('summary', e.target.value)}
                    rows={2}
                />
                <InputError message={errors.summary} />
            </div>

            <div className="grid gap-2">
                <div className="flex items-center justify-between">
                    <Label>Content</Label>
                    {showPreviewTab && (
                        <div className="flex gap-1">
                            <Button
                                type="button"
                                size="sm"
                                variant={tab === 'edit' ? 'secondary' : 'ghost'}
                                onClick={() => setTab('edit')}
                            >
                                Edit
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant={
                                    tab === 'preview' ? 'secondary' : 'ghost'
                                }
                                onClick={() => setTab('preview')}
                            >
                                Preview
                            </Button>
                        </div>
                    )}
                </div>

                {tab === 'edit' ? (
                    <RichTextEditor
                        value={data.content}
                        onChange={(value) => setData('content', value)}
                        placeholder="Lesson content..."
                    />
                ) : (
                    <div className="rounded-md border p-4">
                        <SafeHtml html={data.content} />
                    </div>
                )}
                <InputError message={errors.content} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="video_url">Video URL</Label>
                    <Input
                        id="video_url"
                        type="url"
                        value={data.video_url}
                        onChange={(e) => setData('video_url', e.target.value)}
                        placeholder="https://..."
                    />
                    <InputError message={errors.video_url} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="duration">Duration (minutes)</Label>
                    <Input
                        id="duration"
                        type="number"
                        min={0}
                        value={data.duration}
                        onChange={(e) => setData('duration', e.target.value)}
                    />
                    <InputError message={errors.duration} />
                </div>
            </div>

            <div className="flex items-center gap-4">
                <div className="grid gap-2">
                    <Label>Status</Label>
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

                <div className="flex items-center gap-2 pt-6">
                    <Checkbox
                        id="is_preview"
                        checked={data.is_preview}
                        onCheckedChange={(checked) =>
                            setData('is_preview', checked === true)
                        }
                    />
                    <Label htmlFor="is_preview" className="font-normal">
                        Free preview (visible to non-enrolled students)
                    </Label>
                </div>
            </div>

            <Button type="submit" disabled={processing}>
                {submitLabel}
            </Button>
        </form>
    );
}
