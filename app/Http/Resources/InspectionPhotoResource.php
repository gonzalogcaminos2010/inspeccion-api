<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InspectionPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_id' => $this->inspection_id,
            'template_question_id' => $this->template_question_id,
            'finding_id' => $this->finding_id,
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_path ? Storage::url($this->photo_path) : null,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
        ];
    }
}
