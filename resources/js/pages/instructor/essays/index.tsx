import { Head, Link } from '@inertiajs/react';
import {
    index,
    show,
} from '@/actions/App/Http/Controllers/EssayGradingController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Paginated, QuizAnswer } from '@/types';

export default function EssaysIndex({
    answers,
}: {
    answers: Paginated<QuizAnswer>;
}) {
    return (
        <>
            <Head title="Essay grading" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Essay grading"
                    description="Essay answers waiting for a final grade."
                />

                {answers.data.length === 0 ? (
                    <p className="text-muted-foreground text-sm">
                        No essays are pending grading.
                    </p>
                ) : (
                    <div className="divide-y rounded-lg border">
                        {answers.data.map((answer) => (
                            <div
                                key={answer.id}
                                className="flex items-center justify-between gap-4 p-4"
                            >
                                <div>
                                    <p className="font-medium">
                                        {answer.attempt?.quiz?.title}
                                    </p>
                                    <p className="text-muted-foreground text-sm">
                                        Student: {answer.attempt?.user?.name}
                                    </p>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={show(answer.id)}>Grade</Link>
                                </Button>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

EssaysIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Essay grading', href: index() },
    ],
};
