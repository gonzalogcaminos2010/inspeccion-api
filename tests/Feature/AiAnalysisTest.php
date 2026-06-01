<?php

use App\Models\AiAnalysis;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\InspectionPhoto;
use App\Models\InspectionRequest;
use App\Models\InspectionTemplate;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    config([
        // These tests exercise the Anthropic adapter explicitly.
        'services.ai.provider' => 'anthropic',
        'services.ai.photo_analysis_enabled' => true,
        'services.anthropic.api_key' => 'test-key-xyz',
        'services.anthropic.photo_analysis_enabled' => true,
        'services.anthropic.model' => 'claude-sonnet-4-6',
    ]);
});

function makeAiInspectionForInspector(int $inspectorId): Inspection
{
    $client = Client::create(['name' => 'Mining Co']);
    $serviceType = ServiceType::create(['name' => 'Insp']);
    $equipment = Equipment::create([
        'client_id' => $client->id,
        'name' => 'EQ-'.uniqid(),
        'type' => 'truck',
    ]);
    $template = InspectionTemplate::create([
        'name' => 'Tpl',
        'code' => 'TPL-'.uniqid(),
        'is_active' => true,
    ]);
    $request = InspectionRequest::create([
        'request_number' => 'REQ-'.uniqid(),
        'client_id' => $client->id,
        'service_type_id' => $serviceType->id,
        'requested_date' => now()->toDateString(),
    ]);
    $workOrder = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $request->id,
        'inspector_id' => $inspectorId,
        'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $workOrder->id,
        'equipment_id' => $equipment->id,
        'inspection_template_id' => $template->id,
        'status' => 'in_progress',
    ]);

    return Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $template->id,
        'equipment_id' => $equipment->id,
        'inspector_id' => $inspectorId,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);
}

function makeAiPhoto(int $inspectorId): array
{
    $inspection = makeAiInspectionForInspector($inspectorId);
    $upload = UploadedFile::fake()->image('photo.jpg', 800, 600);
    $path = Storage::disk('public')->putFile('inspections/'.$inspection->id, $upload);

    $photo = InspectionPhoto::create([
        'inspection_id' => $inspection->id,
        'photo_path' => $path,
    ]);

    return [$inspection, $photo];
}

function fakeClaudeResponse(array $payload): array
{
    return [
        'id' => 'msg_'.uniqid(),
        'type' => 'message',
        'role' => 'assistant',
        'model' => 'claude-sonnet-4-6',
        'content' => [
            ['type' => 'text', 'text' => json_encode($payload)],
        ],
        'stop_reason' => 'end_turn',
    ];
}

function fakeGeminiResponse(array $payload): array
{
    return [
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        ['text' => json_encode($payload)],
                    ],
                ],
                'finishReason' => 'STOP',
            ],
        ],
    ];
}

it('analyzes a photo with detected defect', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fake([
        'api.anthropic.com/*' => Http::response(fakeClaudeResponse([
            'has_defect' => true,
            'title' => 'Fuga hidráulica visible',
            'description' => 'Se observa pérdida de fluido en la conexión inferior del cilindro.',
            'severity' => 'HIGH',
            'defect_type' => 'fuga hidraulica',
            'observations' => 'El cilindro presenta una fuga clara en la conexión inferior.',
        ]), 200),
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertOk()
        ->assertJsonPath('data.has_defect', true)
        ->assertJsonPath('data.severity', 'HIGH')
        ->assertJsonPath('data.title', 'Fuga hidráulica visible');

    $analysisId = $response->json('data.analysis_id');
    $stored = AiAnalysis::find($analysisId);
    expect($stored)->not->toBeNull();
    expect($stored->has_defect)->toBeTrue();
    expect($stored->severity)->toBe('HIGH');
    expect($stored->model)->toBe('anthropic:claude-sonnet-4-6');
    expect($stored->prompt_version)->toBe('v1');
    expect($stored->used_by_user)->toBeFalse();
    expect($stored->latency_ms)->toBeGreaterThanOrEqual(0);
});

it('analyzes a photo with no defects', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fake([
        'api.anthropic.com/*' => Http::response(fakeClaudeResponse([
            'has_defect' => false,
            'title' => null,
            'description' => null,
            'severity' => null,
            'defect_type' => null,
            'observations' => 'Equipo en buen estado, sin defectos visibles.',
        ]), 200),
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertOk()
        ->assertJsonPath('data.has_defect', false)
        ->assertJsonPath('data.observations', 'Equipo en buen estado, sin defectos visibles.');

    expect(AiAnalysis::where('has_defect', false)->count())->toBe(1);
});

it('returns 404 when photo does not exist', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => 99999]);

    $response->assertStatus(404);
});

