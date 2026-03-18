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
        $query = Equipment::query()->with('client');

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
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'plate' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'internal_code' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $equipment = Equipment::create($validated);

        return $this->success(new EquipmentResource($equipment->load('client')), 'Equipment created successfully', 201);
    }

    public function show(Equipment $equipment)
    {
        $equipment->load('client');

        return $this->success(new EquipmentResource($equipment));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'plate' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'internal_code' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $equipment->update($validated);

        return $this->success(new EquipmentResource($equipment->load('client')), 'Equipment updated successfully');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return $this->success(null, 'Equipment deleted successfully');
    }
}
