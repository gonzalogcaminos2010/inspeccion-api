<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceTypeResource;
use App\Models\ServiceType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ServiceType::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $serviceTypes = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $serviceTypes->through(fn ($type) => new ServiceTypeResource($type)),
            'Service types retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $serviceType = ServiceType::create($validated);

        return $this->success(new ServiceTypeResource($serviceType), 'Service type created successfully', 201);
    }

    public function show(ServiceType $serviceType)
    {
        return $this->success(new ServiceTypeResource($serviceType));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $serviceType->update($validated);

        return $this->success(new ServiceTypeResource($serviceType), 'Service type updated successfully');
    }

    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();

        return $this->success(null, 'Service type deleted successfully');
    }
}
