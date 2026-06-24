<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected string $model = 'gemini-flash-latest';

    protected array $languageNames = [
        'en' => 'English',
        'es' => 'Spanish',
        'id' => 'Indonesian',
        'tr' => 'Turkish',
        'sv' => 'Swedish',
    ];

    public function translate(string $text, string $targetLang): string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::error('TranslationService: GEMINI_API_KEY is not configured');
            return $text;
        }

        $langName = $this->languageNames[$targetLang] ?? $targetLang;

        $response = Http::timeout(30)->withHeaders([
            'X-goog-api-key' => $apiKey,
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Translate the following text from Arabic to {$langName}.\nReturn ONLY the translated text, nothing else.\n\nText: " . $text,
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            Log::error('TranslationService: Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'target' => $targetLang,
            ]);

            throw new \RuntimeException('Gemini API error: ' . $response->status());
        }

        $translated = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $translated ?: $text;
    }

    public function translateBatch(array $fields, string $targetLang): array
    {
        $text = implode("\n---SEPARATOR---\n", $fields);

        $translated = $this->translate($text, $targetLang);

        $parts = explode("\n---SEPARATOR---\n", $translated);

        $result = [];
        foreach ($fields as $i => $original) {
            $result[$i] = $parts[$i] ?? $original;
        }

        return $result;
    }
}
