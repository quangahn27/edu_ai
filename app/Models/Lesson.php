<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Models\Concerns\HasSlug;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'summary',
        'content',
        'video_url',
        'duration',
        'sort_order',
        'is_preview',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'status' => LessonStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * @return HasMany<AiConversation, $this>
     */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }
}
