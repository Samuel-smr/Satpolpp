<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    public function test_guest_can_ask_the_ai_assistant(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Tuliskan kejadian, lokasi, waktu, dan dampaknya.']],
                    ],
                ]],
            ]),
        ]);

        $response = $this->withSession([
            'ai_assistant' => [
                'history' => [
                    ['role' => 'user', 'text' => 'Saya ingin melapor.'],
                    ['role' => 'model', 'text' => 'Saya siap membantu.'],
                ],
                'updated_at' => now()->timestamp,
            ],
        ])->postJson(route('pelapor.ai-assistant'), [
            'message' => 'Bagaimana membuat deskripsi laporan?',
        ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'answer' => 'Tuliskan kejadian, lokasi, waktu, dan dampaknya.',
            ]);

        Http::assertSent(function (Request $request): bool {
            $requestData = $request->data();

            return $request->hasHeader('x-goog-api-key', 'test-key')
                && $requestData['contents'][2]['parts'][0]['text'] === 'Bagaimana membuat deskripsi laporan?'
                && $requestData['generationConfig']['maxOutputTokens'] === 1024
                && $requestData['generationConfig']['thinkingConfig']['thinkingLevel'] === 'MINIMAL'
                && ! array_key_exists('temperature', $requestData['generationConfig'])
                && str_contains($requestData['system_instruction']['parts'][0]['text'], 'satu percakapan yang berkelanjutan')
                && str_contains($requestData['system_instruction']['parts'][0]['text'], 'Jangan meminta NIK');
        });
    }

    public function test_guest_question_is_validated_before_calling_gemini(): void
    {
        Http::preventStrayRequests();

        $this->postJson(route('pelapor.ai-assistant'), [
            'message' => str_repeat('a', 601),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('message');
    }

    public function test_guest_receives_a_safe_error_when_gemini_is_unavailable(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent' => Http::response([], 429),
        ]);

        $this->postJson(route('pelapor.ai-assistant'), [
            'message' => 'Tolong bantu saya.',
        ])->assertServiceUnavailable()
            ->assertExactJson([
                'message' => 'Asisten AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            ]);

        Http::assertSentCount(2);
    }

    public function test_temporary_gemini_failure_is_retried(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent' => Http::sequence()
                ->push([
                    'error' => ['message' => 'The model is experiencing high demand.'],
                ], 503)
                ->push([
                    'candidates' => [[
                        'content' => [
                            'parts' => [['text' => 'Tentu, saya dapat membantu.']],
                        ],
                    ]],
                ]),
        ]);

        $this->postJson(route('pelapor.ai-assistant'), [
            'message' => 'Bisa bantu saya?',
        ])->assertOk()
            ->assertExactJson([
                'answer' => 'Tentu, saya dapat membantu.',
            ]);

        Http::assertSentCount(2);
    }
}
