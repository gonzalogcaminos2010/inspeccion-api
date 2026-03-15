<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ruc' => $this->ruc,
            'address' => $this->address,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'equipment_count' => $this->whenCounted('equipment'),
        ];
    }
}
