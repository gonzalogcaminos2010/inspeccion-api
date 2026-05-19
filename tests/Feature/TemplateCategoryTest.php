<?php

use App\Models\InspectionTemplate;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

function inspectorUser(): User
{
    return User::factory()->create(['role' => 'inspector', 'is_active' => true]);
}

it('lists categories with pagination', function () {
    Sanctum::actingAs(adminUser());

    TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    TemplateCategory::create(['code' => 'cat_b', 'name' => 'Cat B', 'is_active' => false]);

    $response = $this->getJson('/api/v1/template-categories');

    $response->assertOk()
        ->assertJsonStructure(['success', 'message', 'data', 'pagination'])
        ->assertJsonCount(2, 'data');
});

it('filters categories by active flag', function () {
    Sanctum::actingAs(adminUser());

    TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    TemplateCategory::create(['code' => 'cat_b', 'name' => 'Cat B', 'is_active' => false]);

    $response = $this->getJson('/api/v1/template-categories?active=true');

    $response->assertOk()->assertJsonCount(1, 'data');
});

it('admin can create a category', function () {
    Sanctum::actingAs(adminUser());

    $response = $this->postJson('/api/v1/template-categories', [
        'code' => 'nueva_cat',
        'name' => 'Nueva Categoría',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.code', 'nueva_cat')
        ->assertJsonPath('data.name', 'Nueva Categoría')
        ->assertJsonPath('data.is_active', true);

    expect(TemplateCategory::where('code', 'nueva_cat')->exists())->toBeTrue();
});

it('returns 409 when creating a category with duplicate code', function () {
    Sanctum::actingAs(adminUser());

    TemplateCategory::create(['code' => 'dup_code', 'name' => 'Dup', 'is_active' => true]);

    $response = $this->postJson('/api/v1/template-categories', [
        'code' => 'dup_code',
        'name' => 'Otra',
    ]);

    $response->assertStatus(409)->assertJsonPath('success', false);
});

it('rejects invalid slug format', function () {
    Sanctum::actingAs(adminUser());

    $response = $this->postJson('/api/v1/template-categories', [
        'code' => 'Invalid-Slug!',
        'name' => 'Invalid',
    ]);

    $response->assertStatus(422);
});

it('non-admin cannot create a category', function () {
    Sanctum::actingAs(inspectorUser());

    $response = $this->postJson('/api/v1/template-categories', [
        'code' => 'inspector_try',
        'name' => 'Try',
    ]);

    $response->assertStatus(403);
});

it('admin can update name and is_active but not code', function () {
    Sanctum::actingAs(adminUser());

    $cat = TemplateCategory::create(['code' => 'original', 'name' => 'Original', 'is_active' => true]);

    $response = $this->patchJson("/api/v1/template-categories/{$cat->id}", [
        'name' => 'Cambiado',
        'is_active' => false,
        'code' => 'try_to_change',
    ]);

    $response->assertOk();
    $cat->refresh();
    expect($cat->name)->toBe('Cambiado');
    expect($cat->is_active)->toBeFalse();
    expect($cat->code)->toBe('original');
});

it('non-admin cannot update a category', function () {
    Sanctum::actingAs(inspectorUser());

    $cat = TemplateCategory::create(['code' => 'cat_x', 'name' => 'X', 'is_active' => true]);

    $response = $this->patchJson("/api/v1/template-categories/{$cat->id}", ['name' => 'Y']);

    $response->assertStatus(403);
});

it('hard deletes a category with no template references', function () {
    Sanctum::actingAs(adminUser());

    $cat = TemplateCategory::create(['code' => 'unused_cat', 'name' => 'Unused', 'is_active' => true]);

    $response = $this->deleteJson("/api/v1/template-categories/{$cat->id}");

    $response->assertNoContent();
    expect(TemplateCategory::find($cat->id))->toBeNull();
});

it('soft deletes a category when templates reference it', function () {
    Sanctum::actingAs(adminUser());

    $cat = TemplateCategory::create(['code' => 'in_use', 'name' => 'In Use', 'is_active' => true]);
    InspectionTemplate::create([
        'name' => 'Template X',
        'code' => 'TPL-X',
        'vehicle_type' => 'in_use',
        'is_active' => true,
    ]);

    $response = $this->deleteJson("/api/v1/template-categories/{$cat->id}");

    $response->assertNoContent();
    $cat->refresh();
    expect($cat->is_active)->toBeFalse();
    expect(TemplateCategory::find($cat->id))->not->toBeNull();
});

it('non-admin cannot delete a category', function () {
    Sanctum::actingAs(inspectorUser());

    $cat = TemplateCategory::create(['code' => 'cat_x', 'name' => 'X', 'is_active' => true]);

    $response = $this->deleteJson("/api/v1/template-categories/{$cat->id}");

    $response->assertStatus(403);
});

it('any authenticated user can list categories', function () {
    Sanctum::actingAs(inspectorUser());

    TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);

    $response = $this->getJson('/api/v1/template-categories');

    $response->assertOk();
});
