import { Head, Link } from '@inertiajs/react';
import {
    index,
    show,
} from '@/actions/App/Http/Controllers/Student/CourseController';
import Heading from '@/components/heading';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Course, Paginated } from '@/types';

export default function BrowseCourses({
    courses,
    enrolledCourseIds,
}: {
    courses: Paginated<Course>;
    enrolledCourseIds: number[];
}) {
    return (
        <>
            <Head title="Courses" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Courses"
                    description="Browse published courses and start learning."
                />

                {courses.data.length === 0 ? (
                    <p className="text-muted-foreground text-sm">
                        No courses are available yet.
                    </p>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.data.map((course) => {
                            const enrolled = enrolledCourseIds.includes(
                                course.id,
                            );

                            return (
                                <Card key={course.id}>
                                    <CardHeader>
                                        <CardTitle>{course.title}</CardTitle>
                                        <CardDescription>
                                            {course.instructor?.name} ·{' '}
                                            <span className="capitalize">
                                                {course.level}
                                            </span>
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-muted-foreground line-clamp-3 text-sm">
                                            {course.short_description}
                                        </p>
                                    </CardContent>
                                    <CardFooter>
                                        <Button asChild className="w-full">
                                            <Link href={show(course.id)}>
                                                {enrolled
                                                    ? 'Continue learning'
                                                    : 'View course'}
                                            </Link>
                                        </Button>
                                    </CardFooter>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </div>
        </>
    );
}

BrowseCourses.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Courses', href: index() },
    ],
};
