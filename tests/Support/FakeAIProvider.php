<?php

namespace Tests\Support;

use App\Services\AI\AIProvider;
use App\Services\AI\AIResponse;
use App\Services\AI\Exceptions\AIRequestException;

class FakeAIProvider implements AIProvider
{
    /** @var list<array{prompt: string, options: array}> */
    public array $calls = [];

    private ?AIRequestException $failWith = null;

    public function __construct(private string $reply = 'This is a fake AI response.') {}

    public function failNextRequestWith(AIRequestException $exception): void
    {
        $this->failWith = $exception;
    }

    public function generate(string $prompt, array $options = []): AIResponse
    {
        $this->calls[] = ['prompt' => $prompt, 'options' => $options];

        if ($this->failWith) {
            $exception = $this->failWith;
            $this->failWith = null;

            throw $exception;
        }

        return new AIResponse(
            content: $this->reply,
            promptTokens: 10,
            outputTokens: 5,
            totalTokens: 15,
        );
    }
}
