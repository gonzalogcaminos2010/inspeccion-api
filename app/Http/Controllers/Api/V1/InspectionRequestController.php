<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionRequestResource;
use App\Models\InspectionRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class InspectionRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = InspectionRequest::query()
            ->with(['client', 'serviceType'])
            ->withCount('workOrders');

        if ($search = $request->query('search')) {
            $query->where('request_number', 'like', "%{$search}%");
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->query('service_type_id'));
        }

        $paginator = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $paginator->through(fn ($item) => new InspectionRequestResource($item)),
            'Inspection requests retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_type_id' => 'required|exists:service_types,id',
            'requested_date' => 'required|date',
            'scheduled_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['request_number'] = 'REQ-'.date('Ymd').'-'.str_pad(InspectionRequest::count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['created_by'] = $request->user()->id;

        $inspectionRequest = InspectionRequest::create($validated);
        $inspectionRequest->load(['client', 'serviceType', 'creator']);

        return $this->success(new InspectionRequestResource($inspectionRequest), 'Inspection request created successfully', 201);
    }

    public function show(InspectionRequest $inspectionRequest)
    {
        $inspectionRequest->load(['client', 'serviceType', 'creator', 'workOrders']);
        $inspectionRequest->loadCount('workOrders');

        return $this->success(new InspectionRequestResource($inspectionRequest));
    }

    public function update(Request $request, InspectionRequest $inspectionRequest)
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'service_type_id' => 'sometimes|required|exists:service_types,id',
            'requested_date' => 'sometimes|required|date',
            'scheduled_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $inspectionRequest->update($validated);

        return $this->success(new InspectionRequestResource($inspectionRequest), 'Inspection request updated successfully');
    }

    public function destroy(InspectionRequest $inspectionRequest)
    {
        $inspectionRequest->delete();

        return $this->success(null, 'Inspection request deleted successfully');
    }
}
