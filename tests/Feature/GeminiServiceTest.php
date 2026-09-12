<?php

use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('it parses a successful Gemini response', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Hello from Gemini.']]]],
            ],
            'usageMetadata' => [
                'promptTokenCount' => 12,
                'candidatesTokenCount' => 8,
                'totalTokenCount' => 20,
            ],
        ]),
    ]);

    $service = new GeminiService(apiKey: 'test-key', model: 'gemini-test');
    $response = $service->generate('Hello?', ['system' => 'Be nice.']);

    expect($response->content)->toBe('Hello from Gemini.');
    expect($response->totalTokens)->toBe(20);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'key=test-key')
            && str_contains($request->url(), 'gemini-test')
            && $request['systemInstruction']['parts'][0]['text'] === 'Be nice.';
    });
});

test('it throws when the api key is missing', function () {
    $service = new GeminiService(apiKey: '', model: 'gemini-test');

    expect(fn () => $service->generate('Hello?'))
        ->toThrow(AIRequestException::class);
});

test('it maps a 401 response to an invalid key error', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response([], 401)]);

    $service = new GeminiService(apiKey: 'bad-key', model: 'gemini-test');

    expect(fn () => $service->generate('Hi'))
        ->toThrow(AIRequestException::class, 'invalid');
});

test('it maps a 429 response to a quota error', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response([], 429)]);

    $service = new GeminiService(apiKey: 'test-key', model: 'gemini-test');

    expect(fn () => $service->generate('Hi'))
        ->toThrow(AIRequestException::class, 'quota');
});

test('it maps a server error to a model unavailable error', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response([], 503)]);

    $service = new GeminiService(apiKey: 'test-key', model: 'gemini-test');

    expect(fn () => $service->generate('Hi'))
        ->toThrow(AIRequestException::class, 'unavailable');
});

test('it throws on an empty candidates response', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => []])]);

    $service = new GeminiService(apiKey: 'test-key', model: 'gemini-test');

    expect(fn () => $service->generate('Hi'))
        ->toThrow(AIRequestException::class, 'empty');
});

test('it throws on a connection failure', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::failedConnection()]);

    $service = new GeminiService(apiKey: 'test-key', model: 'gemini-test');

    expect(fn () => $service->generate('Hi'))
        ->toThrow(AIRequestException::class);
});
