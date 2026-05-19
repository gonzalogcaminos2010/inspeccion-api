<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class PhotoAnalysisService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const SYSTEM_PROMPT = 'Sos un experto en inspecciones industriales. Analizas fotos de equipos (excavadoras, gruas, camionetas, perforadoras, etc.) para detectar defectos visibles (grietas, corrosion, fugas, desgaste, rupturas, conexiones defectuosas, etc.). Respondes SIEMPRE en JSON valido con la estructura especificada. Sos conservador: si no hay defecto claro, marcas has_defect=false.';

    private const USER_PROMPT = "Analizar esta foto de inspeccion industrial. Devolver SOLO un JSON con esta estructura: { has_defect: boolean, title: string|null (max 80 chars), description: string|null (max 500 chars), severity: 'LOW'|'MEDIUM'|'HIGH'|'CRITICAL'|null, defect_type: string|null (texto libre corto, ej 'fuga hidraulica'), observations: string (siempre presente, en espanol, 1-3 oraciones) }. Si has_defect=false, el resto de campos pueden ser null y observations describe el estado general del equipo.";

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model
    ) {}

    /**
     * @return array{parsed: array, raw: array, latency_ms: int}
     *
     * @throws PhotoAnalysisException
     */
    public function analyze(string $base64Image, string $mediaType): array
    {
        $start = microtime(true);
        $lastError = null;
        $rawResponse = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(60)->post(self::ENDPOINT, [
                'model' => $this->model,
                'max_tokens' => 1024,
                'system' => self::SYSTEM_PROMPT,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mediaType,
                                    'data' => $base64Image,
                                ],
                            ],
                            ['type' => 'text', 'text' => self::USER_PROMPT],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                throw new PhotoAnalysisException(
                    'Error en la API de Anthropic: HTTP '.$response->status(),
                    $response->json()
                );
            }

            $rawResponse = $response->json();
            $parsed = $this->extractJson($rawResponse);

            if ($parsed !== null && $this->isValidShape($parsed)) {
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
            $rawResponse
        );
    }

    private function extractJson(array $response): ?array
    {
        $text = $response['content'][0]['text'] ?? null;
        if (! is_string($text)) {
            return null;
        }

        // Try direct decode first
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Try extracting JSON from a code block or surrounding text
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function isValidShape(array $parsed): bool
    {
        return array_key_exists('has_defect', $parsed)
            && is_bool($parsed['has_defect'])
            && array_key_exists('observations', $parsed)
            && is_string($parsed['observations']);
    }
}
