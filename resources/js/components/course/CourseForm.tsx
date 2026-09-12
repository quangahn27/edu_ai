import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';
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
import { Textarea } from '@/components/ui/textarea';
import type { Course } from '@/types';

type CourseFormData = {
    title: string;
    short_description: string;
    description: string;
    thumbnail: File | null;
    level: string;
    status: string;
};

interface CourseFormProps {
    course?: Course;
    action: { url: string; method: 'post' | 'put' };
    submitLabel: string;
}

export default function CourseForm({
    course,
    action,
    submitLabel,
}: CourseFormProps) {
    const { data, setData, post, put, processing, errors } =
        useForm<CourseFormData>({
            title: course?.title ?? '',
            short_description: course?.short_description ?? '',
            description: course?.description ?? '',
            thumbnail: null,
            level: course?.level ?? 'beginner',
            status: course?.status ?? 'draft',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (action.method === 'put') {
            put(action.url);
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
                <Label htmlFor="short_description">Short description</Label>
                <Textarea
                    id="short_description"
                    value={data.short_description}
                    onChange={(e) =>
                        setData('short_description', e.target.value)
                    }
                    rows={2}
                />
                <InputError message={errors.short_description} />
            </div>

            <div className="grid gap-2">
                <Label>Description</Label>
                <RichTextEditor
                    value={data.description}
                    onChange={(value) => setData('description', value)}
                    placeholder="Course description..."
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="thumbnail">Thumbnail</Label>
                {course?.thumbnail && (
                    <img
                        src={`/storage/${course.thumbnail}`}
                        alt=""
                        className="h-32 w-auto rounded-md object-cover"
                    />
                )}
                <Input
                    id="thumbnail"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    onChange={(e) =>
                        setData('thumbnail', e.target.files?.[0] ?? null)
                    }
                />
                <InputError message={errors.thumbnail} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label>Level</Label>
                    <Select
                        value={data.level}
                        onValueChange={(value) => setData('level', value)}
                    >
                        <SelectTrigger className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="beginner">Beginner</SelectItem>
                            <SelectItem value="intermediate">
                                Intermediate
                            </SelectItem>
                            <SelectItem value="advanced">Advanced</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.level} />
                </div>

                <div className="grid gap-2">
                    <Label>Status</Label>
                    <Select
                        value={data.status}
                        onValueChange={(value) => setData('status', value)}
                    >
                        <SelectTrigger className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="published">Published</SelectItem>
                            <SelectItem value="archived">Archived</SelectItem>
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
