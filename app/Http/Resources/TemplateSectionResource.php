<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->inspection_template_id,
            'inspection_template_id' => $this->inspection_template_id,
            'title' => $this->name,
            'name' => $this->name,
            'sort_order' => $this->order,
            'order' => $this->order,
            'description' => $this->description,
            'is_required' => true,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'questions' => TemplateQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
