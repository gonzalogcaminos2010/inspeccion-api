<?php

use App\Models\CategoryEquipmentField;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\InspectionRequest;
use App\Models\InspectionTemplate;
use App\Models\ServiceType;
use App\Models\TemplateCategory;
use App\Models\TemplateQuestion;
use App\Models\TemplateSection;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Build the minimum graph needed to exercise the categorised flow.
 * Returns: ['category','template','client','request','inspector','supervisor'].
 */
function flowContext(): array
{
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $supervisor = User::factory()->create(['role' => 'supervisor', 'is_active' => true]);

    $client = Client::create(['name' => 'Mining Co']);
    $serviceType = ServiceType::create(['name' => 'Insp']);
    $category = TemplateCategory::create(['code' => 'cat_test', 'name' => 'Cat Test', 'is_active' => true]);

    $template = InspectionTemplate::create([
        'name' => 'Tpl', 'code' => 'TPL-FLOW-'.uniqid(), 'is_active' => true, 'category_id' => $category->id,
    ]);
    $section = TemplateSection::create([
        'inspection_template_id' => $template->id, 'name' => 'Sec 1', 'order' => 1,
    ]);
    TemplateQuestion::create([
        'template_section_id' => $section->id,
        'text' => 'Funciona?',
        'type' => 'yes_no',
        'is_required' => true,
        'order' => 1,
        'fail_values' => ['0'],
    ]);

    $category->update(['default_template_id' => $template->id, 'default_inspection_interval_months' => 12]);

    CategoryEquipmentField::create([
        'template_category_id' => $category->id,
        'key_name' => 'marca', 'label' => 'Marca', 'type' => 'text',
        'is_required' => true, 'is_mutable' => false,
    ]);
    CategoryEquipmentField::create([
        'template_category_id' => $category->id,
        'key_name' => 'horas_motor', 'label' => 'Horas Motor', 'type' => 'number',
        'is_required' => false, 'is_mutable' => true,
    ]);
    CategoryEquipmentField::create([
        'template_category_id' => $category->id,
        'key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date',
        'is_required' => false, 'is_mutable' => true,
    ]);

    $request = InspectionRequest::create([
        'request_number' => 'REQ-'.uniqid(),
        'client_id' => $client->id,
        'service_type_id' => $serviceType->id,
        'requested_date' => now()->toDateString(),
    ]);

    return compact('inspector', 'supervisor', 'client', 'category', 'template', 'request');
}

it('WO store with only category_id creates a placeholder equipment', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $response = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id,
        'items' => [
            ['category_id' => $ctx['category']->id],
        ],
    ]);

    $response->assertCreated();

    $itemArr = $response->json('data.items.0');
    expect($itemArr['is_equipment_placeholder'])->toBeTrue();
    expect($itemArr['equipment_id'])->not->toBeNull();

    $eq = Equipment::find($itemArr['equipment_id']);
    expect($eq->status)->toBe('placeholder');
    expect($eq->client_id)->toBe($ctx['client']->id);
    expect($eq->category_id)->toBe($ctx['category']->id);
});

it('WO store with neither equipment nor category fails', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $response = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'items' => [['notes' => 'nothing']],
    ]);

    $response->assertStatus(422);
});

it('resolve-equipment with new_equipment swaps and deletes placeholder', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');
    $placeholderId = $createResp->json('data.items.0.equipment_id');

    $resolveResp = $this->postJson("/api/v1/work-order-items/{$itemId}/resolve-equipment", [
        'new_equipment' => [
            'name' => 'Hilux 2020',
            'plate' => 'ABC123',
            'brand' => 'Toyota',
        ],
    ]);

    $resolveResp->assertOk();
    $newId = $resolveResp->json('data.equipment_id');
    expect($newId)->not->toBe($placeholderId);
    expect(Equipment::find($placeholderId))->toBeNull();
    expect(Equipment::find($newId)->client_id)->toBe($ctx['client']->id);
});

it('resolve-equipment with existing equipment swaps without deleting it', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $existing = Equipment::create([
        'client_id' => $ctx['client']->id,
        'category_id' => $ctx['category']->id,
        'name' => 'Existing',
        'status' => 'active',
    ]);

    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');

    $resolveResp = $this->postJson("/api/v1/work-order-items/{$itemId}/resolve-equipment", [
        'equipment_id' => $existing->id,
    ]);

    $resolveResp->assertOk();
    expect($resolveResp->json('data.equipment_id'))->toBe($existing->id);
    expect(Equipment::find($existing->id))->not->toBeNull();
});

