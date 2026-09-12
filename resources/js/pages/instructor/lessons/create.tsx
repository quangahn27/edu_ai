import { Head } from '@inertiajs/react';
import {
    index as coursesIndex,
    show as showCourse,
} from '@/actions/App/Http/Controllers/CourseController';
import { store } from '@/actions/App/Http/Controllers/LessonController';
import Heading from '@/components/heading';
import LessonForm from '@/components/lesson/LessonForm';
import type { Section } from '@/types';

export default function CreateLesson({ section }: { section: Section }) {
    return (
        <>
            <Head title="Add lesson" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Add lesson"
                    description={`New lesson in "${section.title}"`}
                />

                <LessonForm
                    action={{ url: store(section.id).url, method: 'post' }}
                    submitLabel="Create lesson"
                />
            </div>
        </>
    );
}

CreateLesson.layout = (props: { section: Section }) => ({
    breadcrumbs: [
        { title: 'Courses', href: coursesIndex() },
        {
            title: props.section.title,
            href: showCourse(props.section.course_id),
        },
        { title: 'Add lesson', href: '#' },
    ],
});
