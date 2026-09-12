import { Head, Link } from '@inertiajs/react';
import { show as showStudentCourse } from '@/actions/App/Http/Controllers/Student/CourseController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { Course } from '@/types';

function StatCard({ label, value }: { label: string; value: string | number }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-muted-foreground text-sm font-medium">
                    {label}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p className="text-3xl font-semibold">{value}</p>
            </CardContent>
        </Card>
    );
}

type InstructorStats = {
    total_courses: number;
    published_courses: number;
    students: number;
    quiz_attempts: number;
    ai_requests: number;
};

type StudentStats = {
    enrolled_courses: number;
    completed_lessons: number;
    quiz_average: number | null;
    continue_course: Course | null;
};

type AdminStats = {
    today_ai_requests: number;
    tutor_requests: number;
    quiz_generations: number;
    essay_gradings: number;
    failed_requests: number;
    total_tokens: number;
};

type DashboardProps =
    | { role: 'instructor'; stats: InstructorStats }
    | { role: 'student'; stats: StudentStats }
    | { role: 'admin'; stats: AdminStats };

export default function Dashboard(props: DashboardProps) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="space-y-4 p-4">
                {props.role === 'instructor' && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <StatCard
                            label="Total Courses"
                            value={props.stats.total_courses}
                        />
                        <StatCard
                            label="Published Courses"
                            value={props.stats.published_courses}
                        />
                        <StatCard
                            label="Students"
                            value={props.stats.students}
                        />
                        <StatCard
                            label="Quiz Attempts"
                            value={props.stats.quiz_attempts}
                        />
                        <StatCard
                            label="AI Requests"
                            value={props.stats.ai_requests}
                        />
                    </div>
                )}

                {props.role === 'student' && (
                    <>
                        <div className="grid gap-4 sm:grid-cols-3">
                            <StatCard
                                label="Enrolled Courses"
                                value={props.stats.enrolled_courses}
                            />
                            <StatCard
                                label="Completed Lessons"
                                value={props.stats.completed_lessons}
                            />
                            <StatCard
                                label="Quiz Average"
                                value={
                                    props.stats.quiz_average !== null
                                        ? `${props.stats.quiz_average}%`
                                        : '—'
                                }
                            />
                        </div>

                        {props.stats.continue_course && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Continue Learning</CardTitle>
                                </CardHeader>
                                <CardContent className="flex items-center justify-between">
                                    <p className="font-medium">
                                        {props.stats.continue_course.title}
                                    </p>
                                    <Button asChild>
                                        <Link
                                            href={showStudentCourse(
                                                props.stats.continue_course.id,
                                            )}
                                        >
                                            Continue
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}

                {props.role === 'admin' && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <StatCard
                            label="Today's AI Requests"
                            value={props.stats.today_ai_requests}
                        />
                        <StatCard
                            label="Tutor Requests"
                            value={props.stats.tutor_requests}
                        />
                        <StatCard
                            label="Quiz Generations"
                            value={props.stats.quiz_generations}
                        />
                        <StatCard
                            label="Essay Gradings"
                            value={props.stats.essay_gradings}
                        />
                        <StatCard
                            label="Failed Requests"
                            value={props.stats.failed_requests}
                        />
                        <StatCard
                            label="Total Tokens Used"
                            value={props.stats.total_tokens.toLocaleString()}
                        />
                    </div>
                )}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