it('resolve-equipment rejects existing equipment from another client', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $otherClient = Client::create(['name' => 'Other']);
    $other = Equipment::create([
        'client_id' => $otherClient->id,
        'category_id' => $ctx['category']->id,
        'name' => 'Other',
        'status' => 'active',
    ]);

    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');

    $this->postJson("/api/v1/work-order-items/{$itemId}/resolve-equipment", [
        'equipment_id' => $other->id,
    ])->assertStatus(422);
});

it('resolve-equipment returns duplicate_equipment when plate already exists for the client', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    Equipment::create([
        'client_id' => $ctx['client']->id,
        'category_id' => $ctx['category']->id,
        'name' => 'Dup',
        'plate' => 'XYZ999',
        'status' => 'active',
    ]);

    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');

    $resp = $this->postJson("/api/v1/work-order-items/{$itemId}/resolve-equipment", [
        'new_equipment' => ['name' => 'New', 'plate' => 'XYZ999'],
    ]);

    $resp->assertStatus(422)
        ->assertJsonPath('error', 'duplicate_equipment')
        ->assertJsonPath('matched_by', 'plate');
});

it('inspection start resolves template from category default when item has none', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');

    // Resolve before starting so submit isn't blocked.
    $this->postJson("/api/v1/work-order-items/{$itemId}/resolve-equipment", [
        'new_equipment' => ['name' => 'EQ-1'],
    ])->assertOk();

    $startResp = $this->postJson('/api/v1/inspections', ['work_order_item_id' => $itemId]);

    $startResp->assertCreated()
        ->assertJsonPath('data.inspection_template_id', $ctx['template']->id);
});

it('inspection start returns 422 when no template can be resolved', function () {
    $ctx = flowContext();
    // strip the default to force the failure
    $ctx['category']->update(['default_template_id' => null]);

    Sanctum::actingAs($ctx['inspector']);

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'pending',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'status' => 'pending',
    ]);

    $this->postJson('/api/v1/inspections', ['work_order_item_id' => $item->id])
        ->assertStatus(422);
});

it('equipment-data buffers fields on the inspection', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $inspection = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'in_progress', 'started_at' => now(),
    ]);

    $this->postJson("/api/v1/inspections/{$inspection->id}/equipment-data", [
        'fields' => ['marca' => 'Toyota', 'horas_motor' => 1000],
    ])->assertOk();

    expect($inspection->fresh()->equipment_data)->toBe(['marca' => 'Toyota', 'horas_motor' => 1000]);
});

it('GET inspection exposes equipment_fields with merged value and locked flag', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    // Equipment already has 'marca' persisted (immutable field, first capture done).
    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
        'metadata' => ['marca' => 'Toyota'],
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $inspection = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'in_progress', 'started_at' => now(),
        // In-flight buffer only fills the mutable field.
        'equipment_data' => ['horas_motor' => 500],
    ]);

    $fields = $this->getJson("/api/v1/inspections/{$inspection->id}")
        ->assertOk()
        ->json('data.equipment_fields');

    $byKey = collect($fields)->keyBy('key_name');

    // Immutable field with persisted value -> value from metadata, locked.
    expect($byKey['marca']['value'])->toBe('Toyota');
    expect($byKey['marca']['locked'])->toBeTrue();
    expect($byKey['marca']['is_required'])->toBeTrue();

    // Mutable field buffered in this inspection -> value from buffer, not locked.
    expect($byKey['horas_motor']['value'])->toBe(500);
    expect($byKey['horas_motor']['locked'])->toBeFalse();

    // Untouched field -> null, not locked.
    expect($byKey['proxima_inspeccion']['value'])->toBeNull();
    expect($byKey['proxima_inspeccion']['locked'])->toBeFalse();
});

