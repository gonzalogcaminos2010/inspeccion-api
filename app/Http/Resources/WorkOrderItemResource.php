<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'equipment_id' => $this->equipment_id,
            'template_id' => $this->inspection_template_id,
            'inspection_template_id' => $this->inspection_template_id,
            'inspector_id' => $this->inspector_id,
            'status' => $this->status,
            'sequence' => $this->sequence ?? 0,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at,
            'order_number' => $this->whenLoaded('workOrder', fn () => $this->workOrder->order_number),
            'equipment' => new EquipmentResource($this->whenLoaded('equipment')),
            'template' => new InspectionTemplateResource($this->whenLoaded('template')),
            'inspection' => new InspectionResource($this->whenLoaded('inspection')),
            'inspector' => new UserResource($this->whenLoaded('inspector')),
        ];
    }
}
