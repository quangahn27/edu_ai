<?php

namespace App\Services\AI;

/**
 * Abstraction over the underlying generative AI backend, so the application
 * is not hard-wired to Gemini and can be pointed at another provider later.
 */
interface AIProvider
{
    /**
     * @param  array{system?: string, history?: list<array{role: 'user'|'model', text: string}>, json?: bool}  $options
     *
     * @throws Exceptions\AIRequestException
     */
    public function generate(string $prompt, array $options = []): AIResponse;
}
