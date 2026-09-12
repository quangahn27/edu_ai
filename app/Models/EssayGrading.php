<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssayGrading extends Model
{
    protected $fillable = [
        'quiz_answer_id',
        'ai_suggested_score',
        'teacher_score',
        'ai_feedback',
        'teacher_feedback',
        'grading_details',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_suggested_score' => 'decimal:2',
            'teacher_score' => 'decimal:2',
            'grading_details' => 'array',
            'graded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<QuizAnswer, $this>
     */
    public function quizAnswer(): BelongsTo
    {
        return $this->belongsTo(QuizAnswer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
