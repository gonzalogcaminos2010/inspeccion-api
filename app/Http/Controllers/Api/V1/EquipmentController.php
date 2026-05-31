<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Equipment::query()->with(['client', 'category']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('plate', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('internal_code', 'like', "%{$search}%");
            });
        }

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($nextDueBefore = $request->query('next_due_before')) {
            $query->whereNotNull('next_inspection_due_at')
                ->whereDate('next_inspection_due_at', '<=', $nextDueBefore);
        }

        $equipment = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $equipment->through(fn ($item) => new EquipmentResource($item)),
            'Equipment retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:template_categories,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'plate' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'internal_code' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        if ($dupe = $this->findDuplicate($validated, null)) {
            return response()->json([
                'success' => false,
                'error' => 'duplicate_equipment',
                'matched_by' => $dupe['matched_by'],
                'existing_equipment' => new EquipmentResource($dupe['equipment']->load('client', 'category')),
            ], 422);
        }

        $equipment = Equipment::create($validated);

        return $this->success(
            new EquipmentResource($equipment->load('client', 'category')),
            'Equipment created successfully',
            201
        );
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['client', 'category', 'lastInspection']);

        return $this->success(new EquipmentResource($equipment));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:template_categories,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'plate' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'internal_code' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|max:50',
            'next_inspection_due_at' => 'nullable|date',
        ]);

        $payload = array_merge([
            'client_id' => $equipment->client_id,
            'plate' => $equipment->plate,
            'serial_number' => $equipment->serial_number,
        ], $validated);

        if ($dupe = $this->findDuplicate($payload, $equipment->id)) {
            return response()->json([
                'success' => false,
                'error' => 'duplicate_equipment',
                'matched_by' => $dupe['matched_by'],
                'existing_equipment' => new EquipmentResource($dupe['equipment']->load('client', 'category')),
            ], 422);
        }

        $equipment->update($validated);

        return $this->success(
            new EquipmentResource($equipment->load('client', 'category')),
            'Equipment updated successfully'
        );
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return $this->success(null, 'Equipment deleted successfully');
    }

    /**
     * Returns ['matched_by' => 'plate'|'serial_number', 'equipment' => Equipment] or null.
     * Ignores rows where the candidate field is null.
     */
    protected function findDuplicate(array $data, ?int $excludeId): ?array
    {
        $clientId = $data['client_id'] ?? null;
        if (! $clientId) {
            return null;
        }

        foreach (['plate', 'serial_number'] as $field) {
            $value = $data[$field] ?? null;
            if (! $value) {
                continue;
            }

            $match = Equipment::query()
                ->where('client_id', $clientId)
                ->where($field, $value)
                ->when($excludeId, fn ($q, $id) => $q->where('id', '!=', $id))
                ->first();

            if ($match) {
                return ['matched_by' => $field, 'equipment' => $match];
            }
        }

        return null;
    }
}
