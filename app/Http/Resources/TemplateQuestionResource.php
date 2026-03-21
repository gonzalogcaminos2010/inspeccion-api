<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->type === 'select' ? 'multiple_choice' : ($this->type === 'number' ? 'number' : $this->type);

        return [
            'id' => $this->id,
            'section_id' => $this->template_section_id,
            'template_section_id' => $this->template_section_id,
            'question_text' => $this->text,
            'text' => $this->text,
            'question_type' => $type,
            'type' => $type,
            'options' => $this->options,
            'is_required' => $this->is_required,
            'sort_order' => $this->order,
            'order' => $this->order,
            'fail_values' => $this->fail_values,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