it('submit materializes a placeholder from the inspector identification data', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    // Order created "a determinar" -> backend makes a placeholder equipment.
    $createResp = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id,
        'items' => [['category_id' => $ctx['category']->id]],
    ]);
    $itemId = $createResp->json('data.items.0.id');
    $placeholderId = WorkOrderItem::find($itemId)->equipment_id;
    expect(Equipment::find($placeholderId)->status)->toBe('placeholder');

    $insp = Inspection::create([
        'work_order_item_id' => $itemId,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $placeholderId,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    // Inspector identifies the equipment in the field (marca is required).
    $this->postJson("/api/v1/inspections/{$insp->id}/equipment-data", [
        'fields' => ['marca' => 'Liebherr', 'modelo' => 'LTM 1100', 'horas_motor' => 1200],
    ])->assertOk();

    // Now submit succeeds (no separate resolve step) and the placeholder becomes real.
    $this->postJson("/api/v1/inspections/{$insp->id}/submit")->assertOk();

    $eq = Equipment::find($placeholderId)->fresh();
    expect($eq->status)->toBe('active');
    expect($eq->brand)->toBe('Liebherr');
    expect($eq->model)->toBe('LTM 1100');
    expect($eq->name)->toBe('Liebherr LTM 1100');
    expect($eq->metadata['horas_motor'])->toBe(1200);
    expect(WorkOrderItem::find($itemId)->is_equipment_placeholder)->toBeFalse();
});

it('submit is blocked when required identification fields are missing', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $insp = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'in_progress', 'started_at' => now(),
    ]);

    // 'marca' is_required=true but not provided in equipment_data nor in equipment.metadata.
    $resp = $this->postJson("/api/v1/inspections/{$insp->id}/submit");

    $resp->assertStatus(422)->assertJsonPath('missing_fields.0.key_name', 'marca');
});

it('submit syncs equipment_data → equipment.metadata respecting is_mutable', function () {
    $ctx = flowContext();
    Sanctum::actingAs($ctx['inspector']);

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
        'metadata' => ['marca' => 'Toyota'], // immutable, already set
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $insp = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'in_progress', 'started_at' => now(),
        'equipment_data' => [
            'marca' => 'Ford',          // immutable + already set → ignored
            'horas_motor' => 1200,      // mutable → applied
            'proxima_inspeccion' => '2027-01-15',
        ],
    ]);

    $this->postJson("/api/v1/inspections/{$insp->id}/submit")->assertOk();

    $eq->refresh();
    expect($eq->metadata['marca'])->toBe('Toyota');           // unchanged (immutable)
    expect($eq->metadata['horas_motor'])->toBe(1200);          // updated (mutable)
    expect($eq->next_inspection_due_at?->toDateString())->toBe('2027-01-15');
});

it('approve auto-fills next_inspection_due_at from category interval when empty', function () {
    $ctx = flowContext();

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
        'metadata' => ['marca' => 'Toyota'],
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $insp = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'submitted', 'started_at' => now(),
        'overall_result' => 'approved', 'score' => 100,
    ]);

    Sanctum::actingAs($ctx['supervisor']);
    $this->postJson("/api/v1/inspections/{$insp->id}/approve", [])->assertOk();

    $eq->refresh();
    expect($eq->last_inspection_id)->toBe($insp->id);
    expect($eq->last_inspection_completed_at)->not->toBeNull();
    // 12-month interval from approved_at; just assert it lands ~12 months out.
    expect($eq->next_inspection_due_at)->not->toBeNull();
    $months = now()->diffInMonths($eq->next_inspection_due_at, false);
    expect($months)->toBeGreaterThanOrEqual(11)->toBeLessThanOrEqual(13);
});

it('approve respects supervisor override of next_inspection_due_at', function () {
    $ctx = flowContext();

    $eq = Equipment::create([
        'client_id' => $ctx['client']->id, 'category_id' => $ctx['category']->id,
        'name' => 'EQ', 'status' => 'active',
        'next_inspection_due_at' => '2099-12-31',
    ]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $ctx['inspector']->id, 'status' => 'in_progress',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id, 'equipment_id' => $eq->id,
        'category_id' => $ctx['category']->id,
        'inspection_template_id' => $ctx['template']->id,
        'status' => 'in_progress',
    ]);
    $insp = Inspection::create([
        'work_order_item_id' => $item->id,
        'inspection_template_id' => $ctx['template']->id,
        'equipment_id' => $eq->id,
        'inspector_id' => $ctx['inspector']->id,
        'status' => 'submitted', 'started_at' => now(),
        'overall_result' => 'approved', 'score' => 100,
    ]);

    Sanctum::actingAs($ctx['supervisor']);
    $this->postJson("/api/v1/inspections/{$insp->id}/approve", [
        'next_inspection_due_at' => '2027-06-30',
    ])->assertOk();

    expect($eq->fresh()->next_inspection_due_at?->toDateString())->toBe('2027-06-30');
});
