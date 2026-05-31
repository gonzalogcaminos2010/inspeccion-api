<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'default_template_id' => $this->default_template_id,
            'default_inspection_interval_months' => $this->default_inspection_interval_months,
            'default_template' => $this->whenLoaded('defaultTemplate', fn () => [
                'id' => $this->defaultTemplate->id,
                'name' => $this->defaultTemplate->name,
                'code' => $this->defaultTemplate->code,
            ]),
            'equipment_fields' => CategoryEquipmentFieldResource::collection(
                $this->whenLoaded('equipmentFields')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
