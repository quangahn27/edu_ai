import { Head, Link, router, useForm } from '@inertiajs/react';
import { type FormEventHandler, useState } from 'react';
import * as CourseActions from '@/actions/App/Http/Controllers/CourseController';
import * as LessonActions from '@/actions/App/Http/Controllers/LessonController';
import * as QuizActions from '@/actions/App/Http/Controllers/QuizController';
import * as SectionActions from '@/actions/App/Http/Controllers/SectionController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as coursesIndex } from '@/actions/App/Http/Controllers/CourseController';
import type { Course, Section } from '@/types';

function AddQuizForm({
    action,
    onSuccess,
}: {
    action: { url: string };
    onSuccess?: () => void;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        passing_score: '60',
        status: 'draft',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(action.url, {
            onSuccess: () => {
                reset();
                onSuccess?.();
            },
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-wrap items-end gap-3">
            <div className="grid min-w-48 flex-1 gap-1.5">
                <Label htmlFor="quiz-title">Quiz title</Label>
                <Input
                    id="quiz-title"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    placeholder="e.g. Module quiz"
                    required
                />
                {errors.title && (
                    <p className="text-destructive text-xs">{errors.title}</p>
                )}
            </div>
            <Button type="submit" size="sm" disabled={processing}>
                Add quiz
            </Button>
        </form>
    );
}

function AddSectionForm({ course }: { course: Course }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        description: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(SectionActions.store(course.id).url, {
            onSuccess: () => reset(),
        });
    };

    return (
        <form
            onSubmit={submit}
            className="flex flex-wrap items-end gap-3 rounded-lg border border-dashed p-4"
        >
            <div className="grid min-w-48 flex-1 gap-1.5">
                <Label htmlFor="section-title">New section title</Label>
                <Input
                    id="section-title"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    placeholder="e.g. Getting started"
                    required
                />
                {errors.title && (
                    <p className="text-destructive text-xs">{errors.title}</p>
                )}
            </div>
            <Button type="submit" disabled={processing}>
                Add section
            </Button>
        </form>
    );
}

function SectionCard({
    course,
    section,
    isFirst,
    isLast,
}: {
    course: Course;
    section: Section;
    isFirst: boolean;
    isLast: boolean;
}) {
    const [editing, setEditing] = useState(false);
    const { data, setData, patch, processing, errors } = useForm({
        title: section.title,
        description: section.description ?? '',
    });

    const saveSection: FormEventHandler = (e) => {
        e.preventDefault();
        patch(SectionActions.update(section.id).url, {
            onSuccess: () => setEditing(false),
        });
    };

    const move = (direction: 'up' | 'down') => {
        router.patch(SectionActions.reorder(course.id).url, {
            section_ids: reorderIds(
                course.sections ?? [],
                section.id,
                direction,
            ),
        });
    };

    const deleteSection = () => {
        if (
            !confirm(`Delete section "${section.title}" and all its lessons?`)
        ) {
            return;
        }
        router.delete(SectionActions.destroy(section.id).url);
    };

    return (
        <div className="rounded-lg border">
            <div className="flex items-start justify-between gap-4 border-b p-4">
                {editing ? (
                    <form onSubmit={saveSection} className="w-full space-y-3">
                        <Input
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            required
                        />
                        {errors.title && (
                            <p className="text-destructive text-xs">
                                {errors.title}
                            </p>
                        )}
                        <Textarea
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                            placeholder="Section description (optional)"
                            rows={2}
                        />
                        <div className="flex gap-2">
                            <Button
                                type="submit"
                                size="sm"
                                disabled={processing}
                            >
                                Save
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                onClick={() => setEditing(false)}
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                ) : (
                    <>
                        <div>
                            <h3 className="font-medium">{section.title}</h3>
                            {section.description && (
                                <p className="text-muted-foreground mt-1 text-sm">
                                    {section.description}
                                </p>
                            )}
                        </div>
                        <div className="flex shrink-0 items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                disabled={isFirst}
                                onClick={() => move('up')}
                            >
                                ↑
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                disabled={isLast}
                                onClick={() => move('down')}
                            >
                                ↓
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setEditing(true)}
                            >
                                Edit
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={deleteSection}
                            >
                                Delete
                            </Button>
                        </div>
                    </>
                )}
            </div>

            <div className="divide-y">
                {(section.lessons ?? []).map((lesson, index) => (
                    <div
                        key={lesson.id}
                        className="flex items-center justify-between gap-4 p-4"
                    >
                        <div className="flex items-center gap-3">
                            <div className="flex flex-col">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-4 px-1"
                                    disabled={index === 0}
                                    onClick={() =>
                                        router.patch(
                                            LessonActions.reorder(section.id)
                                                .url,
                                            {
                                                lesson_ids: reorderIds(
                                                    section.lessons ?? [],
                                                    lesson.id,
                                                    'up',
                                                ),
                                            },
                                        )
                                    }
                                >
                                    ↑
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-4 px-1"
                                    disabled={
                                        index ===
                                        (section.lessons?.length ?? 1) - 1
                                    }
                                    onClick={() =>
                                        router.patch(
                                            LessonActions.reorder(section.id)
                                                .url,
                                            {
                                                lesson_ids: reorderIds(
                                                    section.lessons ?? [],
                                                    lesson.id,
                                                    'down',
                                                ),
                                            },
                                        )
                                    }
                                >
                                    ↓
                                </Button>
                            </div>
                            <div>
                                <Link
                                    href={LessonActions.edit(lesson.id)}
                                    className="font-medium hover:underline"
                                >
                                    {lesson.title}
                                </Link>
                                <div className="mt-1 flex gap-1.5">
                                    <Badge
                                        variant={
                                            lesson.status === 'published'
                                                ? 'default'
                                                : 'outline'
                                        }
                                    >
                                        {lesson.status}
                                    </Badge>
                                    {lesson.is_preview && (
                                        <Badge variant="secondary">
                                            Preview
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            {(lesson.quizzes ?? []).length > 0 ? (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={QuizActions.show(
                                            lesson.quizzes![0].id,
                                        )}
                                    >
                                        Quiz
                                    </Link>
                                </Button>
                            ) : (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() =>
                                        router.post(
                                            QuizActions.storeForLesson(
                                                lesson.id,
                                            ).url,
                                            {
                                                title: `${lesson.title} Quiz`,
                                                passing_score: 60,
                                                status: 'draft',
                                            },
                                        )
                                    }
                                >
                                    Add quiz
                                </Button>
                            )}
                            <Button variant="outline" size="sm" asChild>
                                <Link href={LessonActions.edit(lesson.id)}>
                                    Edit
                                </Link>
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={() => {
                                    if (
                                        confirm(
                                            `Delete lesson "${lesson.title}"?`,
                                        )
                                    ) {
                                        router.delete(
                                            LessonActions.destroy(lesson.id)
                                                .url,
                                        );
                                    }
                                }}
                            >
                                Delete
                            </Button>
                        </div>
                    </div>
                ))}

                <div className="p-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={LessonActions.create(section.id)}>
                            Add lesson
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}

function reorderIds<T extends { id: number }>(
    items: T[],
    id: number,
    direction: 'up' | 'down',
): number[] {
    const ids = items.map((item) => item.id);
    const index = ids.indexOf(id);
    const swapWith = direction === 'up' ? index - 1 : index + 1;

    if (swapWith < 0 || swapWith >= ids.length) {
        return ids;
    }

    [ids[index], ids[swapWith]] = [ids[swapWith], ids[index]];

    return ids;
}

export default function ShowCourse({ course }: { course: Course }) {
    const sections = course.sections ?? [];

    return (
        <>
            <Head title={course.title} />

            <div className="space-y-6 p-4">
                <div className="flex items-start justify-between gap-4">
                    <Heading
                        title={course.title}
                        description={course.short_description ?? undefined}
                    />
                    <Button variant="outline" asChild>
                        <Link href={CourseActions.edit(course.id)}>
                            Edit course details
                        </Link>
                    </Button>
                </div>

                <div className="space-y-4">
                    {sections.map((section, index) => (
                        <SectionCard
                            key={section.id}
                            course={course}
                            section={section}
                            isFirst={index === 0}
                            isLast={index === sections.length - 1}
                        />
                    ))}
                </div>

                <AddSectionForm course={course} />

                <div className="space-y-3 rounded-lg border p-4">
                    <Heading
                        variant="small"
                        title="Course-level quizzes"
                        description="Quizzes that cover the whole course rather than a single lesson."
                    />
                    {(course.quizzes ?? []).length > 0 && (
                        <ul className="space-y-1">
                            {(course.quizzes ?? []).map((quiz) => (
                                <li key={quiz.id}>
                                    <Link
                                        href={QuizActions.show(quiz.id)}
                                        className="text-sm hover:underline"
                                    >
                                        {quiz.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                    <AddQuizForm
                        action={{
                            url: QuizActions.storeForCourse(course.id).url,
                        }}
                    />
                </div>
            </div>
        </>
    );
}

ShowCourse.layout = (props: { course: Course }) => ({
    breadcrumbs: [
        { title: 'Courses', href: coursesIndex() },
        {
            title: props.course.title,
            href: CourseActions.show(props.course.id),
        },
    ],
});
