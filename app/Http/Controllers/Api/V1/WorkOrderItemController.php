<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkOrderItemResource;
use App\Models\WorkOrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WorkOrderItemController extends Controller
{
    use ApiResponse;

    /**
     * Flat list of items assigned to an inspector across all work orders.
     *
     * Defaults to the authenticated user ("my items"). Admins/supervisors may
     * pass ?inspector_id=X to view another inspector's items. Matching uses the
     * effective-inspector rule: items directly assigned to the inspector, plus
     * unassigned items whose work order is led by that inspector (fallback).
     */
    public function index(Request $request)
    {
        $inspectorId = $request->user()->id;

        if ($request->has('inspector_id') && in_array($request->user()->role, ['admin', 'supervisor'])) {
            $inspectorId = $request->query('inspector_id');
        }

        $query = WorkOrderItem::query()
            ->with(['equipment', 'template', 'inspector', 'inspection', 'workOrder.inspectionRequest.client'])
            ->where(function ($q) use ($inspectorId) {
                $q->where('inspector_id', $inspectorId)
                    ->orWhere(function ($fallback) use ($inspectorId) {
                        $fallback->whereNull('inspector_id')
                            ->whereHas('workOrder', fn ($w) => $w->where('inspector_id', $inspectorId));
                    });
            });

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('work_order_id')) {
            $query->where('work_order_id', $request->query('work_order_id'));
        }

        $paginator = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $paginator->through(fn ($item) => new WorkOrderItemResource($item)),
            'Work order items retrieved successfully'
        );
    }

    /**
     * Reassign an individual item to a different inspector
     * (e.g. inspector B takes over one of inspector A's equipment).
     */
    public function update(Request $request, WorkOrderItem $workOrderItem)
    {
        $validated = $request->validate([
            'inspector_id' => 'present|nullable|exists:users,id',
        ]);

        $workOrderItem->update(['inspector_id' => $validated['inspector_id']]);

        $workOrderItem->load(['equipment', 'template', 'inspector', 'inspection']);

        return $this->success(new WorkOrderItemResource($workOrderItem), 'Work order item updated successfully');
    }
}
