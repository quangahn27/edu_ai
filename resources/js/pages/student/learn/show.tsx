import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, ChevronLeft, ChevronRight, Menu } from 'lucide-react';
import { useState } from 'react';
import {
    complete,
    show,
} from '@/actions/App/Http/Controllers/Student/LearnController';
import { index as coursesIndex } from '@/actions/App/Http/Controllers/Student/CourseController';
import AiTutor from '@/components/ai/AiTutor';
import SafeHtml from '@/components/editor/SafeHtml';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Course, Lesson, LessonProgress } from '@/types';

function CurriculumSidebar({
    course,
    activeLessonId,
}: {
    course: Course;
    activeLessonId: number;
}) {
    return (
        <nav className="space-y-4">
            <h2 className="px-2 text-sm font-semibold">{course.title}</h2>
            {(course.sections ?? []).map((section) => (
                <div key={section.id}>
                    <p className="text-muted-foreground px-2 text-xs font-medium tracking-wide uppercase">
                        {section.title}
                    </p>
                    <ul className="mt-1 space-y-0.5">
                        {(section.lessons ?? []).map((lesson) => {
                            const isActive = lesson.id === activeLessonId;
                            const isCompleted =
                                (lesson.progress ?? [])[0]?.completed_at !=
                                null;

                            return (
                                <li key={lesson.id}>
                                    <Link
                                        href={show(lesson.id)}
                                        className={cn(
                                            'flex items-center gap-2 rounded-md px-2 py-1.5 text-sm',
                                            isActive
                                                ? 'bg-accent text-accent-foreground font-medium'
                                                : 'hover:bg-accent/50',
                                        )}
                                    >
                                        {isCompleted ? (
                                            <CheckCircle2 className="size-3.5 shrink-0 text-green-600" />
                                        ) : (
                                            <span className="size-3.5 shrink-0" />
                                        )}
                                        <span className="truncate">
                                            {lesson.title}
                                        </span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            ))}
        </nav>
    );
}

export default function LearnShow({
    course,
    lesson,
    progress,
    previousLesson,
    nextLesson,
}: {
    course: Course;
    lesson: Lesson;
    progress: LessonProgress | null;
    previousLesson: Lesson | null;
    nextLesson: Lesson | null;
}) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const isCompleted = progress?.completed_at != null;

    const markComplete = () => {
        router.post(complete(lesson.id).url, {}, { preserveScroll: true });
    };

    return (
        <>
            <Head title={lesson.title} />

            <div className="flex min-h-[calc(100vh-4rem)] flex-col lg:flex-row">
                <aside className="hidden w-72 shrink-0 border-r p-4 lg:block">
                    <CurriculumSidebar
                        course={course}
                        activeLessonId={lesson.id}
                    />
                </aside>

                <div className="border-b p-2 lg:hidden">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setSidebarOpen((open) => !open)}
                    >
                        <Menu className="size-4" />
                        Curriculum
                    </Button>
                    {sidebarOpen && (
                        <div className="mt-2 rounded-md border p-3">
                            <CurriculumSidebar
                                course={course}
                                activeLessonId={lesson.id}
                            />
                        </div>
                    )}
                </div>

                <main className="mx-auto w-full max-w-3xl flex-1 space-y-6 p-4 md:p-8">
                    <div className="flex items-center gap-2">
                        <Link
                            href={coursesIndex()}
                            className="text-muted-foreground text-sm hover:underline"
                        >
                            Courses
                        </Link>
                        {isCompleted && (
                            <Badge variant="secondary" className="ml-auto">
                                Completed
                            </Badge>
                        )}
                    </div>

                    <h1 className="text-2xl font-semibold">{lesson.title}</h1>

                    {lesson.video_url && (
                        <Button variant="outline" asChild>
                            <a
                                href={lesson.video_url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Watch video
                            </a>
                        </Button>
                    )}

                    <SafeHtml html={lesson.content ?? ''} />

                    <div className="flex items-center justify-between border-t pt-6">
                        <Button
                            variant="ghost"
                            disabled={!previousLesson}
                            asChild={!!previousLesson}
                        >
                            {previousLesson ? (
                                <Link href={show(previousLesson.id)}>
                                    <ChevronLeft className="size-4" />
                                    Previous
                                </Link>
                            ) : (
                                <span>
                                    <ChevronLeft className="size-4" />
                                    Previous
                                </span>
                            )}
                        </Button>

                        <Button
                            onClick={markComplete}
                            disabled={isCompleted}
                            variant={isCompleted ? 'secondary' : 'default'}
                        >
                            {isCompleted ? 'Completed' : 'Mark as completed'}
                        </Button>

                        <Button
                            variant="ghost"
                            disabled={!nextLesson}
                            asChild={!!nextLesson}
                        >
                            {nextLesson ? (
                                <Link href={show(nextLesson.id)}>
                                    Next
                                    <ChevronRight className="size-4" />
                                </Link>
                            ) : (
                                <span>
                                    Next
                                    <ChevronRight className="size-4" />
                                </span>
                            )}
                        </Button>
                    </div>
                </main>

                <AiTutor lessonId={lesson.id} />
            </div>
        </>
    );
}
