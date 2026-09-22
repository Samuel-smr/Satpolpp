<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskAiAssistantRequest;
use App\Services\GeminiAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiAssistantController extends Controller
{
    public function __invoke(AskAiAssistantRequest $request, GeminiAssistant $assistant): JsonResponse
    {
        $validated = $request->validated();

        if (! config('services.gemini.enabled', true)) {
            return response()->json(['message' => 'Asisten AI sedang dinonaktifkan. Anda tetap dapat mengirim laporan melalui formulir.'], 503);
        }

        try {
            $history = $request->session()->get('ai_assistant.updated_at', 0) > now()->subMinutes(30)->timestamp
                ? $request->session()->get('ai_assistant.history', [])
                : [];
            $message = $assistant->redactSensitiveText($validated['message']);
            $answer = $assistant->ask(
                $message,
                array_slice($history, -8),
            );
            $request->session()->put('ai_assistant', [
                'history' => array_slice([
                    ...$history,
                    ['role' => 'user', 'text' => $message],
                    ['role' => 'model', 'text' => $answer],
                ], -8),
                'updated_at' => now()->timestamp,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Asisten AI SIPRAJA tidak tersedia.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Asisten AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            ], 503);
        }

        return response()->json(['answer' => $answer])->header('Cache-Control', 'private, no-store');
    }
}
