<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        $query = TemplateCategory::query();

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
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
        ]);

        if (TemplateCategory::where('code', $validated['code'])->exists()) {
            return $this->error('Ya existe una categoría con ese code.', 409);
        }

        $category = TemplateCategory::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->success(new TemplateCategoryResource($category), 'Template category created successfully', 201);
    }

    public function update(Request $request, TemplateCategory $templateCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $templateCategory->update($validated);

        return $this->success(new TemplateCategoryResource($templateCategory->fresh()), 'Template category updated successfully');
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        $hasReferences = InspectionTemplate::where('vehicle_type', $templateCategory->code)->exists();

        if ($hasReferences) {
            $templateCategory->update(['is_active' => false]);

            return response()->noContent();
        }

        $templateCategory->delete();

        return response()->noContent();
    }
}
