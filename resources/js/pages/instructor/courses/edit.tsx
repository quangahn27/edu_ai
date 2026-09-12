import { Head } from '@inertiajs/react';
import {
    edit,
    index,
    update,
} from '@/actions/App/Http/Controllers/CourseController';
import CourseForm from '@/components/course/CourseForm';
import Heading from '@/components/heading';
import type { Course } from '@/types';

export default function EditCourse({ course }: { course: Course }) {
    return (
        <>
            <Head title={`Edit ${course.title}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit course"
                    description="Update this course's details."
                />

                <CourseForm
                    course={course}
                    action={{ url: update(course.id).url, method: 'put' }}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditCourse.layout = (props: { course: Course }) => ({
    breadcrumbs: [
        { title: 'Courses', href: index() },
        { title: props.course.title, href: edit(props.course.id) },
    ],
});
