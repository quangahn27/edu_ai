import { Head, Link, router } from '@inertiajs/react';
import {
    create,
    destroy,
    edit,
    index,
    show,
} from '@/actions/App/Http/Controllers/CourseController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Course, Paginated } from '@/types';

const statusVariant: Record<
    Course['status'],
    'default' | 'secondary' | 'outline'
> = {
    draft: 'outline',
    published: 'default',
    archived: 'secondary',
};

export default function CoursesIndex({
    courses,
}: {
    courses: Paginated<Course>;
}) {
    const handleDelete = (course: Course) => {
        if (!confirm(`Delete "${course.title}"? This cannot be undone.`)) {
            return;
        }

        router.delete(destroy(course.id).url);
    };

    return (
        <>
            <Head title="Courses" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Courses"
                        description="Manage your courses, curriculum, and publishing status."
                    />
                    <Button asChild>
                        <Link href={create()}>New course</Link>
                    </Button>
                </div>

                {courses.data.length === 0 ? (
                    <p className="text-muted-foreground text-sm">
                        No courses yet. Create your first course to get started.
                    </p>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3 font-medium">Title</th>
                                    <th className="p-3 font-medium">
                                        Instructor
                                    </th>
                                    <th className="p-3 font-medium">Level</th>
                                    <th className="p-3 font-medium">Status</th>
                                    <th className="p-3 font-medium">
                                        Sections
                                    </th>
                                    <th className="p-3 font-medium">
                                        Students
                                    </th>
                                    <th className="p-3 font-medium" />
                                </tr>
                            </thead>
                            <tbody>
                                {courses.data.map((course) => (
                                    <tr key={course.id} className="border-t">
                                        <td className="p-3">
                                            <Link
                                                href={show(course.id)}
                                                className="font-medium hover:underline"
                                            >
                                                {course.title}
                                            </Link>
                                        </td>
                                        <td className="text-muted-foreground p-3">
                                            {course.instructor?.name}
                                        </td>
                                        <td className="p-3 capitalize">
                                            {course.level}
                                        </td>
                                        <td className="p-3">
                                            <Badge
                                                variant={
                                                    statusVariant[course.status]
                                                }
                                                className="capitalize"
                                            >
                                                {course.status}
                                            </Badge>
                                        </td>
                                        <td className="p-3">
                                            {course.sections_count ?? 0}
                                        </td>
                                        <td className="p-3">
                                            {course.enrollments_count ?? 0}
                                        </td>
                                        <td className="space-x-2 p-3 text-right whitespace-nowrap">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link href={edit(course.id)}>
                                                    Edit
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() =>
                                                    handleDelete(course)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

CoursesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Courses', href: index() },
    ],
};
