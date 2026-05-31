<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Http\Resources\WorkOrderItemResource;
use App\Models\Equipment;
use App\Models\WorkOrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderItemController extends Controller
{
    use ApiResponse;

    /**
     * POST /work-order-items/{item}/resolve-equipment
     *
     * Body:
     *   { equipment_id: int }                       — swap to an existing equipment
     * OR
     *   { new_equipment: { name, plate?, serial_number?, ... } }  — create + swap
     *
     * If the item was holding a placeholder, the placeholder is deleted after the swap.
     */
    public function resolveEquipment(Request $request, WorkOrderItem $workOrderItem)
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'new_equipment' => 'nullable|array',
            'new_equipment.name' => 'required_with:new_equipment|string|max:255',
            'new_equipment.plate' => 'nullable|string|max:50',
            'new_equipment.serial_number' => 'nullable|string|max:100',
            'new_equipment.brand' => 'nullable|string|max:255',
            'new_equipment.model' => 'nullable|string|max:255',
            'new_equipment.year' => 'nullable|integer',
            'new_equipment.internal_code' => 'nullable|string|max:100',
            'new_equipment.metadata' => 'nullable|array',
        ]);

        if (empty($validated['equipment_id']) && empty($validated['new_equipment'])) {
            return $this->error('Debe enviar equipment_id o new_equipment.', 422);
        }

        $workOrderItem->load(['workOrder.inspectionRequest', 'equipment']);

        $clientId = $workOrderItem->workOrder->inspectionRequest->client_id ?? null;
        $categoryId = $workOrderItem->category_id ?? $workOrderItem->equipment?->category_id;

        if (! $clientId || ! $categoryId) {
            return $this->error('No se puede resolver el equipo: faltan client_id o category_id en el ítem.', 422);
        }

        $newEquipment = null;

        // Branch A: bind to an existing equipment.
        if (! empty($validated['equipment_id'])) {
            $newEquipment = Equipment::findOrFail($validated['equipment_id']);

            if ($newEquipment->client_id !== $clientId) {
                return $this->error('El equipo elegido pertenece a otro cliente.', 422);
            }

            if ($newEquipment->category_id && $newEquipment->category_id !== $categoryId) {
                return $this->error('El equipo elegido es de otra categoría.', 422);
            }
        }

        // Branch B: create a new equipment for this category.
        if (! empty($validated['new_equipment'])) {
            $payload = array_merge($validated['new_equipment'], [
                'client_id' => $clientId,
                'category_id' => $categoryId,
                'status' => 'active',
            ]);

            $dupe = $this->findDuplicate($payload);
            if ($dupe) {
                return response()->json([
                    'success' => false,
                    'error' => 'duplicate_equipment',
                    'matched_by' => $dupe['matched_by'],
                    'existing_equipment' => new EquipmentResource($dupe['equipment']->load('client', 'category')),
                ], 422);
            }

            $newEquipment = Equipment::create($payload);
        }

        $oldEquipmentId = $workOrderItem->equipment_id;
        $wasPlaceholder = $workOrderItem->is_equipment_placeholder;

        DB::transaction(function () use ($workOrderItem, $newEquipment, $oldEquipmentId, $wasPlaceholder) {
            $workOrderItem->update([
                'equipment_id' => $newEquipment->id,
                'is_equipment_placeholder' => false,
            ]);

            // Cleanup the placeholder we just swapped out of.
            if ($wasPlaceholder && $oldEquipmentId && $oldEquipmentId !== $newEquipment->id) {
                Equipment::where('id', $oldEquipmentId)
                    ->where('status', 'placeholder')
                    ->delete();
            }
        });

        $workOrderItem->load(['equipment.category', 'category', 'template', 'inspection']);

        return $this->success(
            new WorkOrderItemResource($workOrderItem),
            'Equipment resolved successfully'
        );
    }

    protected function findDuplicate(array $data): ?array
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
                ->first();

            if ($match) {
                return ['matched_by' => $field, 'equipment' => $match];
            }
        }

        return null;
    }
}
