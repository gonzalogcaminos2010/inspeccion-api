<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkOrderItemResource;
use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = WorkOrder::query()
            ->with(['inspectionRequest', 'inspector']);

        if ($search = $request->query('search')) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        if ($request->has('inspection_request_id')) {
            $query->where('inspection_request_id', $request->query('inspection_request_id'));
        }

        if ($request->has('inspector_id')) {
            $query->where('inspector_id', $request->query('inspector_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $paginator = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $paginator->through(fn ($item) => new WorkOrderResource($item)),
            'Work orders retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_request_id' => 'required|exists:inspection_requests,id',
            'inspector_id' => 'nullable|exists:users,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'required|exists:equipment,id',
            'items.*.inspection_template_id' => 'required|exists:inspection_templates,id',
        ]);

        $workOrder = DB::transaction(function () use ($validated) {
            $orderNumber = 'OT-'.date('Ymd').'-'.str_pad(WorkOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            $workOrder = WorkOrder::create([
                'order_number' => $orderNumber,
                'inspection_request_id' => $validated['inspection_request_id'],
                'inspector_id' => $validated['inspector_id'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $workOrder->items()->create([
                    'equipment_id' => $item['equipment_id'],
                    'inspection_template_id' => $item['inspection_template_id'],
                ]);
            }

            return $workOrder;
        });

        $workOrder->load(['inspectionRequest', 'inspector', 'items.equipment', 'items.template']);

        return $this->success(new WorkOrderResource($workOrder), 'Work order created successfully', 201);
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['inspectionRequest', 'inspector', 'items.equipment', 'items.template']);

        return $this->success(new WorkOrderResource($workOrder));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'inspection_request_id' => 'sometimes|required|exists:inspection_requests,id',
            'inspector_id' => 'nullable|exists:users,id',
            'scheduled_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $workOrder->update($validated);

        return $this->success(new WorkOrderResource($workOrder), 'Work order updated successfully');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return $this->success(null, 'Work order deleted successfully');
    }

    public function start(WorkOrder $workOrder)
    {
        $workOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this->success(new WorkOrderResource($workOrder), 'Work order started successfully');
    }

    public function complete(WorkOrder $workOrder)
    {
        $workOrder->load('items');

        $allDone = $workOrder->items->every(fn ($item) => in_array($item->status, ['completed', 'skipped']));

        if (! $allDone) {
            return $this->error('All work order items must be completed or skipped before completing the work order.', 422);
        }

        $workOrder->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this->success(new WorkOrderResource($workOrder), 'Work order completed successfully');
    }

    public function items(WorkOrder $workOrder)
    {
        $items = $workOrder->items()->with(['equipment', 'template'])->get();

        return $this->success(WorkOrderItemResource::collection($items), 'Work order items retrieved successfully');
    }
}
