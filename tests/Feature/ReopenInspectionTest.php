<?php

use App\Models\Client;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\InspectionRequest;
use App\Models\InspectionTemplate;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeInspection(int $inspectorId, string $status): Inspection
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
        'status' => $status,
        'started_at' => now()->subHour(),
        'completed_at' => in_array($status, ['submitted', 'completed']) ? now() : null,
    ]);
}

it('inspector owner reopens a submitted inspection', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'submitted');

    Sanctum::actingAs($inspector);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertOk()
        ->assertJsonPath('data.status', 'in_progress');

    $inspection->refresh();
    expect($inspection->status)->toBe('in_progress');
    expect($inspection->completed_at)->toBeNull();
    expect($inspection->started_at)->not->toBeNull();
});

it('inspector owner reopens a returned inspection', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'returned');

    Sanctum::actingAs($inspector);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertOk();
    expect($inspection->fresh()->status)->toBe('in_progress');
});

it('another inspector cannot reopen another inspector inspection', function () {
    $owner = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $other = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeInspection($owner->id, 'submitted');

    Sanctum::actingAs($other);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertStatus(403);
});

it('supervisor cannot reopen another inspector inspection', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $supervisor = User::factory()->create(['role' => 'supervisor', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'submitted');

    Sanctum::actingAs($supervisor);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertStatus(403);
});

it('admin cannot reopen another inspector inspection', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'submitted');

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertStatus(403);
});

it('reopening an in_progress inspection returns 409', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'in_progress');

    Sanctum::actingAs($inspector);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertStatus(409);
});

it('reopening a completed inspection returns 409', function () {
    $inspector = User::factory()->create(['role' => 'inspector', 'is_active' => true]);
    $inspection = makeInspection($inspector->id, 'completed');

    Sanctum::actingAs($inspector);

    $response = $this->postJson("/api/v1/inspections/{$inspection->id}/reopen");

    $response->assertStatus(409);
});
