<?php

namespace App\Http\Controllers;

use App\Enums\AiLogStatus;
use App\Enums\CourseStatus;
use App\Enums\QuizAttemptStatus;
use App\Models\AiLog;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', match (true) {
            $user->isAdmin() => ['role' => 'admin', 'stats' => $this->adminStats()],
            $user->isInstructor() => ['role' => 'instructor', 'stats' => $this->instructorStats($user)],
            default => ['role' => 'student', 'stats' => $this->studentStats($user)],
        });
    }

    /**
     * @return array<string, int>
     */
    private function instructorStats(User $user): array
    {
        $courseIds = $user->courses()->pluck('id');
        $quizIds = Quiz::query()->belongingToCourses($courseIds)->pluck('id');

        return [
            'total_courses' => $courseIds->count(),
            'published_courses' => $user->courses()->where('status', CourseStatus::Published)->count(),
            'students' => CourseEnrollment::query()->whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id'),
            'quiz_attempts' => QuizAttempt::query()->whereIn('quiz_id', $quizIds)->count(),
            'ai_requests' => AiLog::query()->where('user_id', $user->id)->count(),
        ];
    }

    /**
     * @return array<string, int|float|Course|null>
     */
    private function studentStats(User $user): array
    {
        return [
            'enrolled_courses' => $user->enrollments()->count(),
            'completed_lessons' => LessonProgress::query()
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->count(),
            'quiz_average' => round((float) QuizAttempt::query()
                ->where('user_id', $user->id)
                ->where('status', QuizAttemptStatus::Graded)
                ->avg('percentage'), 1) ?: null,
            'continue_course' => Course::query()
                ->join('course_enrollments', 'course_enrollments.course_id', '=', 'courses.id')
                ->where('course_enrollments.user_id', $user->id)
                ->with('instructor:id,name')
                ->latest('course_enrollments.enrolled_at')
                ->select('courses.*')
                ->first(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function adminStats(): array
    {
        return [
            'today_ai_requests' => AiLog::query()->whereDate('created_at', today())->count(),
            'tutor_requests' => AiLog::query()->where('feature', 'tutor')->count(),
            'quiz_generations' => AiLog::query()->where('feature', 'quiz_generator')->count(),
            'essay_gradings' => AiLog::query()->where('feature', 'essay_grading')->count(),
            'failed_requests' => AiLog::query()->where('status', AiLogStatus::Failed)->count(),
            'total_tokens' => (int) AiLog::query()->sum('total_tokens'),
        ];
    }
}
