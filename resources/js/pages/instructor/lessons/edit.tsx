import { Head } from '@inertiajs/react';
import {
    index as coursesIndex,
    show as showCourse,
} from '@/actions/App/Http/Controllers/CourseController';
import { update } from '@/actions/App/Http/Controllers/LessonController';
import Heading from '@/components/heading';
import LessonForm from '@/components/lesson/LessonForm';
import type { Course, Lesson, Section } from '@/types';

type LessonWithSection = Lesson & { section: Section & { course: Course } };

export default function EditLesson({ lesson }: { lesson: LessonWithSection }) {
    return (
        <>
            <Head title={`Edit ${lesson.title}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit lesson"
                    description={`In "${lesson.section.title}"`}
                />

                <LessonForm
                    lesson={lesson}
                    action={{ url: update(lesson.id).url, method: 'patch' }}
                    submitLabel="Save changes"
                    showPreviewTab
                />
            </div>
        </>
    );
}

EditLesson.layout = (props: { lesson: LessonWithSection }) => ({
    breadcrumbs: [
        { title: 'Courses', href: coursesIndex() },
        {
            title: props.lesson.section.course.title,
            href: showCourse(props.lesson.section.course_id),
        },
        { title: props.lesson.title, href: '#' },
    ],
});
