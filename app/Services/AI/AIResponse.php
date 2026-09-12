<?php

namespace App\Services\AI;

final readonly class AIResponse
{
    public function __construct(
        public string $content,
        public ?int $promptTokens = null,
        public ?int $outputTokens = null,
        public ?int $totalTokens = null,
    ) {}
}
