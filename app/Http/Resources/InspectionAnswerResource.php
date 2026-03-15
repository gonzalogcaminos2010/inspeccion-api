<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_id' => $this->inspection_id,
            'template_question_id' => $this->template_question_id,
            'answer_text' => $this->answer_text,
            'answer_boolean' => $this->answer_boolean,
            'answer_number' => $this->answer_number,
            'answer_json' => $this->answer_json,
            'is_flagged' => $this->is_flagged,
            'notes' => $this->notes,
            'question' => new TemplateQuestionResource($this->whenLoaded('question')),
        ];
    }
}
