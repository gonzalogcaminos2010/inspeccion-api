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
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'plate' => $this->plate,
            'serial_number' => $this->serial_number,
            'internal_code' => $this->internal_code,
            'equipment_code' => $this->internal_code,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'client' => new ClientResource($this->whenLoaded('client')),
        ];
    }
}
