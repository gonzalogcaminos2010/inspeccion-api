<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryEquipmentFieldResource;
use App\Models\CategoryEquipmentField;
use App\Models\TemplateCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryEquipmentFieldController extends Controller
{
    use ApiResponse;

    public function store(Request $request, TemplateCategory $templateCategory)
    {
        $validated = $request->validate([
            'key_name' => 'required|string|max:64|regex:/^[a-z][a-z0-9_]*$/',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,select,boolean',
            'options' => 'nullable|array',
            'unit' => 'nullable|string|max:32',
            'is_required' => 'nullable|boolean',
            'is_mutable' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $exists = CategoryEquipmentField::where('template_category_id', $templateCategory->id)
            ->where('key_name', $validated['key_name'])
            ->exists();

        if ($exists) {
            return $this->error('Ya existe un campo con ese key_name en esta categoría.', 409);
        }

        $field = $templateCategory->equipmentFields()->create([
            'key_name' => $validated['key_name'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'options' => $validated['options'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'is_mutable' => $validated['is_mutable'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->success(new CategoryEquipmentFieldResource($field), 'Equipment field created successfully', 201);
    }

    public function update(Request $request, CategoryEquipmentField $categoryEquipmentField)
    {
        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:text,number,date,select,boolean',
            'options' => 'sometimes|nullable|array',
            'unit' => 'sometimes|nullable|string|max:32',
            'is_required' => 'sometimes|boolean',
            'is_mutable' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $categoryEquipmentField->update($validated);

        return $this->success(
            new CategoryEquipmentFieldResource($categoryEquipmentField->fresh()),
            'Equipment field updated successfully'
        );
    }

    public function destroy(CategoryEquipmentField $categoryEquipmentField)
    {
        $categoryEquipmentField->delete();

        return response()->noContent();
    }
}
