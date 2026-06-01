<?php

namespace App\Services\Ai;

/**
 * Provider-agnostic photo defect analyzer. Anthropic and Gemini adapters both
 * implement this so the controller can swap providers via config.
 */
interface PhotoAnalyzer
{
    /**
     * @return array{parsed: array, raw: array, latency_ms: int}
     *
     * @throws PhotoAnalysisException
     */
    public function analyze(string $base64Image, string $mediaType): array;
}
