<?php

namespace App\Services\Ai;

/**
 * Shared prompts for photo defect analysis, used by every provider adapter so
 * the instruction text stays identical across Anthropic / Gemini.
 */
class AiPrompts
{
    public const SYSTEM = 'Sos un experto en inspecciones industriales. Analizas fotos de equipos (excavadoras, gruas, camionetas, perforadoras, etc.) para detectar defectos visibles (grietas, corrosion, fugas, desgaste, rupturas, conexiones defectuosas, etc.). Respondes SIEMPRE en JSON valido con la estructura especificada. Sos conservador: si no hay defecto claro, marcas has_defect=false.';

    public const USER = "Analizar esta foto de inspeccion industrial. Devolver SOLO un JSON con esta estructura: { has_defect: boolean, title: string|null (max 80 chars), description: string|null (max 500 chars), severity: 'LOW'|'MEDIUM'|'HIGH'|'CRITICAL'|null, defect_type: string|null (texto libre corto, ej 'fuga hidraulica'), observations: string (siempre presente, en espanol, 1-3 oraciones) }. Si has_defect=false, el resto de campos pueden ser null y observations describe el estado general del equipo.";

    /**
     * Validate the parsed shape (provider-agnostic).
     */
    public static function isValidShape(array $parsed): bool
    {
        return array_key_exists('has_defect', $parsed)
            && is_bool($parsed['has_defect'])
            && array_key_exists('observations', $parsed)
            && is_string($parsed['observations']);
    }

    /**
     * Decode JSON from a model's text output, tolerating surrounding prose/code fences.
     */
    public static function extractJsonFromText(?string $text): ?array
    {
        if (! is_string($text)) {
            return null;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
