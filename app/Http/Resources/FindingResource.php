<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FindingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_id' => $this->inspection_id,
            'template_question_id' => $this->template_question_id,
            'severity' => $this->severity,
            'description' => $this->description,
            'recommendation' => $this->recommendation,
            'is_resolved' => $this->is_resolved,
            'resolved_at' => $this->resolved_at,
            'resolved_by' => $this->resolved_by,
            'created_at' => $this->created_at,
            'question' => new TemplateQuestionResource($this->whenLoaded('question')),
            'resolver' => new UserResource($this->whenLoaded('resolver')),
            'photos' => InspectionPhotoResource::collection($this->whenLoaded('photos')),
        ];
    }
}
