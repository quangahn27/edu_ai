<?php

namespace App\Services\AI;

use App\Services\AI\Exceptions\AIRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiService implements AIProvider
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $model = null,
    ) {}

    public function generate(string $prompt, array $options = []): AIResponse
    {
        $apiKey = $this->apiKey ?? config('services.gemini.key');
        $model = $this->model ?? config('services.gemini.model');

        if (empty($apiKey)) {
            throw new AIRequestException('Gemini API key is not configured.');
        }

        $contents = [];

        foreach ($options['history'] ?? [] as $turn) {
            $contents[] = [
                'role' => $turn['role'],
                'parts' => [['text' => $turn['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        if (! empty($options['json'])) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        if (! empty($options['system'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $options['system']]],
            ];
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->withQueryParameters(['key' => $apiKey])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    $payload,
                );
        } catch (ConnectionException $e) {
            throw new AIRequestException('Unable to reach the Gemini API.', previous: $e);
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new AIRequestException('The Gemini API key is invalid.');
        }

        if ($response->status() === 429) {
            throw new AIRequestException('The Gemini API quota has been exceeded.');
        }

        if ($response->serverError()) {
            throw new AIRequestException('The Gemini model is currently unavailable.');
        }

        if ($response->failed()) {
            throw new AIRequestException('The Gemini API request failed: '.$response->body());
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new AIRequestException('The Gemini API returned an invalid response.');
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($text)) {
            throw new AIRequestException('The Gemini API returned an empty response.');
        }

        return new AIResponse(
            content: $text,
            promptTokens: $data['usageMetadata']['promptTokenCount'] ?? null,
            outputTokens: $data['usageMetadata']['candidatesTokenCount'] ?? null,
            totalTokens: $data['usageMetadata']['totalTokenCount'] ?? null,
        );
    }
}
