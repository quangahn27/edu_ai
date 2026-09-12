import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    destroy as destroyQuestion,
    reorder as reorderQuestions,
    store as storeQuestion,
    update as updateQuestion,
} from '@/actions/App/Http/Controllers/QuestionController';
import { edit as editQuiz } from '@/actions/App/Http/Controllers/QuizController';
import { show as showCourse } from '@/actions/App/Http/Controllers/CourseController';
import Heading from '@/components/heading';
import QuestionForm from '@/components/quiz/QuestionForm';
import QuizGeneratorModal from '@/components/quiz/QuizGeneratorModal';
import SafeHtml from '@/components/editor/SafeHtml';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as coursesIndex } from '@/actions/App/Http/Controllers/CourseController';
import type { Course, Question, Quiz } from '@/types';

const typeLabel: Record<Question['type'], string> = {
    multiple_choice: 'Multiple choice',
    true_false: 'True / False',
    short_answer: 'Short answer',
    essay: 'Essay',
};

function QuestionRow({
    quiz,
    question,
    isFirst,
    isLast,
}: {
    quiz: Quiz;
    question: Question;
    isFirst: boolean;
    isLast: boolean;
}) {
    const [editing, setEditing] = useState(false);

    const move = (direction: 'up' | 'down') => {
        const ids = (quiz.questions ?? []).map((q) => q.id);
        const index = ids.indexOf(question.id);
        const swapWith = direction === 'up' ? index - 1 : index + 1;
        if (swapWith < 0 || swapWith >= ids.length) return;
        [ids[index], ids[swapWith]] = [ids[swapWith], ids[index]];
        router.patch(reorderQuestions(quiz.id).url, { question_ids: ids });
    };

    if (editing) {
        return (
            <div className="rounded-lg border p-4">
                <QuestionForm
                    question={question}
                    action={{
                        url: updateQuestion(question.id).url,
                        method: 'patch',
                    }}
                    submitLabel="Save question"
                    onSuccess={() => setEditing(false)}
                />
                <Button
                    variant="outline"
                    size="sm"
                    className="mt-3"
                    onClick={() => setEditing(false)}
                >
                    Cancel
                </Button>
            </div>
        );
    }

    return (
        <div className="rounded-lg border p-4">
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0 flex-1">
                    <div className="mb-1 flex items-center gap-2">
                        <Badge variant="secondary">
                            {typeLabel[question.type]}
                        </Badge>
                        <span className="text-muted-foreground text-xs">
                            {question.points} pt
                            {question.points === 1 ? '' : 's'}
                        </span>
                    </div>
                    <SafeHtml html={question.question_text} />
                </div>
                <div className="flex shrink-0 items-center gap-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        disabled={isFirst}
                        onClick={() => move('up')}
                    >
                        ↑
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        disabled={isLast}
                        onClick={() => move('down')}
                    >
                        ↓
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setEditing(true)}
                    >
                        Edit
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        onClick={() => {
                            if (confirm('Delete this question?')) {
                                router.delete(destroyQuestion(question.id).url);
                            }
                        }}
                    >
                        Delete
                    </Button>
                </div>
            </div>

            {question.options && question.options.length > 0 && (
                <ul className="mt-3 space-y-1 text-sm">
                    {question.options.map((option) => (
                        <li key={option.id} className="flex items-center gap-2">
                            <span
                                className={
                                    option.is_correct
                                        ? 'font-medium text-green-600'
                                        : 'text-muted-foreground'
                                }
                            >
                                {option.is_correct ? '✓' : '·'}{' '}
                                {option.option_text}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default function ShowQuiz({
    quiz,
}: {
    quiz: Quiz;
    course: Course | null;
}) {
    const [addingQuestion, setAddingQuestion] = useState(false);
    const [generatorOpen, setGeneratorOpen] = useState(false);
    const questions = quiz.questions ?? [];

    return (
        <>
            <Head title={quiz.title} />

            <div className="space-y-6 p-4">
                <div className="flex items-start justify-between gap-4">
                    <Heading
                        title={quiz.title}
                        description={
                            quiz.description
                                ? undefined
                                : 'Build your quiz by adding questions below.'
                        }
                    />
                    <div className="flex gap-2">
                        {quiz.lesson_id && (
                            <Button
                                variant="outline"
                                onClick={() => setGeneratorOpen(true)}
                            >
                                Generate Questions with AI
                            </Button>
                        )}
                        <Button variant="outline" asChild>
                            <Link href={editQuiz(quiz.id)}>
                                Edit quiz settings
                            </Link>
                        </Button>
                    </div>
                </div>

                {quiz.lesson_id && (
                    <QuizGeneratorModal
                        lessonId={quiz.lesson_id}
                        quizId={quiz.id}
                        open={generatorOpen}
                        onOpenChange={setGeneratorOpen}
                    />
                )}

                <div className="space-y-4">
                    {questions.map((question, index) => (
                        <QuestionRow
                            key={question.id}
                            quiz={quiz}
                            question={question}
                            isFirst={index === 0}
                            isLast={index === questions.length - 1}
                        />
                    ))}
                </div>

                {addingQuestion ? (
                    <div className="rounded-lg border border-dashed p-4">
                        <QuestionForm
                            action={{
                                url: storeQuestion(quiz.id).url,
                                method: 'post',
                            }}
                            submitLabel="Add question"
                            onSuccess={() => setAddingQuestion(false)}
                        />
                        <Button
                            variant="outline"
                            size="sm"
                            className="mt-3"
                            onClick={() => setAddingQuestion(false)}
                        >
                            Cancel
                        </Button>
                    </div>
                ) : (
                    <Button onClick={() => setAddingQuestion(true)}>
                        Add question
                    </Button>
                )}
            </div>
        </>
    );
}

ShowQuiz.layout = (props: { quiz: Quiz; course: Course | null }) => ({
    breadcrumbs: [
        { title: 'Courses', href: coursesIndex() },
        ...(props.course
            ? [{ title: props.course.title, href: showCourse(props.course.id) }]
            : []),
        { title: props.quiz.title, href: '#' },
    ],
});
