import { Head } from '@inertiajs/react';
import { index as coursesIndex } from '@/actions/App/Http/Controllers/CourseController';
import { show, update } from '@/actions/App/Http/Controllers/QuizController';
import Heading from '@/components/heading';
import QuizForm from '@/components/quiz/QuizForm';
import type { Quiz } from '@/types';

export default function EditQuiz({ quiz }: { quiz: Quiz }) {
    return (
        <>
            <Head title={`Edit ${quiz.title}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit quiz"
                    description="Update this quiz's settings."
                />

                <QuizForm
                    quiz={quiz}
                    action={{ url: update(quiz.id).url, method: 'patch' }}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditQuiz.layout = (props: { quiz: Quiz }) => ({
    breadcrumbs: [
        { title: 'Courses', href: coursesIndex() },
        { title: props.quiz.title, href: show(props.quiz.id) },
        { title: 'Edit', href: '#' },
    ],
});
