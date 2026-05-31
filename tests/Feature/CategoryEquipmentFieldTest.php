<?php

use App\Models\CategoryEquipmentField;
use App\Models\InspectionTemplate;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function cefAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

function cefInspector(): User
{
    return User::factory()->create(['role' => 'inspector', 'is_active' => true]);
}

it('admin can attach a field to a category', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);

    $response = $this->postJson("/api/v1/template-categories/{$cat->id}/equipment-fields", [
        'key_name' => 'marca',
        'label' => 'Marca',
        'type' => 'text',
        'is_required' => true,
        'is_mutable' => false,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.key_name', 'marca')
        ->assertJsonPath('data.is_mutable', false);

    expect(CategoryEquipmentField::where('template_category_id', $cat->id)->count())->toBe(1);
});

it('rejects duplicate key_name within the same category', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    CategoryEquipmentField::create([
        'template_category_id' => $cat->id,
        'key_name' => 'marca',
        'label' => 'Marca',
        'type' => 'text',
    ]);

    $response = $this->postJson("/api/v1/template-categories/{$cat->id}/equipment-fields", [
        'key_name' => 'marca',
        'label' => 'Marca dup',
        'type' => 'text',
    ]);

    $response->assertStatus(409);
});

it('rejects invalid key_name slug', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);

    $response = $this->postJson("/api/v1/template-categories/{$cat->id}/equipment-fields", [
        'key_name' => 'Bad-Slug!',
        'label' => 'X',
        'type' => 'text',
    ]);

    $response->assertStatus(422);
});

it('non-admin cannot create a field', function () {
    Sanctum::actingAs(cefInspector());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);

    $response = $this->postJson("/api/v1/template-categories/{$cat->id}/equipment-fields", [
        'key_name' => 'marca',
        'label' => 'Marca',
        'type' => 'text',
    ]);

    $response->assertStatus(403);
});

it('lists fields sorted by sort_order for any authenticated user', function () {
    Sanctum::actingAs(cefInspector());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    CategoryEquipmentField::create(['template_category_id' => $cat->id, 'key_name' => 'b', 'label' => 'B', 'type' => 'text', 'sort_order' => 2]);
    CategoryEquipmentField::create(['template_category_id' => $cat->id, 'key_name' => 'a', 'label' => 'A', 'type' => 'text', 'sort_order' => 1]);

    $response = $this->getJson("/api/v1/template-categories/{$cat->id}/equipment-fields");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.key_name', 'a')
        ->assertJsonPath('data.1.key_name', 'b');
});

it('admin can update a field', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    $field = CategoryEquipmentField::create([
        'template_category_id' => $cat->id,
        'key_name' => 'marca',
        'label' => 'Marca',
        'type' => 'text',
        'is_required' => false,
    ]);

    $response = $this->patchJson("/api/v1/category-equipment-fields/{$field->id}", [
        'label' => 'Marca Comercial',
        'is_required' => true,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.label', 'Marca Comercial')
        ->assertJsonPath('data.is_required', true);
});

it('admin can delete a field', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    $field = CategoryEquipmentField::create([
        'template_category_id' => $cat->id,
        'key_name' => 'tmp',
        'label' => 'Tmp',
        'type' => 'text',
    ]);

    $response = $this->deleteJson("/api/v1/category-equipment-fields/{$field->id}");

    $response->assertNoContent();
    expect(CategoryEquipmentField::find($field->id))->toBeNull();
});

it('category update accepts default_template_id and interval', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    $tpl = InspectionTemplate::create([
        'name' => 'Tpl', 'code' => 'TPL-A', 'is_active' => true, 'category_id' => $cat->id,
    ]);

    $response = $this->patchJson("/api/v1/template-categories/{$cat->id}", [
        'default_template_id' => $tpl->id,
        'default_inspection_interval_months' => 12,
    ]);

    $response->assertOk();
    $cat->refresh();
    expect($cat->default_template_id)->toBe($tpl->id);
    expect($cat->default_inspection_interval_months)->toBe(12);
});

it('cascade-deletes fields when a category is hard-deleted', function () {
    Sanctum::actingAs(cefAdmin());
    $cat = TemplateCategory::create(['code' => 'cat_a', 'name' => 'Cat A', 'is_active' => true]);
    CategoryEquipmentField::create([
        'template_category_id' => $cat->id,
        'key_name' => 'marca',
        'label' => 'Marca',
        'type' => 'text',
    ]);

    $this->deleteJson("/api/v1/template-categories/{$cat->id}")->assertNoContent();

    expect(CategoryEquipmentField::where('template_category_id', $cat->id)->count())->toBe(0);
});
