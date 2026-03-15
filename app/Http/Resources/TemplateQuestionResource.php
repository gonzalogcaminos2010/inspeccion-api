<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_section_id' => $this->template_section_id,
            'text' => $this->text,
            'type' => $this->type,
            'options' => $this->options,
            'is_required' => $this->is_required,
            'order' => $this->order,
            'fail_values' => $this->fail_values,
        ];
    }
}
