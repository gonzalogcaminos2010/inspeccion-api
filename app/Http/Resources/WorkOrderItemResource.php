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
            'inspection_template_id' => $this->inspection_template_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'equipment' => new EquipmentResource($this->whenLoaded('equipment')),
            'template' => new InspectionTemplateResource($this->whenLoaded('template')),
            'inspection' => new InspectionResource($this->whenLoaded('inspection')),
        ];
    }
}
