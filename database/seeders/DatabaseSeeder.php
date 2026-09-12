<?php

namespace Database\Seeders;

use App\Enums\LessonStatus;
use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Enums\QuizStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with demo accounts and course content.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@edu-ai.test',
            'role' => UserRole::Admin,
        ]);

        $instructor = User::factory()->create([
            'name' => 'Jane Instructor',
            'email' => 'instructor@edu-ai.test',
            'role' => UserRole::Instructor,
        ]);

        $student = User::factory()->create([
            'name' => 'Sam Student',
            'email' => 'student@edu-ai.test',
            'role' => UserRole::Student,
        ]);

        $this->command->info('Demo accounts (password: "password"):');
        $this->command->line("  Admin:      {$admin->email}");
        $this->command->line("  Instructor: {$instructor->email}");
        $this->command->line("  Student:    {$student->email}");

        collect(['Introduction to Laravel', 'Modern JavaScript Fundamentals'])
            ->each(function (string $title) use ($instructor, $student) {
                $course = Course::factory()
                    ->published()
                    ->for($instructor, 'instructor')
                    ->create(['title' => $title]);

                $course->enrollments()->create(['user_id' => $student->id]);

                $firstLesson = null;

                collect(range(1, 2))->each(function (int $sectionIndex) use ($course, &$firstLesson) {
                    $section = Section::factory()->for($course)->create([
                        'title' => "Section {$sectionIndex}",
                        'sort_order' => $sectionIndex - 1,
                    ]);

                    $lessonCount = random_int(2, 3);

                    collect(range(1, $lessonCount))->each(function (int $lessonIndex) use ($section, $sectionIndex, &$firstLesson) {
                        $lesson = Lesson::factory()->for($section)->create([
                            'title' => "Lesson {$sectionIndex}.{$lessonIndex}",
                            'sort_order' => $lessonIndex - 1,
                            'status' => LessonStatus::Published,
                            'is_preview' => $sectionIndex === 1 && $lessonIndex === 1,
                        ]);

                        $firstLesson ??= $lesson;
                    });
                });

                if ($firstLesson) {
                    $firstLesson->progress()->create([
                        'user_id' => $student->id,
                        'completed_at' => now(),
                        'progress_percent' => 100,
                    ]);
                }

                $quiz = Quiz::factory()->for($course)->create([
                    'title' => "{$title} Quiz",
                    'status' => QuizStatus::Published,
                ]);

                $mcq = Question::factory()->for($quiz)->create([
                    'type' => QuestionType::MultipleChoice,
                    'question_text' => 'Which of the following best describes this course topic?',
                    'points' => 2,
                    'sort_order' => 0,
                ]);
                $correct = $mcq->options()->create(['option_text' => 'A correct, well-reasoned answer', 'is_correct' => true, 'sort_order' => 0]);
                $mcq->options()->create(['option_text' => 'An unrelated distractor', 'is_correct' => false, 'sort_order' => 1]);

                $trueFalse = Question::factory()->for($quiz)->create([
                    'type' => QuestionType::TrueFalse,
                    'question_text' => 'This course covers beginner-friendly material.',
                    'points' => 1,
                    'sort_order' => 1,
                ]);
                $trueFalse->options()->create(['option_text' => 'True', 'is_correct' => true, 'sort_order' => 0]);
                $trueFalse->options()->create(['option_text' => 'False', 'is_correct' => false, 'sort_order' => 1]);

                Question::factory()->for($quiz)->essay()->create([
                    'question_text' => 'Explain, in your own words, one key concept from this course.',
                    'points' => 5,
                    'sort_order' => 2,
                ]);

                $attempt = $quiz->attempts()->create([
                    'user_id' => $student->id,
                    'started_at' => now()->subMinutes(10),
                    'submitted_at' => now(),
                    'status' => QuizAttemptStatus::Submitted,
                ]);

                $attempt->answers()->create([
                    'question_id' => $mcq->id,
                    'selected_option_id' => $correct->id,
                    'is_correct' => true,
                    'score' => $mcq->points,
                ]);
            });
    }
}
