<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryEquipmentFieldResource;
use App\Http\Resources\TemplateCategoryResource;
use App\Models\InspectionTemplate;
use App\Models\TemplateCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TemplateCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = TemplateCategory::query()->with('defaultTemplate');

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('with_fields')) {
            $query->with('equipmentFields');
        }

        $paginator = $query->orderBy('name')->paginate($request->query('per_page', 100));

        return $this->paginated(
            $paginator->through(fn ($cat) => new TemplateCategoryResource($cat)),
            'Template categories retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'default_template_id' => 'nullable|exists:inspection_templates,id',
            'default_inspection_interval_months' => 'nullable|integer|min:1|max:120',
        ]);

        if (TemplateCategory::where('code', $validated['code'])->exists()) {
            return $this->error('Ya existe una categoría con ese code.', 409);
        }

        $category = TemplateCategory::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
            'default_template_id' => $validated['default_template_id'] ?? null,
            'default_inspection_interval_months' => $validated['default_inspection_interval_months'] ?? null,
        ]);

        $category->load('defaultTemplate');

        return $this->success(new TemplateCategoryResource($category), 'Template category created successfully', 201);
    }

    public function update(Request $request, TemplateCategory $templateCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'default_template_id' => 'sometimes|nullable|exists:inspection_templates,id',
            'default_inspection_interval_months' => 'sometimes|nullable|integer|min:1|max:120',
        ]);

        $templateCategory->update($validated);

        return $this->success(
            new TemplateCategoryResource($templateCategory->fresh()->load('defaultTemplate')),
            'Template category updated successfully'
        );
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        $hasReferences = InspectionTemplate::where('vehicle_type', $templateCategory->code)
            ->orWhere('category_id', $templateCategory->id)
            ->exists();

        if ($hasReferences) {
            $templateCategory->update(['is_active' => false]);

            return response()->noContent();
        }

        $templateCategory->delete();

        return response()->noContent();
    }

    public function equipmentFields(TemplateCategory $templateCategory)
    {
        $fields = $templateCategory->equipmentFields()->get();

        return $this->success(
            CategoryEquipmentFieldResource::collection($fields),
            'Equipment fields retrieved successfully'
        );
    }
}
