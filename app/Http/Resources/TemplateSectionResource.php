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
            'inspection_template_id' => $this->inspection_template_id,
            'name' => $this->name,
            'order' => $this->order,
            'description' => $this->description,
            'questions' => TemplateQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
