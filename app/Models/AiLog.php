<?php

namespace App\Models;

use App\Enums\AiFeature;
use App\Enums\AiLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    protected $fillable = [
        'user_id',
        'feature',
        'model',
        'status',
        'prompt_tokens',
        'output_tokens',
        'total_tokens',
        'duration_ms',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'feature' => AiFeature::class,
            'status' => AiLogStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
