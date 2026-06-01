<?php

namespace App\Services\Ai;

/**
 * Resolves the active AI provider (gemini | anthropic) from config, so the
 * controller and the ai_enabled session flag agree on a single source of truth.
 */
class AiConfig
{
    public static function provider(): string
    {
        return config('services.ai.provider', 'gemini');
    }

    public static function model(): string
    {
        return self::provider() === 'anthropic'
            ? config('services.anthropic.model')
            : config('services.gemini.model');
    }

    public static function apiKey(): ?string
    {
        return self::provider() === 'anthropic'
            ? config('services.anthropic.api_key')
            : config('services.gemini.api_key');
    }

    /** Photo analysis is usable only when enabled AND the active provider is keyed. */
    public static function enabled(): bool
    {
        return (bool) config('services.ai.photo_analysis_enabled')
            && ! empty(self::apiKey());
    }

    public static function makeAnalyzer(): PhotoAnalyzer
    {
        return self::provider() === 'anthropic'
            ? new PhotoAnalysisService(self::apiKey(), self::model())
            : new GeminiPhotoAnalysisService(self::apiKey(), self::model());
    }
}
