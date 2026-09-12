import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, PlayCircle } from 'lucide-react';
import { store as enroll } from '@/actions/App/Http/Controllers/Student/EnrollmentController';
import {
    index,
    show,
} from '@/actions/App/Http/Controllers/Student/CourseController';
import { show as learnShow } from '@/actions/App/Http/Controllers/Student/LearnController';
import { take as takeQuiz } from '@/actions/App/Http/Controllers/Student/QuizAttemptController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Course, CourseEnrollment, Quiz } from '@/types';

function QuizLink({ quiz, enabled }: { quiz: Quiz; enabled: boolean }) {
    if (quiz.status !== 'published') {
        return null;
    }

    return (
        <Button
            variant="outline"
            size="sm"
            disabled={!enabled}
            asChild={enabled}
        >
            {enabled ? (
                <Link href={takeQuiz(quiz.id)}>Take quiz: {quiz.title}</Link>
            ) : (
                <span>Enroll to unlock quiz</span>
            )}
        </Button>
    );
}

export default function ShowStudentCourse({
    course,
    enrollment,
}: {
    course: Course;
    enrollment: CourseEnrollment | null;
}) {
    const { post, processing } = useForm();
    const isEnrolled = enrollment !== null;

    return (
        <>
            <Head title={course.title} />

            <div className="space-y-6 p-4">
                <div className="flex items-start justify-between gap-4">
                    <Heading
                        title={course.title}
                        description={course.short_description ?? undefined}
                    />
                    {!isEnrolled && (
                        <Button
                            disabled={processing}
                            onClick={() => post(enroll(course.id).url)}
                        >
                            Enroll
                        </Button>
                    )}
                </div>

                <p className="text-muted-foreground text-sm">
                    Instructor: {course.instructor?.name}
                </p>

                <div className="space-y-4">
                    {(course.sections ?? []).map((section) => (
                        <div key={section.id} className="rounded-lg border">
                            <div className="border-b p-4">
                                <h3 className="font-medium">{section.title}</h3>
                                {section.description && (
                                    <p className="text-muted-foreground mt-1 text-sm">
                                        {section.description}
                                    </p>
                                )}
                            </div>
                            <div className="divide-y">
                                {(section.lessons ?? []).map((lesson) => {
                                    const accessible =
                                        isEnrolled || lesson.is_preview;

                                    return (
                                        <div
                                            key={lesson.id}
                                            className="flex items-center justify-between gap-4 p-4"
                                        >
                                            <div className="flex items-center gap-3">
                                                {accessible ? (
                                                    <PlayCircle className="text-muted-foreground size-4" />
                                                ) : (
                                                    <Lock className="text-muted-foreground size-4" />
                                                )}
                                                <span>{lesson.title}</span>
                                                {lesson.is_preview && (
                                                    <Badge variant="secondary">
                                                        Preview
                                                    </Badge>
                                                )}
                                            </div>
                                            <div className="flex gap-2">
                                                {(lesson.quizzes ?? []).map(
                                                    (quiz) => (
                                                        <QuizLink
                                                            key={quiz.id}
                                                            quiz={quiz}
                                                            enabled={accessible}
                                                        />
                                                    ),
                                                )}
                                                {accessible ? (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={learnShow(
                                                                lesson.id,
                                                            )}
                                                        >
                                                            Open
                                                        </Link>
                                                    </Button>
                                                ) : (
                                                    <span className="text-muted-foreground self-center text-xs">
                                                        Enroll to unlock
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </div>

                {(course.quizzes ?? []).length > 0 && (
                    <div className="space-y-2 rounded-lg border p-4">
                        <Heading variant="small" title="Course quizzes" />
                        <div className="flex flex-wrap gap-2">
                            {(course.quizzes ?? []).map((quiz) => (
                                <QuizLink
                                    key={quiz.id}
                                    quiz={quiz}
                                    enabled={isEnrolled}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

ShowStudentCourse.layout = (props: { course: Course }) => ({
    breadcrumbs: [
        { title: 'Courses', href: index() },
        { title: props.course.title, href: show(props.course.id) },
    ],
});
