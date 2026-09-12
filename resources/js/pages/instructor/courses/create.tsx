import { Head } from '@inertiajs/react';
import CourseForm from '@/components/course/CourseForm';
import Heading from '@/components/heading';
import {
    create,
    index,
    store,
} from '@/actions/App/Http/Controllers/CourseController';

export default function CreateCourse() {
    return (
        <>
            <Head title="Create course" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Create course"
                    description="Set up a new course. You can add sections and lessons after saving."
                />

                <CourseForm
                    action={{ url: store().url, method: 'post' }}
                    submitLabel="Create course"
                />
            </div>
        </>
    );
}

CreateCourse.layout = {
    breadcrumbs: [
        { title: 'Courses', href: index() },
        { title: 'Create', href: create() },
    ],
};
