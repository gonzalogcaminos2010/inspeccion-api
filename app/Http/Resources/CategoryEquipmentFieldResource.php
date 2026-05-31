<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryEquipmentFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_category_id' => $this->template_category_id,
            'key_name' => $this->key_name,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'unit' => $this->unit,
            'is_required' => $this->is_required,
            'is_mutable' => $this->is_mutable,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
