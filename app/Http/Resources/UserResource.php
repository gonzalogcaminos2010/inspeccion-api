<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            // Session capability: AI photo analysis is on only when enabled AND keyed.
            'ai_enabled' => (bool) config('services.anthropic.photo_analysis_enabled')
                && ! empty(config('services.anthropic.api_key')),
            'created_at' => $this->created_at,
        ];
    }
}
