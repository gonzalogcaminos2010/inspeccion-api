<?php

use App\Models\Client;
use App\Models\Equipment;
use App\Models\InspectionRequest;
use App\Models\InspectionTemplate;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function scaffold(): array
{
    $client = Client::create(['name' => 'Mining Co']);
    $serviceType = ServiceType::create(['name' => 'Insp']);
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

    return compact('client', 'serviceType', 'template', 'request');
}

function makeEquipment(int $clientId): Equipment
{
    return Equipment::create([
        'client_id' => $clientId,
        'name' => 'EQ-'.uniqid(),
        'type' => 'truck',
    ]);
}

it('stores per-item inspector_id when creating a work order', function () {
    $ctx = scaffold();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $inspectorA = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspectorB = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $eqA = makeEquipment($ctx['client']->id);
    $eqB = makeEquipment($ctx['client']->id);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/work-orders', [
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $inspectorA->id,
        'items' => [
            ['equipment_id' => $eqA->id, 'inspection_template_id' => $ctx['template']->id, 'inspector_id' => $inspectorA->id],
            ['equipment_id' => $eqB->id, 'inspection_template_id' => $ctx['template']->id, 'inspector_id' => $inspectorB->id],
        ],
    ]);

    $response->assertCreated();

    $items = WorkOrderItem::all();
    expect($items)->toHaveCount(2);
    expect($items->firstWhere('equipment_id', $eqA->id)->inspector_id)->toBe($inspectorA->id);
    expect($items->firstWhere('equipment_id', $eqB->id)->inspector_id)->toBe($inspectorB->id);
});

it('effective inspector falls back to the work order lead when item has none', function () {
    $ctx = scaffold();
    $lead = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $lead->id,
        'status' => 'pending',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
    ]);

    expect($item->effective_inspector_id)->toBe($lead->id);

    $item->update(['inspector_id' => User::factory()->create(['role' => 'inspector'])->id]);
    expect($item->fresh()->effective_inspector_id)->toBe($item->fresh()->inspector_id);
});

it('work orders filter includes orders where inspector only owns items', function () {
    $ctx = scaffold();
    $lead = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $helper = User::factory()->create(['role' => 'inspector', 'is_active' => true]);

    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $lead->id,
        'status' => 'pending',
    ]);
    WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
        'inspector_id' => $helper->id,
    ]);

    Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

    // Helper is not the WO lead but owns an item -> WO must appear.
    $this->getJson("/api/v1/work-orders?inspector_id={$helper->id}")
        ->assertOk()
        ->assertJsonPath('data.0.id', $wo->id);

    // Lead also sees it.
    $this->getJson("/api/v1/work-orders?inspector_id={$lead->id}")
        ->assertOk()
        ->assertJsonPath('data.0.id', $wo->id);
});

it('my-items endpoint returns directly assigned and fallback items only', function () {
    $ctx = scaffold();
    $inspectorA = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspectorB = User::factory()->create(['role' => 'inspector', 'is_active' => true]);

    // WO led by A: item1 assigned to B, item2 unassigned (falls back to A).
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $inspectorA->id,
        'status' => 'pending',
    ]);
    $itemForB = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
        'inspector_id' => $inspectorB->id,
    ]);
    $fallbackItemForA = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
    ]);

    Sanctum::actingAs($inspectorA);
    $resA = $this->getJson('/api/v1/work-order-items')->assertOk();
    $idsA = collect($resA->json('data'))->pluck('id');
    expect($idsA)->toContain($fallbackItemForA->id);
    expect($idsA)->not->toContain($itemForB->id);

    Sanctum::actingAs($inspectorB);
    $resB = $this->getJson('/api/v1/work-order-items')->assertOk();
    $idsB = collect($resB->json('data'))->pluck('id');
    expect($idsB)->toContain($itemForB->id);
    expect($idsB)->not->toContain($fallbackItemForA->id);
});

it('supervisor can reassign an item to another inspector', function () {
    $ctx = scaffold();
    $inspectorA = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspectorB = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $supervisor = User::factory()->create(['role' => 'supervisor', 'is_active' => true]);

    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $inspectorA->id,
        'status' => 'pending',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
        'inspector_id' => $inspectorA->id,
    ]);

    Sanctum::actingAs($supervisor);
    $this->patchJson("/api/v1/work-order-items/{$item->id}", ['inspector_id' => $inspectorB->id])
        ->assertOk()
        ->assertJsonPath('data.inspector_id', $inspectorB->id);

    expect($item->fresh()->inspector_id)->toBe($inspectorB->id);
});

it('inspector cannot reassign an item', function () {
    $ctx = scaffold();
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $wo = WorkOrder::create([
        'order_number' => 'OT-'.uniqid(),
        'inspection_request_id' => $ctx['request']->id,
        'inspector_id' => $inspector->id,
        'status' => 'pending',
    ]);
    $item = WorkOrderItem::create([
        'work_order_id' => $wo->id,
        'equipment_id' => makeEquipment($ctx['client']->id)->id,
        'inspection_template_id' => $ctx['template']->id,
    ]);

    Sanctum::actingAs($inspector);
    $this->patchJson("/api/v1/work-order-items/{$item->id}", ['inspector_id' => $inspector->id])
        ->assertStatus(403);
});
