<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'inspection_request_id' => $this->inspection_request_id,
            'inspector_id' => $this->inspector_id,
            'scheduled_date' => $this->scheduled_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'inspection_request' => new InspectionRequestResource($this->whenLoaded('inspectionRequest')),
            'inspector' => new UserResource($this->whenLoaded('inspector')),
            'items' => WorkOrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
