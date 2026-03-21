<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'category' => $this->vehicle_type,
            'vehicle_type' => $this->vehicle_type,
            'is_active' => $this->is_active,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sections' => TemplateSectionResource::collection($this->whenLoaded('sections')),
            'sections_count' => $this->whenCounted('sections'),
        ];
    }
}
