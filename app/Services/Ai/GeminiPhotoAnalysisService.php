<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

/**
 * Gemini (Google AI Studio) adapter for photo defect analysis. Cheaper than
 * Anthropic vision — Gemini Flash has a generous free tier. Returns the same
 * parsed shape as the Anthropic adapter.
 */
class GeminiPhotoAnalysisService implements PhotoAnalyzer
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model
    ) {}

    public function analyze(string $base64Image, string $mediaType): array
    {
        $start = microtime(true);
        $lastError = null;
        $rawResponse = null;

        $url = self::ENDPOINT.$this->model.':generateContent';

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->timeout(60)->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => AiPrompts::SYSTEM]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'inline_data' => [
                                    'mime_type' => $mediaType,
                                    'data' => $base64Image,
                                ],
                            ],
                            ['text' => AiPrompts::USER],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 512,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                throw new PhotoAnalysisException(
                    'Error en la API de Gemini: HTTP '.$response->status(),
                    $response->json() ?? []
                );
            }

            $rawResponse = $response->json();
            $text = $rawResponse['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $parsed = AiPrompts::extractJsonFromText($text);

            if ($parsed !== null && AiPrompts::isValidShape($parsed)) {
                return [
                    'parsed' => $parsed,
                    'raw' => $rawResponse,
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                ];
            }

            $lastError = 'JSON malformado o estructura inválida';
        }

        throw new PhotoAnalysisException(
            'No se pudo obtener un análisis válido después de 2 intentos: '.$lastError,
            $rawResponse ?? []
        );
    }
}
