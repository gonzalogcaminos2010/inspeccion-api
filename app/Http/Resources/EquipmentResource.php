<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'plate' => $this->plate,
            'serial_number' => $this->serial_number,
            'internal_code' => $this->internal_code,
            'equipment_code' => $this->internal_code,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'next_inspection_due_at' => $this->next_inspection_due_at?->toDateString(),
            'last_inspection_completed_at' => $this->last_inspection_completed_at,
            'last_inspection_id' => $this->last_inspection_id,
            'created_at' => $this->created_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'category' => new TemplateCategoryResource($this->whenLoaded('category')),
        ];
    }
}
