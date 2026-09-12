export type CourseLevel = 'beginner' | 'intermediate' | 'advanced';
export type CourseStatus = 'draft' | 'published' | 'archived';
export type LessonStatus = 'draft' | 'published';

export type Instructor = {
    id: number;
    name: string;
};

export type Course = {
    id: number;
    instructor_id: number;
    title: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    thumbnail: string | null;
    level: CourseLevel;
    status: CourseStatus;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    instructor?: Instructor;
    sections?: Section[];
    quizzes?: Quiz[];
    sections_count?: number;
    enrollments_count?: number;
    [key: string]: unknown;
};

export type Section = {
    id: number;
    course_id: number;
    title: string;
    description: string | null;
    sort_order: number;
    lessons?: Lesson[];
    quizzes?: Quiz[];
    [key: string]: unknown;
};

export type Lesson = {
    id: number;
    section_id: number;
    title: string;
    slug: string;
    summary: string | null;
    content: string | null;
    video_url: string | null;
    duration: number | null;
    sort_order: number;
    is_preview: boolean;
    status: LessonStatus;
    section?: Section & { course?: Course };
    progress?: LessonProgress[];
    quizzes?: Quiz[];
    [key: string]: unknown;
};

export type LessonProgress = {
    id: number;
    lesson_id: number;
    user_id: number;
    completed_at: string | null;
    progress_percent: number;
    [key: string]: unknown;
};

export type CourseEnrollment = {
    id: number;
    course_id: number;
    user_id: number;
    enrolled_at: string;
    status: 'active' | 'completed' | 'cancelled';
    [key: string]: unknown;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

export type QuestionType =
    | 'multiple_choice'
    | 'true_false'
    | 'short_answer'
    | 'essay';

export type QuestionOption = {
    id: number;
    question_id: number;
    option_text: string;
    is_correct: boolean;
    sort_order: number;
    [key: string]: unknown;
};

export type Question = {
    id: number;
    quiz_id: number;
    type: QuestionType;
    question_text: string;
    explanation: string | null;
    reference_answer: string | null;
    points: number;
    sort_order: number;
    options?: QuestionOption[];
    [key: string]: unknown;
};

export type Quiz = {
    id: number;
    course_id: number | null;
    lesson_id: number | null;
    title: string;
    description: string | null;
    time_limit: number | null;
    passing_score: number;
    max_attempts: number | null;
    shuffle_questions: boolean;
    status: 'draft' | 'published';
    questions?: Question[];
    [key: string]: unknown;
};

export type EssayGradingCriterion = {
    name: string;
    score: number;
    max_score: number;
    comment: string;
};

export type EssayGrading = {
    id: number;
    quiz_answer_id: number;
    ai_suggested_score: number | null;
    teacher_score: number | null;
    ai_feedback: string | null;
    teacher_feedback: string | null;
    grading_details: {
        criteria?: EssayGradingCriterion[];
        strengths?: string[];
        improvements?: string[];
    } | null;
    graded_by: number | null;
    graded_at: string | null;
    [key: string]: unknown;
};

export type QuizAnswer = {
    id: number;
    quiz_attempt_id: number;
    question_id: number;
    selected_option_id: number | null;
    answer_text: string | null;
    is_correct: boolean | null;
    score: number | null;
    question?: Question;
    attempt?: QuizAttempt;
    essayGrading?: EssayGrading | null;
    [key: string]: unknown;
};

export type QuizAttempt = {
    id: number;
    quiz_id: number;
    user_id: number;
    started_at: string;
    submitted_at: string | null;
    score: number | null;
    percentage: number | null;
    status: 'in_progress' | 'submitted' | 'graded';
    quiz?: Quiz;
    answers?: QuizAnswer[];
    user?: Instructor;
    [key: string]: unknown;
};

/** A question shape stripped of answer-revealing fields, sent while a quiz is in progress. */
export type TakingQuestion = {
    id: number;
    type: QuestionType;
    question_text: string;
    points: number;
    options: { id: number; option_text: string }[];
};

/** A question proposed by the AI quiz generator, pending instructor review. */
export type GeneratedQuestion = {
    type: QuestionType;
    question_text: string;
    explanation: string | null;
    points: number;
    difficulty: string | null;
    options: { option_text: string; is_correct: boolean }[];
};
