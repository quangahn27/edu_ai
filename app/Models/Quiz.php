<?php

namespace App\Models;

use App\Enums\QuizStatus;
use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'time_limit',
        'passing_score',
        'max_attempts',
        'shuffle_questions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'status' => QuizStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Resolve the course this quiz belongs to, whether attached directly
     * or indirectly through a lesson.
     */
    public function resolveCourse(): ?Course
    {
        return $this->course ?? $this->lesson?->section?->course;
    }

    /**
     * Quizzes attached directly to one of the given courses, or indirectly
     * through one of their lessons.
     *
     * @param  Builder<Quiz>  $query
     * @param  Collection<int, int>|array<int, int>  $courseIds
     * @return Builder<Quiz>
     */
    #[Scope]
    protected function belongingToCourses(Builder $query, $courseIds): Builder
    {
        return $query->where(function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds)
                ->orWhereHas('lesson.section', fn ($section) => $section->whereIn('course_id', $courseIds));
        });
    }
}
