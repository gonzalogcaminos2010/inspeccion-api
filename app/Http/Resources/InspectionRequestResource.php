<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'number' => $this->request_number,
            'client_id' => $this->client_id,
            'service_type_id' => $this->service_type_id,
            'request_date' => $this->requested_date,
            'requested_date' => $this->requested_date,
            'due_date' => $this->scheduled_date,
            'scheduled_date' => $this->scheduled_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'service_type' => new ServiceTypeResource($this->whenLoaded('serviceType')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'work_orders' => WorkOrderResource::collection($this->whenLoaded('workOrders')),
            'work_orders_count' => $this->whenCounted('workOrders'),
        ];
    }
}
