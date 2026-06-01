<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiAnalysis;
use App\Models\InspectionPhoto;
use App\Services\Ai\AiConfig;
use App\Services\Ai\PhotoAnalysisException;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AiAnalysisController extends Controller
{
    use ApiResponse;

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    private const MEDIA_TYPE_MAP = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
    ];

    public function analyzePhoto(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => 'required|integer',
        ]);

        if (! AiConfig::enabled()) {
            return $this->error('La integración con IA no está configurada o está deshabilitada.', 503);
        }

        $photo = InspectionPhoto::with('inspection')->find($validated['photo_id']);
        if (! $photo) {
            return $this->error('Foto no encontrada.', 404);
        }

        $user = $request->user();
        if (! in_array($user->role, ['admin', 'supervisor']) && $user->id !== $photo->inspection->inspector_id) {
            return $this->error('No tenés acceso a esta inspección.', 403);
        }

        if (! Storage::disk('public')->exists($photo->photo_path)) {
            return $this->error('Archivo de foto no encontrado en el storage.', 404);
        }

        $fileSize = Storage::disk('public')->size($photo->photo_path);
        if ($fileSize > self::MAX_FILE_SIZE) {
            return $this->error('La foto supera el tamaño máximo de 10MB.', 422);
        }

        $extension = strtolower(pathinfo($photo->photo_path, PATHINFO_EXTENSION));
        $mediaType = self::MEDIA_TYPE_MAP[$extension] ?? 'image/jpeg';

        $contents = Storage::disk('public')->get($photo->photo_path);
        $base64 = base64_encode($contents);

        $service = AiConfig::makeAnalyzer();

        try {
            $result = $service->analyze($base64, $mediaType);
        } catch (PhotoAnalysisException $e) {
            return $this->error('Error al analizar la foto: '.$e->getMessage(), 502);
        }

        $parsed = $result['parsed'];

        $analysis = AiAnalysis::create([
            'photo_id' => $photo->id,
            'inspection_id' => $photo->inspection_id,
            'requested_by_user_id' => $user->id,
            'model' => AiConfig::provider().':'.AiConfig::model(),
            'prompt_version' => 'v1',
            'response_json' => $result['raw'],
            'has_defect' => (bool) ($parsed['has_defect'] ?? false),
            'severity' => $parsed['severity'] ?? null,
            'latency_ms' => $result['latency_ms'],
        ]);

        return $this->success(array_merge($parsed, [
            'analysis_id' => $analysis->id,
        ]), 'Análisis completado.');
    }

    public function markUsed(Request $request, AiAnalysis $aiAnalysis)
    {
        if ($aiAnalysis->requested_by_user_id !== $request->user()->id) {
            return $this->error('Solo el usuario que solicitó el análisis puede marcarlo como usado.', 403);
        }

        $aiAnalysis->update(['used_by_user' => true]);

        return response()->noContent();
    }
}