it('returns 503 when ANTHROPIC_API_KEY is not configured', function () {
    config(['services.anthropic.api_key' => null]);

    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertStatus(503);
});

it('returns 503 when AI_PHOTO_ANALYSIS_ENABLED is false', function () {
    config(['services.ai.photo_analysis_enabled' => false]);

    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertStatus(503);
});

it('returns 502 when Claude returns malformed JSON twice', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'esto no es JSON']],
        ], 200),
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertStatus(502);
});

it('returns 422 when photo file exceeds 10MB', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeAiInspectionForInspector($inspector->id);

    $bigContent = str_repeat('A', 11 * 1024 * 1024);
    $path = 'inspections/'.$inspection->id.'/big.jpg';
    Storage::disk('public')->put($path, $bigContent);

    $photo = InspectionPhoto::create([
        'inspection_id' => $inspection->id,
        'photo_path' => $path,
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertStatus(422);
});

it('returns 403 when inspector is not the assigned one', function () {
    $owner = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $other = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($owner->id);

    Sanctum::actingAs($other);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertStatus(403);
});

it('allows admin to analyze any photo', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fake([
        'api.anthropic.com/*' => Http::response(fakeClaudeResponse([
            'has_defect' => false,
            'observations' => 'OK.',
        ]), 200),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertOk();
});

it('marks an analysis as used by the requester', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    $analysis = AiAnalysis::create([
        'photo_id' => $photo->id,
        'inspection_id' => $inspection->id,
        'requested_by_user_id' => $inspector->id,
        'model' => 'claude-sonnet-4-6',
        'prompt_version' => 'v1',
        'response_json' => ['ok' => true],
        'has_defect' => true,
        'severity' => 'LOW',
        'latency_ms' => 1234,
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->patchJson("/api/v1/ai/analyses/{$analysis->id}/mark-used");

    $response->assertNoContent();
    expect($analysis->fresh()->used_by_user)->toBeTrue();
});

it('rejects mark-used by non-requester', function () {
    $owner = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $other = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($owner->id);

    $analysis = AiAnalysis::create([
        'photo_id' => $photo->id,
        'inspection_id' => $inspection->id,
        'requested_by_user_id' => $owner->id,
        'model' => 'claude-sonnet-4-6',
        'prompt_version' => 'v1',
        'response_json' => ['ok' => true],
        'has_defect' => false,
        'latency_ms' => 100,
    ]);

    Sanctum::actingAs($other);

    $response = $this->patchJson("/api/v1/ai/analyses/{$analysis->id}/mark-used");

    $response->assertStatus(403);
    expect($analysis->fresh()->used_by_user)->toBeFalse();
});

it('retries once and succeeds when first response is malformed but second is valid', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fakeSequence('api.anthropic.com/*')
        ->push(['content' => [['type' => 'text', 'text' => 'no es json']]], 200)
        ->push(fakeClaudeResponse([
            'has_defect' => true,
            'title' => 'Corrosion menor',
            'description' => 'Se observa oxidacion superficial.',
            'severity' => 'LOW',
            'defect_type' => 'corrosion',
            'observations' => 'Oxidacion superficial en estructura.',
        ]), 200);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertOk()->assertJsonPath('data.severity', 'LOW');
});

it('analyzes a photo via the Gemini provider', function () {
    config([
        'services.ai.provider' => 'gemini',
        'services.gemini.api_key' => 'gemini-test-key',
        'services.gemini.model' => 'gemini-2.0-flash',
    ]);

    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    [$inspection, $photo] = makeAiPhoto($inspector->id);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(fakeGeminiResponse([
            'has_defect' => true,
            'title' => 'Corrosión en chasis',
            'description' => 'Oxidación avanzada en el larguero.',
            'severity' => 'MEDIUM',
            'defect_type' => 'corrosion',
            'observations' => 'Se observa corrosión en el chasis.',
        ]), 200),
    ]);

    Sanctum::actingAs($inspector);

    $response = $this->postJson('/api/v1/ai/analyze-photo', ['photo_id' => $photo->id]);

    $response->assertOk()
        ->assertJsonPath('data.has_defect', true)
        ->assertJsonPath('data.severity', 'MEDIUM')
        ->assertJsonPath('data.title', 'Corrosión en chasis');

    $stored = AiAnalysis::find($response->json('data.analysis_id'));
    expect($stored->model)->toBe('gemini:gemini-2.0-flash');
    expect($stored->has_defect)->toBeTrue();
});

it('hides AI (ai_enabled=false) when the active provider has no key', function () {
    config([
        'services.ai.provider' => 'gemini',
        'services.gemini.api_key' => null,
    ]);
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    Sanctum::actingAs($inspector);

    $me = $this->getJson('/api/v1/me');
    $me->assertOk()->assertJsonPath('data.ai_enabled', false);
});
