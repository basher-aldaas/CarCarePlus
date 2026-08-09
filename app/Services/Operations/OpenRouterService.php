<?php

namespace App\Services\Operations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService
{
    public function ask(string $prompt): string
    {
        $apiKey = trim((string) config('services.openrouter.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException(
                'OPENROUTER_API_KEY is not configured.'
            );
        }

        $model = trim((string) config('services.openrouter.model'));

        if ($model === '') {
            throw new RuntimeException(
                'OPENROUTER_MODEL is not configured.'
            );
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout(60)
            ->post(
                'https://openrouter.ai/api/v1/chat/completions',
                [
                    'model' => $model,

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'أنت خبير محترف في تشخيص أعطال السيارات. أجب بالعربية فقط. لا تخمن إذا كانت المعلومات ناقصة. أعد JSON فقط.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]
            );

        $response->throw();

        $content = $response->json(
            'choices.0.message.content'
        );

        if (!is_string($content)) {
            throw new RuntimeException(
                'OpenRouter returned an invalid response.'
            );
        }

        return trim(
            str_replace(
                ['```json', '```'],
                '',
                $content
            )
        );
    }
}
